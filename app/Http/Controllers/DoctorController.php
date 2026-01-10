<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Schedule;
use App\Http\Requests\DoctorStoreRequest;
use App\Http\Requests\DoctorUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    /**
     * Get all doctors for the current clinic
     */
    public function index()
    {
        $user = Auth::user();
        
        // Admin can see all doctors, staff can see doctors from their clinic
        if ($user->isAdmin()) {
            $doctors = Doctor::with(['user', 'clinic', 'schedules'])->get();
        } else {
            $doctors = Doctor::with(['user', 'clinic', 'schedules'])
                ->where('clinic_id', $user->clinic_id)
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $doctors
        ]);
    }

    /**
     * Generate a unique doctor ID
     */
    private function generateDoctorId($clinicId)
    {
        $clinic = Clinic::find($clinicId);
        if (!$clinic) {
            throw new \Exception('Clinic not found');
        }
        
        // Generate a unique doctor ID based on clinic prefix
        $prefix = strtoupper(substr($clinic->name, 0, 3)) . '-DOC-';
        
        // Find the highest existing doctor ID for this clinic
        $lastDoctor = Doctor::where('clinic_id', $clinicId)
            ->where('doctor_id', 'LIKE', $prefix . '%')
            ->orderBy('doctor_id', 'desc')
            ->first();
        
        if ($lastDoctor) {
            $lastNumber = intval(substr($lastDoctor->doctor_id, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $newNumber;
    }

    /**
     * Create a new doctor
     */
    public function store(DoctorStoreRequest $request)
    {
        $validatedData = $request->validated();
        
        $user = Auth::user();
        
        // Check if user has permission to create doctors for this clinic
        if (!$user->isAdmin() && $request->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create doctors for this clinic'
            ], 403);
        }

        try {
            // Create user account first
            $userData = [
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt(Str::random(12)), // Generate random password
                'role' => 'doctor',
                'clinic_id' => $request->clinic_id,
                'specialization' => $request->specialization,
                'license_number' => $request->license_number,
            ];

            $user = User::create($userData);

            // Create doctor record with generated doctor ID
            $doctor = Doctor::create([
                'clinic_id' => $request->clinic_id,
                'user_id' => $user->id,
                'doctor_id' => $this->generateDoctorId($request->clinic_id),
                'specialization' => $request->specialization,
                'license_number' => $request->license_number,
                'qualifications' => $request->qualifications,
                'years_of_experience' => $request->years_of_experience,
                'consultation_fee' => $request->consultation_fee,
                'bio' => $request->bio,
                'availability' => $request->availability ?? 'Monday-Friday',
                'is_accepting_new_patients' => $request->is_accepting_new_patients ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Doctor created successfully',
                'data' => Doctor::with(['user', 'clinic', 'schedules'])->find($doctor->id)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create doctor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific doctor
     */
    public function show($id)
    {
        $doctor = Doctor::with(['user', 'clinic', 'appointments', 'bills', 'medicalRecords', 'schedules'])->find($id);

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

        return response()->json([
            'success' => true,
            'data' => $doctor
        ]);
    }

    /**
     * Update a doctor
     */
    public function update(DoctorUpdateRequest $request, $id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found'
            ], 404);
        }

        $user = Auth::user();
        
        // Check if user has permission to update this doctor
        if (!$user->isAdmin() && !$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update doctor information'
            ], 403);
        }

        if (!$user->isAdmin() && $doctor->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this doctor'
            ], 403);
        }

        $validatedData = $request->validated();

        try {
            $doctor->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Doctor updated successfully',
                'data' => Doctor::with(['user', 'clinic', 'schedules'])->find($doctor->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update doctor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a doctor
     */
    public function destroy($id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found'
            ], 404);
        }

        $user = Auth::user();
        
        // Only admins can delete doctors
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can delete doctors'
            ], 403);
        }

        try {
            // Soft delete the doctor
            $doctor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Doctor deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete doctor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctor's appointments
     */
    public function appointments($doctorId)
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

        $appointments = $doctor->appointments()->with(['patient', 'clinic'])->get();

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }

    /**
     * Get doctor's schedule
     */
    public function schedule($doctorId)
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

        $schedule = $doctor->schedules()->orderBy('day_of_week')->get();

        return response()->json([
            'success' => true,
            'data' => $schedule
        ]);
    }

    /**
     * Update doctor's schedule
     */
    public function updateSchedule(Request $request, $doctorId)
    {
        $doctor = Doctor::find($doctorId);

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found'
            ], 404);
        }

        $user = Auth::user();
        
        // Check if user has permission to update this doctor's schedule
        if (!$user->isAdmin() && !$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update doctor schedule'
            ], 403);
        }

        if (!$user->isAdmin() && $doctor->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this doctor'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'schedule' => 'required|array',
            'schedule.*.day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'schedule.*.start_time' => 'required|date_format:H:i',
            'schedule.*.end_time' => 'required|date_format:H:i|after:schedule.*.start_time',
            'schedule.*.slot_duration' => 'nullable|integer|min:5|max:120',
            'schedule.*.is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Delete existing schedule
            Schedule::where('doctor_id', $doctorId)->delete();

            // Create new schedule
            foreach ($request->schedule as $scheduleItem) {
                Schedule::create([
                    'clinic_id' => $doctor->clinic_id,
                    'doctor_id' => $doctorId,
                    'day_of_week' => $scheduleItem['day_of_week'],
                    'start_time' => $scheduleItem['start_time'],
                    'end_time' => $scheduleItem['end_time'],
                    'slot_duration' => $scheduleItem['slot_duration'] ?? 30,
                    'is_active' => $scheduleItem['is_active'] ?? true,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Doctor schedule updated successfully',
                'data' => $doctor->schedules()->orderBy('day_of_week')->get()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update doctor schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctor's bills
     */
    public function bills($doctorId)
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

        $bills = $doctor->bills()->with(['patient', 'clinic', 'appointment'])->get();

        return response()->json([
            'success' => true,
            'data' => $bills
        ]);
    }
}