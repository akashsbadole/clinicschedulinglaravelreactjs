<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use App\Models\Clinic;
use App\Http\Requests\PatientStoreRequest;
use App\Http\Requests\PatientUpdateRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    /**
     * Get all patients for the current clinic
     */
    public function index()
    {
        $user = Auth::user();

        // Admin can see all patients, doctors/staff can see patients from their clinic
        if ($user->isAdmin()) {
            $patients = Patient::with(['user', 'clinic'])->get();
        } else {
            $patients = Patient::with(['user', 'clinic'])
                ->where('clinic_id', $user->clinic_id)
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }

    /**
     * Generate a unique patient ID
     */
    private function generatePatientId($clinicId)
    {
        $clinic = Clinic::find($clinicId);
        if (!$clinic) {
            throw new \Exception('Clinic not found');
        }

        // Generate a unique patient ID based on clinic prefix
        $prefix = strtoupper(substr($clinic->name, 0, 3)) . '-PAT-';

        // Find the highest existing patient ID for this clinic
        $lastPatient = Patient::where('clinic_id', $clinicId)
            ->where('patient_id', 'LIKE', $prefix . '%')
            ->orderBy('patient_id', 'desc')
            ->first();

        if ($lastPatient) {
            $lastNumber = intval(substr($lastPatient->patient_id, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    /**
     * Create a new patient
     */
    public function store(PatientStoreRequest $request)
    {
        $validatedData = $request->validated();

        $user = Auth::user();

        // Check if user has permission to create patients for this clinic
        if (!$user->isAdmin() && $request->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create patients for this clinic'
            ], 403);
        }

        try {
            // Create user account first
            $userData = [
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt(Str::random(12)), // Generate random password
                'role' => 'patient',
                'clinic_id' => $request->clinic_id,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
            ];

            $user = User::create($userData);

            // Create patient record with generated patient ID
            $patient = Patient::create([
                'clinic_id' => $request->clinic_id,
                'user_id' => $user->id,
                'patient_id' => $this->generatePatientId($request->clinic_id),
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'blood_type' => $request->blood_type,
                'allergies' => $request->allergies,
                'medical_history' => $request->medical_history,
                'insurance_provider' => $request->insurance_provider,
                'insurance_policy_number' => $request->insurance_policy_number,
                'last_visit_date' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Patient created successfully',
                'data' => Patient::with(['user', 'clinic'])->find($patient->id)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create patient',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific patient
     */
    public function show($id)
    {
        $patient = Patient::with(['user', 'clinic', 'appointments', 'bills'])->find($id);

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

        // Only load medical records if user is admin or doctor
        if ($user->isAdmin() || $user->isDoctor()) {
            $patient->load('medicalRecords');
        }

        return response()->json([
            'success' => true,
            'data' => $patient
        ]);
    }

    /**
     * Update a patient
     */
    public function update(PatientUpdateRequest $request, $id)
    {
        $patient = Patient::with('user')->find($id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }

        $user = Auth::user();

        // Check if user has permission to update this patient
        if (!$user->isAdmin() && $patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this patient'
            ], 403);
        }

        $validatedData = $request->validated();

        try {
            // Update User fields if present
            $userFields = ['first_name', 'last_name', 'email', 'phone', 'address', 'date_of_birth', 'gender'];
            $userData = [];

            // Handle name splitting/joining
            if (isset($validatedData['first_name']) || isset($validatedData['last_name'])) {
                $firstName = $validatedData['first_name'] ?? explode(' ', $patient->user->name)[0];
                $lastName = $validatedData['last_name'] ?? (count(explode(' ', $patient->user->name)) > 1 ? explode(' ', $patient->user->name)[1] : '');
                $userData['name'] = trim($firstName . ' ' . $lastName);
            }

            foreach ($userFields as $field) {
                if (isset($validatedData[$field]) && $field !== 'first_name' && $field !== 'last_name') {
                    $userData[$field] = $validatedData[$field];
                }
            }

            if (!empty($userData)) {
                $patient->user->update($userData);
            }

            // Update Patient fields
            $patient->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Patient updated successfully',
                'data' => Patient::with(['user', 'clinic'])->find($patient->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update patient',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a patient
     */
    public function destroy($id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }

        $user = Auth::user();

        // Only admins can delete patients
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can delete patients'
            ], 403);
        }

        try {
            // Soft delete the patient
            $patient->delete();

            return response()->json([
                'success' => true,
                'message' => 'Patient deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete patient',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patient's appointments
     */
    public function appointments($patientId)
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

        $appointments = $patient->appointments()->with(['doctor', 'clinic'])->get();

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }

    /**
     * Get patient's bills
     */
    public function bills($patientId)
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

        $bills = $patient->bills()->with(['doctor', 'clinic', 'appointment'])->get();

        return response()->json([
            'success' => true,
            'data' => $bills
        ]);
    }

    /**
     * Get patient's medical records
     */
    public function medicalRecords($patientId)
    {
        $patient = Patient::find($patientId);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }

        $user = Auth::user();

        // Check if user has access to this patient (only admin and doctors can access medical records)
        if (!$user->isAdmin() && !$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access medical records'
            ], 403);
        }

        if (!$user->isAdmin() && $patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this patient'
            ], 403);
        }

        $medicalRecords = $patient->medicalRecords()->with(['doctor', 'clinic', 'appointment'])->get();

        return response()->json([
            'success' => true,
            'data' => $medicalRecords
        ]);
    }
}