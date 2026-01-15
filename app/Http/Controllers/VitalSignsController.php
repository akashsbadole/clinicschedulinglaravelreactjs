<?php

namespace App\Http\Controllers;

use App\Models\VitalSign;
use App\Models\Encounter;
use App\Models\Patient;
use App\Http\Requests\VitalSignsStoreRequest;
use App\Http\Requests\VitalSignsUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VitalSignsController extends Controller
{
    /**
     * Get all vital signs for the current clinic
     */
    public function index()
    {
        $user = Auth::user();
        
        // Admin can see all vital signs, doctors/staff can see vital signs from their clinic
        if ($user->isAdmin()) {
            $vitalSigns = VitalSign::with(['encounter', 'patient', 'recordedBy'])->get();
        } else {
            $vitalSigns = VitalSign::with(['encounter', 'patient', 'recordedBy'])
                ->whereHas('encounter', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $vitalSigns
        ]);
    }

    /**
     * Create new vital signs
     */
    public function store(VitalSignsStoreRequest $request)
    {
        $validatedData = $request->validated();
        
        $user = Auth::user();
        
        // Check if user has permission to create vital signs for this encounter
        $encounter = Encounter::find($request->encounter_id);
        if (!$encounter) {
            return response()->json([
                'success' => false,
                'message' => 'Encounter not found'
            ], 404);
        }
        
        if (!$user->isAdmin() && $encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create vital signs for this encounter'
            ], 403);
        }
        
        try {
            // Calculate BMI if height and weight are provided
            $bmi = null;
            if ($request->height && $request->weight) {
                $heightInMeters = $request->height / 100;
                $bmi = $request->weight / ($heightInMeters * $heightInMeters);
            }
            
            // Create vital signs record
            $vitalSigns = VitalSign::create([
                'encounter_id' => $request->encounter_id,
                'patient_id' => $request->patient_id,
                'recorded_by' => $user->id,
                'height' => $request->height,
                'weight' => $request->weight,
                'bmi' => $bmi,
                'temperature' => $request->temperature,
                'systolic_bp' => $request->systolic_bp,
                'diastolic_bp' => $request->diastolic_bp,
                'pulse_rate' => $request->pulse_rate,
                'respiratory_rate' => $request->respiratory_rate,
                'oxygen_saturation' => $request->oxygen_saturation,
                'notes' => $request->notes,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Vital signs recorded successfully',
                'data' => VitalSign::with(['encounter', 'patient', 'recordedBy'])->find($vitalSigns->id)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record vital signs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific vital signs record
     */
    public function show($id)
    {
        $vitalSigns = VitalSign::with(['encounter', 'patient', 'recordedBy'])->find($id);
        
        if (!$vitalSigns) {
            return response()->json([
                'success' => false,
                'message' => 'Vital signs record not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this vital signs record
        if (!$user->isAdmin() && $vitalSigns->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this vital signs record'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $vitalSigns
        ]);
    }

    /**
     * Update a vital signs record
     */
    public function update(VitalSignsUpdateRequest $request, $id)
    {
        $vitalSigns = VitalSign::find($id);
        
        if (!$vitalSigns) {
            return response()->json([
                'success' => false,
                'message' => 'Vital signs record not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to update this vital signs record
        if (!$user->isAdmin() && $vitalSigns->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this vital signs record'
            ], 403);
        }
        
        $validatedData = $request->validated();
        
        try {
            // Calculate BMI if height and weight are provided
            if ($request->height && $request->weight) {
                $heightInMeters = $request->height / 100;
                $validatedData['bmi'] = $request->weight / ($heightInMeters * $heightInMeters);
            }
            
            $vitalSigns->update($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Vital signs updated successfully',
                'data' => VitalSign::with(['encounter', 'patient', 'recordedBy'])->find($vitalSigns->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update vital signs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get vital signs for a specific encounter
     */
    public function getEncounterVitalSigns($encounterId)
    {
        $encounter = Encounter::find($encounterId);
        
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
        
        $vitalSigns = $encounter->vitalSigns()->with(['recordedBy'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $vitalSigns
        ]);
    }

    /**
     * Get vital signs for a specific patient
     */
    public function getPatientVitalSigns($patientId)
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
        
        $vitalSigns = $patient->vitalSigns()->with(['encounter', 'recordedBy'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $vitalSigns
        ]);
    }

    /**
     * Get latest vital signs for a specific patient
     */
    public function getLatestPatientVitalSigns($patientId)
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
        
        $vitalSigns = $patient->vitalSigns()->with(['encounter', 'recordedBy'])
            ->orderBy('created_at', 'desc')
            ->first();
        
        return response()->json([
            'success' => true,
            'data' => $vitalSigns
        ]);
    }
}