<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use App\Models\Service;
use App\Models\Setting;
use App\Http\Requests\BillStoreRequest;
use App\Http\Requests\BillUpdateRequest;
use App\Http\Requests\PaymentRequest;
use App\Events\PaymentReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class BillController extends Controller
{
    /**
     * Generate a unique bill number
     */
    private function generateBillNumber($clinicId)
    {
        $clinic = Clinic::find($clinicId);
        if (!$clinic) {
            throw new \Exception('Clinic not found');
        }

        // Generate a unique bill ID based on clinic prefix
        $prefix = strtoupper(substr($clinic->name, 0, 3)) . '-BILL-';

        // Find the highest existing bill number for this clinic
        $lastBill = Bill::where('clinic_id', $clinicId)
            ->where('bill_number', 'LIKE', $prefix . '%')
            ->orderBy('bill_number', 'desc')
            ->first();

        if ($lastBill) {
            $lastNumber = intval(substr($lastBill->bill_number, -6));
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return $prefix . $newNumber;
    }

    /**
     * Calculate tax amount based on clinic settings
     */
    private function calculateTax($amount, $clinicId)
    {
        $taxRateSetting = Setting::active()
            ->forClinic($clinicId)
            ->byKey('tax_rate')
            ->first();

        $taxRate = $taxRateSetting ? $taxRateSetting->parsed_value / 100 : 0.10; // Default to 10% if not set
        return $amount * $taxRate;
    }

    /**
     * Calculate discount amount
     */
    private function calculateDiscount($amount, $patientId, $appointmentId = null, $clinicId = null)
    {
        $discount = 0;

        // Check if patient has insurance
        $patient = Patient::find($patientId);
        if ($patient && $patient->insurance_provider) {
            // Get insurance discount rate from settings
            $insuranceDiscountSetting = Setting::active()
                ->forClinic($clinicId ?? $patient->clinic_id)
                ->byKey('insurance_discount_rate')
                ->first();

            $insuranceDiscountRate = $insuranceDiscountSetting ? $insuranceDiscountSetting->parsed_value / 100 : 0.20; // Default to 20%
            $discount = $amount * $insuranceDiscountRate;
        }

        // Check if this is a follow-up appointment
        if ($appointmentId) {
            $appointment = Appointment::find($appointmentId);
            if ($appointment && $appointment->appointment_type === 'follow-up') {
                // Get follow-up discount rate from settings
                $followUpDiscountSetting = Setting::active()
                    ->forClinic($clinicId ?? $appointment->clinic_id)
                    ->byKey('follow_up_discount_rate')
                    ->first();

                $followUpDiscountRate = $followUpDiscountSetting ? $followUpDiscountSetting->parsed_value / 100 : 0.10; // Default to 10%
                $discount += $amount * $followUpDiscountRate;
            }
        }

        return $discount;
    }

    /**
     * Calculate total amount with tax and discount
     */
    private function calculateTotalAmount($amount, $tax, $discount)
    {
        return $amount + $tax - $discount;
    }

    /**
     * Get all bills for the current clinic/user
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Bill::with(['clinic', 'patient.user', 'doctor.user', 'appointment', 'creator']);

        // Filter by clinic if user is not admin
        if (!$user->isAdmin()) {
            $query->where('clinic_id', $user->clinic_id);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by patient if provided
        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Filter by doctor if provided
        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('bill_date', [$request->start_date, $request->end_date]);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            if ($request->payment_status === 'paid') {
                $query->where('status', 'paid');
            } elseif ($request->payment_status === 'unpaid') {
                $query->where('status', 'unpaid');
            } elseif ($request->payment_status === 'partially_paid') {
                $query->where('status', 'partially_paid');
            }
        }

        // Sort by bill date (default) or other criteria
        $query->orderBy('bill_date', 'desc');

        // Pagination
        $bills = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $bills
        ]);
    }

    /**
     * Create a new bill
     */
    public function store(BillStoreRequest $request)
    {
        \Log::info('BillController store called', ['request' => $request->all()]);
        $validatedData = $request->validated();

        $user = Auth::user();
        \Log::info('User authenticated', ['user_id' => $user ? $user->id : null]);

        // Check if user has permission to create bills for this clinic
        if (!$user->isAdmin() && !$user->isDoctor() && !$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create bills'
            ], 403);
        }

        // Check if user has access to this clinic
        if (!$user->isAdmin() && $request->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create bills for this clinic'
            ], 403);
        }

        $patient = Patient::find($request->patient_id);
        $doctor = $request->doctor_id ? Doctor::find($request->doctor_id) : null;
        $appointment = $request->appointment_id ? Appointment::find($request->appointment_id) : null;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }

        // Calculate tax and discount if not provided
        $amount = $request->amount;
        $tax = $request->tax ?? $this->calculateTax($amount, $request->clinic_id);
        $discount = $request->discount ?? $this->calculateDiscount($amount, $request->patient_id, $request->appointment_id, $request->clinic_id);
        $totalAmount = $request->total_amount ?? $this->calculateTotalAmount($amount, $tax, $discount);

        try {
            // Create the bill
            $bill = Bill::create([
                'clinic_id' => $request->clinic_id,
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'appointment_id' => $request->appointment_id,
                'created_by' => $user->id,
                'bill_number' => $this->generateBillNumber($request->clinic_id),
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'amount' => $amount,
                'tax' => $tax,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'status' => $request->status ?? 'unpaid',
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'description' => $request->description,
                'items' => $request->items,
                'insurance_claim_status' => $request->insurance_claim_status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bill created successfully',
                'data' => Bill::with(['clinic', 'patient.user', 'doctor.user', 'appointment', 'creator'])->find($bill->id)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create bill',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific bill
     */
    public function show($id)
    {
        $bill = Bill::with(['clinic', 'patient.user', 'doctor.user', 'appointment', 'creator'])->find($id);

        if (!$bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill not found'
            ], 404);
        }

        $user = Auth::user();

        // Check if user has access to this bill
        if (!$user->isAdmin() && $bill->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this bill'
            ], 403);
        }

        // Patients can only access their own bills
        if ($user->isPatient() && $bill->patient_id != $user->patient->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this bill'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $bill
        ]);
    }

    /**
     * Update a bill
     */
    public function update(BillUpdateRequest $request, $id)
    {
        $bill = Bill::find($id);

        if (!$bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill not found'
            ], 404);
        }

        $user = Auth::user();

        // Check if user has permission to update this bill
        if (!$user->isAdmin() && !$user->isDoctor() && !$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update bills'
            ], 403);
        }

        // Check if user has access to this clinic
        if (!$user->isAdmin() && $bill->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this bill'
            ], 403);
        }

        $validatedData = $request->validated();

        // Recalculate totals if amount, tax, or discount is being updated
        if ($request->has('amount') || $request->has('tax') || $request->has('discount')) {
            $amount = $request->amount ?? $bill->amount;
            $tax = $request->tax ?? $bill->tax;
            $discount = $request->discount ?? $bill->discount;
            $validatedData['total_amount'] = $this->calculateTotalAmount($amount, $tax, $discount);
        }

        try {
            // Update the bill
            $bill->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Bill updated successfully',
                'data' => Bill::with(['clinic', 'patient.user', 'doctor.user', 'appointment', 'creator'])->find($bill->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bill',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a bill
     */
    public function destroy($id)
    {
        $bill = Bill::find($id);

        if (!$bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill not found'
            ], 404);
        }

        $user = Auth::user();

        // Only admins can delete bills
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can delete bills'
            ], 403);
        }

        // Check if user has access to this clinic
        if (!$user->isAdmin() && $bill->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this bill'
            ], 403);
        }

        try {
            // Soft delete the bill
            $bill->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bill deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete bill',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payment for a bill
     */
    public function processPayment(PaymentRequest $request, $id)
    {
        $bill = Bill::find($id);

        if (!$bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill not found'
            ], 404);
        }

        $user = Auth::user();

        // Check if user has permission to process payments
        if (!$user->isAdmin() && !$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to process payments'
            ], 403);
        }

        // Check if user has access to this clinic
        if (!$user->isAdmin() && $bill->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this bill'
            ], 403);
        }

        // Check if bill is already paid
        if ($bill->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Bill is already paid'
            ], 400);
        }

        // Check if bill is cancelled
        if ($bill->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot process payment for a cancelled bill'
            ], 400);
        }

        $paymentAmount = $request->amount;
        $remainingAmount = $bill->total_amount - ($bill->status === 'partially_paid' ? $bill->paid_amount ?? 0 : 0);

        // Check if payment amount exceeds remaining amount
        if ($paymentAmount > $remainingAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount exceeds remaining bill amount',
                'remaining_amount' => $remainingAmount
            ], 400);
        }

        try {
            // Update bill status and payment information
            $updateData = [
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date ?? now(),
                'status' => $paymentAmount >= $remainingAmount ? 'paid' : 'partially_paid',
            ];

            // Store payment details in items array if it exists
            $paymentDetails = [
                'payment_amount' => $paymentAmount,
                'payment_method' => $request->payment_method,
                'payment_date' => $updateData['payment_date'],
                'transaction_reference' => $request->transaction_reference,
                'notes' => $request->notes,
                'processed_by' => $user->id,
                'processed_at' => now()
            ];

            $existingItems = $bill->items ?? [];
            $existingItems['payments'] = $existingItems['payments'] ?? [];
            $existingItems['payments'][] = $paymentDetails;

            $updateData['items'] = $existingItems;

            $bill->update($updateData);

            // Fire the payment received event
            event(new PaymentReceived($bill, $paymentAmount, $request->payment_method));

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => Bill::with(['clinic', 'patient.user', 'doctor.user', 'appointment', 'creator'])->find($bill->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate bill PDF
     */
    public function generatePdf($id)
    {
        $bill = Bill::with(['clinic', 'patient.user', 'doctor.user', 'appointment', 'creator'])->find($id);

        if (!$bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill not found'
            ], 404);
        }

        $user = Auth::user();

        // Check if user has access to this bill
        if (!$user->isAdmin() && $bill->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this bill'
            ], 403);
        }

        // Patients can only access their own bills
        if ($user->isPatient() && $bill->patient_id != $user->patient->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this bill'
            ], 403);
        }

        try {
            $data = [
                'bill' => $bill,
                'clinic' => $bill->clinic,
                'patient' => $bill->patient,
                'doctor' => $bill->doctor,
                'appointment' => $bill->appointment,
                'generated_at' => now(),
                'generated_by' => $user->name,
            ];

            $html = view()->file(app_path('Views/bills/invoice.blade.php'), $data)->render();
            $pdf = Pdf::loadHtml($html);

            return $pdf->download('bill_' . $bill->bill_number . '.pdf');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get bills for a specific patient
     */
    public function getPatientBills($patientId)
    {
        $patient = Patient::find($patientId);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }

        $user = Auth::user();

        // Check if user has access to this patient
        if (!$user->isAdmin() && $patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this patient'
            ], 403);
        }

        // Patients can only access their own bills
        if ($user->isPatient() && $patient->id != $user->patient->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this patient'
            ], 403);
        }

        $bills = $patient->bills()->with(['doctor', 'clinic', 'appointment', 'creator'])
            ->orderBy('bill_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bills
        ]);
    }

    /**
     * Get bills for a specific doctor
     */
    public function getDoctorBills($doctorId)
    {
        $doctor = Doctor::find($doctorId);

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found'
            ], 404);
        }

        $user = Auth::user();

        // Check if user has access to this doctor
        if (!$user->isAdmin() && $doctor->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this doctor'
            ], 403);
        }

        $bills = $doctor->bills()->with(['patient.user', 'clinic', 'appointment', 'creator'])
            ->orderBy('bill_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bills
        ]);
    }

    /**
     * Get bills for a specific appointment
     */
    public function getAppointmentBills($appointmentId)
    {
        $appointment = Appointment::find($appointmentId);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }

        $user = Auth::user();

        // Check if user has access to this appointment
        if (!$user->isAdmin() && $appointment->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this appointment'
            ], 403);
        }

        $bills = $appointment->bills()->with(['patient.user', 'doctor.user', 'clinic', 'creator'])
            ->orderBy('bill_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bills
        ]);
    }

    /**
     * Get billing statistics for dashboard
     */
    public function getBillingStatistics(Request $request)
    {
        $user = Auth::user();

        $query = Bill::query();

        // Filter by clinic if user is not admin
        if (!$user->isAdmin()) {
            $query->where('clinic_id', $user->clinic_id);
        }

        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('bill_date', [$request->start_date, $request->end_date]);
        } else {
            // Default to last 30 days
            $query->where('bill_date', '>=', now()->subDays(30)->toDateString());
        }

        $totalBills = $query->count();
        $totalAmount = $query->sum('total_amount');
        $paidAmount = $query->where('status', 'paid')->sum('total_amount');
        $unpaidAmount = $query->where('status', 'unpaid')->sum('total_amount');
        $partiallyPaidAmount = $query->where('status', 'partially_paid')->sum('total_amount');
        $cancelledAmount = $query->where('status', 'cancelled')->sum('total_amount');

        // Get payment method distribution
        $paymentMethods = $query->whereNotNull('payment_method')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as amount')
            ->groupBy('payment_method')
            ->get();

        // Get status distribution
        $statusDistribution = $query->selectRaw('status, COUNT(*) as count, SUM(total_amount) as amount')
            ->groupBy('status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_bills' => $totalBills,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'unpaid_amount' => $unpaidAmount,
                'partially_paid_amount' => $partiallyPaidAmount,
                'cancelled_amount' => $cancelledAmount,
                'payment_methods' => $paymentMethods,
                'status_distribution' => $statusDistribution,
            ]
        ]);
    }

    /**
     * Create bill from appointment
     */
    public function createFromAppointment($appointmentId)
    {
        $appointment = Appointment::with(['clinic', 'patient', 'doctor'])->find($appointmentId);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }

        $user = Auth::user();

        // Check if user has permission to create bills
        if (!$user->isAdmin() && !$user->isDoctor() && !$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create bills'
            ], 403);
        }

        // Check if user has access to this clinic
        if (!$user->isAdmin() && $appointment->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this appointment'
            ], 403);
        }

        // Check if appointment is completed
        if ($appointment->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Can only create bill for completed appointments'
            ], 400);
        }

        // Check if bill already exists for this appointment
        $existingBill = Bill::where('appointment_id', $appointmentId)->first();
        if ($existingBill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill already exists for this appointment',
                'existing_bill_id' => $existingBill->id
            ], 400);
        }

        try {
            // Determine amount based on doctor's consultation fee
            $amount = $appointment->doctor->consultation_fee ?? 100.00;
            $tax = $this->calculateTax($amount, $appointment->clinic_id);
            $discount = $this->calculateDiscount($amount, $appointment->patient_id, $appointment->id, $appointment->clinic_id);
            $totalAmount = $this->calculateTotalAmount($amount, $tax, $discount);

            // Create the bill
            $bill = Bill::create([
                'clinic_id' => $appointment->clinic_id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'created_by' => $user->id,
                'bill_number' => $this->generateBillNumber($appointment->clinic_id),
                'bill_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'amount' => $amount,
                'tax' => $tax,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'status' => 'unpaid',
                'description' => 'Consultation fee for appointment ' . $appointment->appointment_number,
                'items' => [
                    [
                        'service_name' => 'Consultation Fee',
                        'quantity' => 1,
                        'unit_price' => $amount,
                        'total_price' => $amount
                    ]
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bill created from appointment successfully',
                'data' => Bill::with(['clinic', 'patient.user', 'doctor.user', 'appointment', 'creator'])->find($bill->id)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create bill from appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a bill
     */
    public function cancel($id)
    {
        $bill = Bill::find($id);

        if (!$bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill not found'
            ], 404);
        }

        $user = Auth::user();

        // Check if user has permission to cancel bills
        if (!$user->isAdmin() && !$user->isDoctor() && !$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to cancel bills'
            ], 403);
        }

        // Check if user has access to this clinic
        if (!$user->isAdmin() && $bill->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this bill'
            ], 403);
        }

        // Check if bill can be cancelled (not already paid or cancelled)
        if ($bill->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel a paid bill'
            ], 400);
        }

        if ($bill->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Bill is already cancelled'
            ], 400);
        }

        try {
            // Update status to cancelled
            $bill->update([
                'status' => 'cancelled',
                'cancelled_by' => $user->id,
                'cancelled_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bill cancelled successfully',
                'data' => Bill::with(['clinic', 'patient.user', 'doctor.user', 'appointment', 'creator'])->find($bill->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel bill',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get service catalog for billing
     */
    public function getServiceCatalog(Request $request)
    {
        $user = Auth::user();
        $query = Service::active();

        // Filter by clinic if user is not admin
        if (!$user->isAdmin()) {
            $query->where('clinic_id', $user->clinic_id);
        }

        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $services = $query->orderBy('category')->orderBy('name')->get();

        // Transform to match the expected format
        $serviceCatalog = $services->map(function ($service) {
            return [
                'id' => $service->service_code,
                'name' => $service->name,
                'description' => $service->description,
                'base_price' => $service->base_price,
                'category' => $service->category,
                'taxable' => $service->taxable,
                'insurance_eligible' => $service->insurance_eligible,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $serviceCatalog
        ]);
    }
}