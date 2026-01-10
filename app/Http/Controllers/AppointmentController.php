<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\Clinic;
use App\Models\User;
use App\Http\Requests\AppointmentStoreRequest;
use App\Http\Requests\AppointmentUpdateRequest;
use App\Events\AppointmentCreated;
use App\Events\AppointmentUpdated;
use App\Events\AppointmentCancelled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Get all appointments for the current clinic/user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Appointment::with(['clinic', 'patient.user', 'doctor.user', 'creator']);
        
        // Filter by clinic if user is not admin
        if (!$user->isAdmin()) {
            $query->where('clinic_id', $user->clinic_id);
        }
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('appointment_date', [$request->start_date, $request->end_date]);
        }
        
        // Filter by doctor if provided
        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }
        
        // Filter by patient if provided
        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }
        
        // Sort by appointment date (default) or other criteria
        $query->orderBy('appointment_date', 'asc');
        
        // Pagination
        $appointments = $query->paginate($request->get('per_page', 15));
        
        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }
    
    /**
     * Generate a unique appointment number
     */
    private function generateAppointmentNumber($clinicId)
    {
        $clinic = Clinic::find($clinicId);
        if (!$clinic) {
            throw new \Exception('Clinic not found');
        }
        
        // Generate a unique appointment ID based on clinic prefix
        $prefix = strtoupper(substr($clinic->name, 0, 3)) . '-APPT-';
        
        // Find the highest existing appointment number for this clinic
        $lastAppointment = Appointment::where('clinic_id', $clinicId)
            ->where('appointment_number', 'LIKE', $prefix . '%')
            ->orderBy('appointment_number', 'desc')
            ->first();
        
        if ($lastAppointment) {
            $lastNumber = intval(substr($lastAppointment->appointment_number, -6));
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }
        
        return $prefix . $newNumber;
    }
    
    /**
     * Get available time slots for a doctor on a specific date
     */
    public function getAvailableSlots(Request $request, $doctorId)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
            'appointment_type' => 'nullable|string|in:consultation,follow-up,emergency',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
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
        
        $date = Carbon::parse($request->date);
        $dayOfWeek = $date->format('l'); // Full day name (Monday, Tuesday, etc.)
        
        // Get doctor's schedule for this day
        $schedule = Schedule::where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();
        
        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor is not available on this day'
            ], 404);
        }
        
        // Get existing appointments for this doctor on this date
        $existingAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date->toDateString())
            ->whereIn('status', ['scheduled', 'completed'])
            ->orderBy('start_time', 'asc')
            ->get();
        
        // Generate available time slots
        $slotDuration = $schedule->slot_duration; // in minutes
        $startTime = Carbon::parse($schedule->start_time);
        $endTime = Carbon::parse($schedule->end_time);
        
        $availableSlots = [];
        $currentTime = $startTime->copy();
        
        while ($currentTime->lt($endTime)) {
            $slotEndTime = $currentTime->copy()->addMinutes($slotDuration);
            
            // Check if this slot is already booked
            $isBooked = false;
            foreach ($existingAppointments as $appointment) {
                $appointmentStart = Carbon::parse($appointment->start_time);
                $appointmentEnd = Carbon::parse($appointment->end_time);
                
                // Check for overlap
                if ($currentTime->lt($appointmentEnd) && $slotEndTime->gt($appointmentStart)) {
                    $isBooked = true;
                    break;
                }
            }
            
            if (!$isBooked) {
                $availableSlots[] = [
                    'start_time' => $currentTime->format('H:i'),
                    'end_time' => $slotEndTime->format('H:i'),
                    'is_available' => true
                ];
            }
            
            $currentTime->addMinutes($slotDuration);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'doctor_id' => $doctorId,
                'date' => $date->toDateString(),
                'day_of_week' => $dayOfWeek,
                'schedule' => $schedule,
                'available_slots' => $availableSlots,
                'slot_duration' => $slotDuration
            ]
        ]);
    }
    
    /**
     * Create a new appointment
     */
    public function store(AppointmentStoreRequest $request)
    {
        $validatedData = $request->validated();
        
        $user = Auth::user();
        
        // Check if user has permission to create appointments for this clinic
        if (!$user->isAdmin() && !$user->isDoctor() && !$user->isStaff() && !$user->isPatient()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create appointments'
            ], 403);
        }
        
        // Check if user has access to this clinic
        if (!$user->isAdmin() && $request->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create appointments for this clinic'
            ], 403);
        }
        
        $doctor = Doctor::find($request->doctor_id);
        $patient = Patient::find($request->patient_id);
        
        if (!$doctor || !$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor or patient not found'
            ], 404);
        }
        
        // Check if doctor is accepting new patients (for first visits)
        if ($request->is_first_visit && !$doctor->is_accepting_new_patients) {
            return response()->json([
                'success' => false,
                'message' => 'This doctor is not currently accepting new patients'
            ], 400);
        }
        
        // Calculate end time based on appointment type and slot duration
        $appointmentDate = Carbon::parse($request->appointment_date);
        $startTime = Carbon::parse($request->start_time);
        $appointmentDateTime = $appointmentDate->copy()->setTimeFrom($startTime);
        
        // Get doctor's schedule for this day
        $dayOfWeek = $appointmentDate->format('l');
        $schedule = Schedule::where('doctor_id', $doctor->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();
        
        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor is not available on this day'
            ], 400);
        }
        
        // Check if the requested time is within the doctor's schedule
        $scheduleStart = Carbon::parse($schedule->start_time);
        $scheduleEnd = Carbon::parse($schedule->end_time);
        
        if ($startTime->lt($scheduleStart) || $startTime->gte($scheduleEnd)) {
            return response()->json([
                'success' => false,
                'message' => 'Requested time is outside doctor\'s available schedule'
            ], 400);
        }
        
        // Determine appointment duration based on type
        $appointmentDuration = $schedule->slot_duration; // default to slot duration
        if ($request->appointment_type === 'emergency') {
            $appointmentDuration = 60; // 1 hour for emergencies
        } elseif ($request->appointment_type === 'follow-up') {
            $appointmentDuration = 30; // 30 minutes for follow-ups
        }
        
        $endTime = $startTime->copy()->addMinutes($appointmentDuration);
        
        // Check for scheduling conflicts
        $conflictingAppointment = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $appointmentDate->toDateString())
            ->whereIn('status', ['scheduled', 'completed'])
            ->where(function($query) use ($startTime, $endTime) {
                $query->where(function($q) use ($startTime, $endTime) {
                    // Overlapping start time
                    $q->whereTime('start_time', '<', $endTime->format('H:i:s'))
                      ->whereTime('end_time', '>', $startTime->format('H:i:s'));
                });
            })
            ->first();
        
        if ($conflictingAppointment) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor already has an appointment scheduled during this time',
                'conflict' => [
                    'appointment_id' => $conflictingAppointment->id,
                    'start_time' => $conflictingAppointment->start_time,
                    'end_time' => $conflictingAppointment->end_time,
                    'status' => $conflictingAppointment->status
                ]
            ], 409); // 409 Conflict
        }
        
        // Check for patient conflicts (multiple appointments at same time)
        $patientConflict = Appointment::where('patient_id', $patient->id)
            ->whereDate('appointment_date', $appointmentDate->toDateString())
            ->whereIn('status', ['scheduled', 'completed'])
            ->where(function($query) use ($startTime, $endTime) {
                $query->where(function($q) use ($startTime, $endTime) {
                    $q->whereTime('start_time', '<', $endTime->format('H:i:s'))
                      ->whereTime('end_time', '>', $startTime->format('H:i:s'));
                });
            })
            ->first();
        
        if ($patientConflict) {
            return response()->json([
                'success' => false,
                'message' => 'Patient already has an appointment scheduled during this time',
                'conflict' => [
                    'appointment_id' => $patientConflict->id,
                    'start_time' => $patientConflict->start_time,
                    'end_time' => $patientConflict->end_time,
                    'status' => $patientConflict->status
                ]
            ], 409); // 409 Conflict
        }
        
        try {
            // Create the appointment
            $appointment = Appointment::create([
                'clinic_id' => $request->clinic_id,
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'created_by' => $user->id,
                'appointment_number' => $this->generateAppointmentNumber($request->clinic_id),
                'appointment_date' => $appointmentDate->toDateString(),
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'status' => 'scheduled',
                'appointment_type' => $request->appointment_type,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'priority' => $request->priority ?? 'normal',
                'is_first_visit' => $request->is_first_visit ?? false,
            ]);

            // Fire the appointment created event
            event(new AppointmentCreated($appointment));

            return response()->json([
                'success' => true,
                'message' => 'Appointment created successfully',
                'data' => Appointment::with(['clinic', 'patient.user', 'doctor.user', 'creator'])->find($appointment->id)
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get a specific appointment
     */
    public function show($id)
    {
        $appointment = Appointment::with(['clinic', 'patient.user', 'doctor.user', 'creator', 'bill', 'medicalRecords'])->find($id);
        
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
        
        // Patients can only access their own appointments
        if ($user->isPatient() && $appointment->patient_id != $user->patient->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this appointment'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $appointment
        ]);
    }
    
    /**
     * Update an appointment
     */
    public function update(AppointmentUpdateRequest $request, $id)
    {
        $appointment = Appointment::find($id);
        
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to update this appointment
        if (!$user->isAdmin() && !$user->isDoctor() && !$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update appointments'
            ], 403);
        }
        
        // Check if user has access to this clinic
        if (!$user->isAdmin() && $appointment->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this appointment'
            ], 403);
        }
        
        $validatedData = $request->validated();
        
        // Check for scheduling conflicts if date/time is being changed
        if ($request->has('appointment_date') || $request->has('start_time')) {
            $newDate = $request->appointment_date ?? $appointment->appointment_date;
            $newStartTime = $request->start_time ?? $appointment->start_time;
            
            $appointmentDate = Carbon::parse($newDate);
            $startTime = Carbon::parse($newStartTime);
            
            // Get doctor's schedule for this day
            $dayOfWeek = $appointmentDate->format('l');
            $schedule = Schedule::where('doctor_id', $appointment->doctor_id)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->first();
            
            if ($schedule) {
                // Check if the requested time is within the doctor's schedule
                $scheduleStart = Carbon::parse($schedule->start_time);
                $scheduleEnd = Carbon::parse($schedule->end_time);
                
                if ($startTime->lt($scheduleStart) || $startTime->gte($scheduleEnd)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Requested time is outside doctor\'s available schedule'
                    ], 400);
                }
                
                // Determine appointment duration
                $appointmentDuration = $schedule->slot_duration;
                if ($appointment->appointment_type === 'emergency') {
                    $appointmentDuration = 60;
                } elseif ($appointment->appointment_type === 'follow-up') {
                    $appointmentDuration = 30;
                }
                
                $endTime = $startTime->copy()->addMinutes($appointmentDuration);
                
                // Check for scheduling conflicts (excluding current appointment)
                $conflictingAppointment = Appointment::where('doctor_id', $appointment->doctor_id)
                    ->where('id', '!=', $appointment->id)
                    ->whereDate('appointment_date', $appointmentDate->toDateString())
                    ->whereIn('status', ['scheduled', 'completed'])
                    ->where(function($query) use ($startTime, $endTime) {
                        $query->where(function($q) use ($startTime, $endTime) {
                            $q->whereTime('start_time', '<', $endTime->format('H:i:s'))
                              ->whereTime('end_time', '>', $startTime->format('H:i:s'));
                        });
                    })
                    ->first();
                
                if ($conflictingAppointment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Doctor already has an appointment scheduled during this time',
                        'conflict' => [
                            'appointment_id' => $conflictingAppointment->id,
                            'start_time' => $conflictingAppointment->start_time,
                            'end_time' => $conflictingAppointment->end_time,
                            'status' => $conflictingAppointment->status
                        ]
                    ], 409);
                }
            }
        }
        
        try {
            // Track changes for notification
            $changes = [];
            $original = $appointment->getOriginal();

            foreach ($request->all() as $key => $value) {
                if (array_key_exists($key, $original) && $original[$key] != $value) {
                    $changes[$key] = [
                        'old' => $original[$key],
                        'new' => $value
                    ];
                }
            }

            // Update the appointment
            $appointment->update($request->all());

            // Fire the appointment updated event if there were changes
            if (!empty($changes)) {
                event(new AppointmentUpdated($appointment, $changes));
            }

            return response()->json([
                'success' => true,
                'message' => 'Appointment updated successfully',
                'data' => Appointment::with(['clinic', 'patient.user', 'doctor.user', 'creator'])->find($appointment->id)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Cancel an appointment
     */
    public function destroy($id)
    {
        $appointment = Appointment::find($id);
        
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to cancel this appointment
        if (!$user->isAdmin() && !$user->isDoctor() && !$user->isStaff() && !$user->isPatient()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to cancel appointments'
            ], 403);
        }
        
        // Check if user has access to this clinic
        if (!$user->isAdmin() && $appointment->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this appointment'
            ], 403);
        }
        
        // Patients can only cancel their own appointments
        if ($user->isPatient() && $appointment->patient_id != $user->patient->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to cancel this appointment'
            ], 403);
        }
        
        // Check if appointment can be cancelled (not already completed or cancelled)
        if (in_array($appointment->status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment cannot be cancelled as it is already ' . $appointment->status
            ], 400);
        }
        
        try {
            // Update status to cancelled instead of deleting
            $appointment->update([
                'status' => 'cancelled'
            ]);

            // Fire the appointment cancelled event
            event(new AppointmentCancelled($appointment));

            return response()->json([
                'success' => true,
                'message' => 'Appointment cancelled successfully',
                'data' => Appointment::with(['clinic', 'patient.user', 'doctor.user', 'creator'])->find($appointment->id)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mark appointment as completed
     */
    public function complete($id)
    {
        $appointment = Appointment::find($id);
        
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to complete this appointment
        if (!$user->isAdmin() && !$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Only doctors and admins can mark appointments as completed'
            ], 403);
        }
        
        // Check if user has access to this clinic
        if (!$user->isAdmin() && $appointment->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this appointment'
            ], 403);
        }
        
        // Check if appointment can be completed (must be scheduled)
        if ($appointment->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Only scheduled appointments can be marked as completed'
            ], 400);
        }
        
        try {
            // Update status to completed
            $appointment->update([
                'status' => 'completed'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Appointment marked as completed successfully',
                'data' => Appointment::with(['clinic', 'patient.user', 'doctor.user', 'creator'])->find($appointment->id)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark appointment as completed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mark appointment as no-show
     */
    public function markNoShow($id)
    {
        $appointment = Appointment::find($id);
        
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to mark no-show
        if (!$user->isAdmin() && !$user->isDoctor() && !$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to mark appointments as no-show'
            ], 403);
        }
        
        // Check if user has access to this clinic
        if (!$user->isAdmin() && $appointment->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this appointment'
            ], 403);
        }
        
        // Check if appointment can be marked as no-show (must be scheduled)
        if ($appointment->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Only scheduled appointments can be marked as no-show'
            ], 400);
        }
        
        try {
            // Update status to no-show
            $appointment->update([
                'status' => 'no-show'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Appointment marked as no-show successfully',
                'data' => Appointment::with(['clinic', 'patient.user', 'doctor.user', 'creator'])->find($appointment->id)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark appointment as no-show',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get appointments for a specific patient
     */
    public function getPatientAppointments($patientId)
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
        
        // Patients can only access their own appointments
        if ($user->isPatient() && $patient->id != $user->patient->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this patient'
            ], 403);
        }
        
        $appointments = $patient->appointments()->with(['clinic', 'doctor.user', 'creator'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }
    
    /**
     * Get appointments for a specific doctor
     */
    public function getDoctorAppointments($doctorId)
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
        
        $appointments = $doctor->appointments()->with(['clinic', 'patient.user', 'creator'])
            ->orderBy('appointment_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }
}