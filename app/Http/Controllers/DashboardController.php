<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Encounter;
use App\Models\Diagnosis;
use App\Models\LabOrder;
use App\Models\MedicationOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard with key metrics and analytics
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get date range from request or use defaults
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());
        
        // Calculate key metrics
        $totalPatients = Patient::count();
        $newPatients = Patient::where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)
                                ->count();
        
        $totalAppointments = Appointment::count();
        $upcomingAppointments = Appointment::where('appointment_date', '>=', now())
                                          ->where('status', '!=', 'completed')
                                          ->count();
        
        $totalRevenue = Bill::where('status', 'paid')
                           ->where('payment_date', '>=', $startDate)
                           ->where('payment_date', '<=', $endDate)
                           ->sum('amount');
        
        $pendingPayments = Bill::where('status', '!=', 'paid')
                              ->where('due_date', '>=', $startDate)
                              ->where('due_date', '<=', $endDate)
                              ->sum('amount');
        
        // Clinical metrics
        $totalEncounters = Encounter::count();
        $recentEncounters = Encounter::with(['patient', 'doctor'])
                                    ->where('encounter_date', '>=', $startDate)
                                    ->where('encounter_date', '<=', $endDate)
                                    ->orderBy('encounter_date', 'desc')
                                    ->limit(10)
                                    ->get();
        
        $commonDiagnoses = Diagnosis::select('diagnosis', Diagnosis::raw('count(*) as count'))
                                    ->groupBy('diagnosis')
                                    ->orderBy('count', 'desc')
                                    ->limit(5)
                                    ->get();
        
        $labOrders = LabOrder::where('created_at', '>=', $startDate)
                            ->where('created_at', '<=', $endDate)
                            ->count();
        
        $medicationOrders = MedicationOrder::where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)
                                          ->count();
        
        return Inertia::render('Dashboard/Index', [
            'metrics' => [
                'totalPatients' => $totalPatients,
                'newPatients' => $newPatients,
                'totalAppointments' => $totalAppointments,
                'upcomingAppointments' => $upcomingAppointments,
                'totalRevenue' => $totalRevenue,
                'pendingPayments' => $pendingPayments,
                'totalEncounters' => $totalEncounters,
                'labOrders' => $labOrders,
                'medicationOrders' => $medicationOrders,
            ],
            'recentEncounters' => $recentEncounters,
            'commonDiagnoses' => $commonDiagnoses,
            'dateRange' => [
                'startDate' => $startDate,
                'endDate' => $endDate
            ]
        ]);
    }

    /**
     * Get analytics data for charts
     */
    public function analytics(Request $request)
    {
        $user = $request->user();
        
        // Get date range from request or use defaults
        $startDate = $request->input('start_date', now()->subMonths(6));
        $endDate = $request->input('end_date', now());
        $interval = $request->input('interval', 'month');
        
        // Patient growth analytics
        $patientGrowth = $this->getTimeSeriesData(
            Patient::class, 
            'created_at', 
            $startDate, 
            $endDate, 
            $interval
        );
        
        // Revenue analytics
        $revenueData = $this->getTimeSeriesData(
            Bill::class, 
            'payment_date', 
            $startDate, 
            $endDate, 
            $interval, 
            'amount'
        );
        
        // Appointment analytics
        $appointmentData = $this->getTimeSeriesData(
            Appointment::class, 
            'appointment_date', 
            $startDate, 
            $endDate, 
            $interval
        );
        
        // Diagnosis frequency
        $diagnosisData = Diagnosis::select('diagnosis', Diagnosis::raw('count(*) as count'))
                                ->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)
                                ->groupBy('diagnosis')
                                ->orderBy('count', 'desc')
                                ->limit(10)
                                ->get();
        
        return response()->json([
            'patientGrowth' => $patientGrowth,
            'revenueData' => $revenueData,
            'appointmentData' => $appointmentData,
            'diagnosisData' => $diagnosisData,
            'dateRange' => [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'interval' => $interval
            ]
        ]);
    }

    /**
     * Get time series data for analytics charts
     */
    private function getTimeSeriesData($model, $dateField, $startDate, $endDate, $interval, $valueField = null)
    {
        $query = $model::selectRaw("DATE_TRUNC('$interval', $dateField) as period");
        
        if ($valueField) {
            $query->selectRaw("SUM($valueField) as value");
        } else {
            $query->selectRaw('COUNT(*) as value');
        }
        
        $query->where($dateField, '>=', $startDate)
              ->where($dateField, '<=', $endDate)
              ->groupBy('period')
              ->orderBy('period');
        
        return $query->get();
    }

    /**
     * Get operational metrics for the dashboard
     */
    public function operationalMetrics(Request $request)
    {
        $user = $request->user();
        
        // Get date range from request or use defaults
        $startDate = $request->input('start_date', now()->startOfWeek());
        $endDate = $request->input('end_date', now()->endOfWeek());
        
        // Average wait time for appointments
        $avgWaitTime = Appointment::where('status', 'completed')
                                ->where('appointment_date', '>=', $startDate)
                                ->where('appointment_date', '<=', $endDate)
                                ->avg(DB::raw('EXTRACT(EPOCH FROM (actual_start_time - appointment_date))'));
        
        // Average consultation duration
        $avgConsultationDuration = Appointment::where('status', 'completed')
                                            ->where('appointment_date', '>=', $startDate)
                                            ->where('appointment_date', '<=', $endDate)
                                            ->avg(DB::raw('EXTRACT(EPOCH FROM (actual_end_time - actual_start_time))'));
        
        // No-show rate
        $totalScheduled = Appointment::where('appointment_date', '>=', $startDate)
                                    ->where('appointment_date', '<=', $endDate)
                                    ->count();
        
        $noShows = Appointment::where('status', 'no_show')
                            ->where('appointment_date', '>=', $startDate)
                            ->where('appointment_date', '<=', $endDate)
                            ->count();
        
        $noShowRate = $totalScheduled > 0 ? ($noShows / $totalScheduled) * 100 : 0;
        
        // Resource utilization
        $doctorUtilization = Doctor::withCount(['appointments' => function($query) use ($startDate, $endDate) {
            $query->where('appointment_date', '>=', $startDate)
                  ->where('appointment_date', '<=', $endDate);
        }])->get();
        
        return response()->json([
            'avgWaitTime' => $avgWaitTime,
            'avgConsultationDuration' => $avgConsultationDuration,
            'noShowRate' => $noShowRate,
            'doctorUtilization' => $doctorUtilization,
            'dateRange' => [
                'startDate' => $startDate,
                'endDate' => $endDate
            ]
        ]);
    }

    /**
     * Export dashboard data as CSV
     */
    public function export(Request $request)
    {
        $user = $request->user();
        
        // Get date range from request or use defaults
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());
        
        // Get data for export
        $data = [
            'Metrics' => [
                'Total Patients' => Patient::count(),
                'New Patients' => Patient::where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)
                                        ->count(),
                'Total Appointments' => Appointment::count(),
                'Upcoming Appointments' => Appointment::where('appointment_date', '>=', now())
                                                    ->where('status', '!=', 'completed')
                                                    ->count(),
                'Total Revenue' => Bill::where('status', 'paid')
                                       ->where('payment_date', '>=', $startDate)
                                       ->where('payment_date', '<=', $endDate)
                                       ->sum('amount'),
                'Pending Payments' => Bill::where('status', '!=', 'paid')
                                        ->where('due_date', '>=', $startDate)
                                        ->where('due_date', '<=', $endDate)
                                        ->sum('amount'),
            ]
        ];
        
        $csvFileName = 'dashboard_export_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            foreach ($data as $section => $metrics) {
                fputcsv($file, [$section]);
                foreach ($metrics as $key => $value) {
                    fputcsv($file, [$key, $value]);
                }
                fputcsv($file, []); // Empty line between sections
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}