<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function dashboard(Request $request)
    {
        \Log::info('Dashboard request received', [
            'user_id' => $request->user() ? $request->user()->id : 'null',
            'auth_check' => auth()->check(),
            'headers' => $request->headers->all(),
            'bearer_token' => $request->bearerToken(),
        ]);

        if (!$request->user()) {
            \Log::error('No authenticated user in dashboard request');
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $clinicId = $request->user()->clinic_id;

        // Total patients
        $totalPatients = Patient::where('clinic_id', $clinicId)->count();

        // Total doctors
        $totalDoctors = Doctor::where('clinic_id', $clinicId)->count();

        // Total appointments this month
        $totalAppointments = Appointment::where('clinic_id', $clinicId)
            ->whereMonth('appointment_date', now()->month)
            ->whereYear('appointment_date', now()->year)
            ->count();

        // Total revenue this month
        $totalRevenue = Bill::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereMonth('bill_date', now()->month)
            ->whereYear('bill_date', now()->year)
            ->sum('total_amount');

        // Pending bills
        $pendingBills = Bill::where('clinic_id', $clinicId)
            ->where('status', 'pending')
            ->count();

        // Today's appointments
        $todayAppointments = Appointment::where('clinic_id', $clinicId)
            ->whereDate('appointment_date', today())
            ->count();

        return response()->json([
            'total_patients' => $totalPatients,
            'total_doctors' => $totalDoctors,
            'total_appointments_this_month' => $totalAppointments,
            'total_revenue_this_month' => $totalRevenue,
            'pending_bills' => $pendingBills,
            'today_appointments' => $todayAppointments,
        ]);
    }

    /**
     * Financial reports
     */
    public function financial(Request $request)
    {
        $clinicId = $request->user()->clinic_id;
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        // Revenue report
        $revenue = Bill::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->sum('total_amount');

        // Doctor earnings
        $doctorEarnings = Bill::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->select('doctor_id', DB::raw('SUM(total_amount) as earnings'))
            ->groupBy('doctor_id')
            ->with('doctor')
            ->get()
            ->map(function ($bill) {
                return [
                    'doctor_name' => $bill->doctor->user->name,
                    'earnings' => $bill->earnings,
                ];
            });

        // Payment methods
        $paymentMethods = Bill::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'revenue' => $revenue,
            'doctor_earnings' => $doctorEarnings,
            'payment_methods' => $paymentMethods,
        ]);
    }

    /**
     * Operational reports
     */
    public function operational(Request $request)
    {
        $clinicId = $request->user()->clinic_id;
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        // No-show rates
        $totalAppointments = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->count();

        $noShows = Appointment::where('clinic_id', $clinicId)
            ->where('status', 'no-show')
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->count();

        $noShowRate = $totalAppointments > 0 ? ($noShows / $totalAppointments) * 100 : 0;

        // Doctor utilization
        $doctorUtilization = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->select('doctor_id', DB::raw('COUNT(*) as appointments'))
            ->groupBy('doctor_id')
            ->with('doctor')
            ->get()
            ->map(function ($appointment) {
                return [
                    'doctor_name' => $appointment->doctor->user->name,
                    'appointments' => $appointment->appointments,
                ];
            });

        // Appointment cancellations
        $cancellations = Appointment::where('clinic_id', $clinicId)
            ->where('status', 'cancelled')
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->count();

        return response()->json([
            'no_show_rate' => round($noShowRate, 2),
            'total_appointments' => $totalAppointments,
            'no_shows' => $noShows,
            'doctor_utilization' => $doctorUtilization,
            'cancellations' => $cancellations,
        ]);
    }

    /**
     * Export financial report to CSV
     */
    public function exportFinancial(Request $request)
    {
        $clinicId = $request->user()->clinic_id;
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $bills = Bill::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->with(['doctor.user', 'patient.user'])
            ->get();

        $filename = 'financial_report_' . now()->format('Y-m-d') . '.csv';

        return new StreamedResponse(function () use ($bills) {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, ['Bill Number', 'Patient', 'Doctor', 'Amount', 'Payment Method', 'Bill Date']);

            // CSV data
            foreach ($bills as $bill) {
                fputcsv($handle, [
                    $bill->bill_number,
                    $bill->patient->user->name,
                    $bill->doctor->user->name,
                    $bill->total_amount,
                    $bill->payment_method,
                    $bill->bill_date,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export operational report to CSV
     */
    public function exportOperational(Request $request)
    {
        $clinicId = $request->user()->clinic_id;
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $appointments = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->with(['doctor.user', 'patient.user'])
            ->get();

        $filename = 'operational_report_' . now()->format('Y-m-d') . '.csv';

        return new StreamedResponse(function () use ($appointments) {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, ['Appointment Number', 'Patient', 'Doctor', 'Date', 'Status', 'Type']);

            // CSV data
            foreach ($appointments as $appointment) {
                fputcsv($handle, [
                    $appointment->appointment_number,
                    $appointment->patient->user->name,
                    $appointment->doctor->user->name,
                    $appointment->appointment_date,
                    $appointment->status,
                    $appointment->appointment_type,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}