<?php

namespace App\Http\Controllers;

use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Http\Requests\EncounterStoreRequest;
use App\Http\Requests\EncounterUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EncounterController extends Controller
{
    /**
     * Get all encounters for the current clinic
     */
    public function index()
    {
        $user = Auth::user();
        
        // Admin can see all encounters, doctors/staff can see encounters from their clinic
        if ($user->isAdmin()) {
            $encounters = Encounter::with(['patient', 'doctor', 'appointment', 'createdBy'])->get();
        } else {
            $encounters = Encounter::with(['patient', 'doctor', 'appointment', 'createdBy'])
                ->where('clinic_id', $user->clinic_id)
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $encounters
        ]);
    }

    /**
     * Generate a unique encounter number
     */
    private function generateEncounterNumber($clinicId)
    {
        $prefix = 'ENC-' . date('Y') . '-';
        
        // Find the highest existing encounter number for this clinic this year
        $lastEncounter = Encounter::where('clinic_id', $clinicId)
            ->where('encounter_number', 'LIKE', $prefix . '%')
            ->orderBy('encounter_number', 'desc')
            ->first();
        
        if ($lastEncounter) {
            $lastNumber = intval(substr($lastEncounter->encounter_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $newNumber;
    }

    /**
     * Create a new encounter
     */
    public function store(EncounterStoreRequest $request)
    {
        $validatedData = $request->validated();
        
        $user = Auth::user();
        
        // Check if user has permission to create encounters for this clinic
        if (!$user->isAdmin() && $request->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create encounters for this clinic'
            ], 403);
        }
        
        try {
            // Create encounter
            $encounter = Encounter::create([
                'clinic_id' => $request->clinic_id,
                'patient_id' => $request->patient_id,
                'appointment_id' => $request->appointment_id,
                'doctor_id' => $request->doctor_id,
                'created_by' => $user->id,
                'encounter_type' => $request->encounter_type,
                'encounter_number' => $this->generateEncounterNumber($request->clinic_id),
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'status' => $request->status,
                'priority' => $request->priority,
                'chief_complaint' => $request->chief_complaint,
                'history_of_present_illness' => $request->history_of_present_illness,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Encounter created successfully',
                'data' => Encounter::with(['patient', 'doctor', 'appointment', 'createdBy'])->find($encounter->id)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create encounter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific encounter
     */
    public function show($id)
    {
        $encounter = Encounter::with(['patient', 'doctor', 'appointment', 'createdBy', 'vitalSigns', 'clinicalNotes', 'diagnoses', 'labOrders', 'procedures'])->find($id);
        
        if (!$encounter) {
            return response()->json([
                'success' => false,
                'message' => 'Encounter not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this encounter
        if (!$user->isAdmin() && $encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this encounter'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $encounter
        ]);
    }

    /**
     * Update an encounter
     */
    public function update(EncounterUpdateRequest $request, $id)
    {
        $encounter = Encounter::find($id);
        
        if (!$encounter) {
            return response()->json([
                'success' => false,
                'message' => 'Encounter not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to update this encounter
        if (!$user->isAdmin() && $encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this encounter'
            ], 403);
        }
        
        $validatedData = $request->validated();
        
        try {
            $encounter->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Encounter updated successfully',
                'data' => Encounter::with(['patient', 'doctor', 'appointment', 'createdBy'])->find($encounter->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update encounter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete an encounter
     */
    public function complete($id)
    {
        $encounter = Encounter::find($id);
        
        if (!$encounter) {
            return response()->json([
                'success' => false,
                'message' => 'Encounter not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to complete this encounter
        if (!$user->isAdmin() && $encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to complete this encounter'
            ], 403);
        }
        
        try {
            $encounter->update([
                'status' => 'completed',
                'end_time' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Encounter completed successfully',
                'data' => Encounter::with(['patient', 'doctor', 'appointment', 'createdBy'])->find($encounter->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete encounter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get encounters for a specific patient
     */
    public function getPatientEncounters($patientId)
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
        
        $encounters = $patient->encounters()->with(['doctor', 'appointment', 'createdBy'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $encounters
        ]);
    }

    /**
     * Get active encounters for the current clinic
     */
    public function getActiveEncounters()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $encounters = Encounter::with(['patient', 'doctor', 'appointment', 'createdBy'])
                ->where('status', 'active')
                ->get();
        } else {
            $encounters = Encounter::with(['patient', 'doctor', 'appointment', 'createdBy'])
                ->where('clinic_id', $user->clinic_id)
                ->where('status', 'active')
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $encounters
        ]);
    }

    /**
     * Get encounters by status
     */
    public function getEncountersByStatus($status)
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $encounters = Encounter::with(['patient', 'doctor', 'appointment', 'createdBy'])
                ->where('status', $status)
                ->get();
        } else {
            $encounters = Encounter::with(['patient', 'doctor', 'appointment', 'createdBy'])
                ->where('clinic_id', $user->clinic_id)
                ->where('status', $status)
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $encounters
        ]);
    }
}