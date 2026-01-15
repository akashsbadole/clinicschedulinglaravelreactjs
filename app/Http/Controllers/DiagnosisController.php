<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Icd10Code;
use App\Http\Requests\DiagnosisStoreRequest;
use App\Http\Requests\DiagnosisUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiagnosisController extends Controller
{
    /**
     * Get all diagnoses for the current clinic
     */
    public function index()
    {
        $user = Auth::user();
        
        // Admin can see all diagnoses, doctors can see diagnoses from their clinic
        if ($user->isAdmin()) {
            $diagnoses = Diagnosis::with(['encounter', 'patient', 'doctor', 'createdBy', 'icd10Codes'])->get();
        } else {
            $diagnoses = Diagnosis::with(['encounter', 'patient', 'doctor', 'createdBy', 'icd10Codes'])
                ->whereHas('encounter', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $diagnoses
        ]);
    }

    /**
     * Create a new diagnosis
     */
    public function store(DiagnosisStoreRequest $request)
    {
        $validatedData = $request->validated();
        
        $user = Auth::user();
        
        // Check if user has permission to create diagnoses for this encounter
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
                'message' => 'Unauthorized to create diagnoses for this encounter'
            ], 403);
        }
        
        try {
            // Create diagnosis
            $diagnosis = Diagnosis::create([
                'encounter_id' => $request->encounter_id,
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'created_by' => $user->id,
                'diagnosis_type' => $request->diagnosis_type,
                'clinical_description' => $request->clinical_description,
                'date_of_diagnosis' => $request->date_of_diagnosis,
                'diagnosis_status' => $request->diagnosis_status,
                'supporting_evidence' => $request->supporting_evidence,
                'is_confirmed' => $request->is_confirmed ?? false,
            ]);
            
            // Attach ICD-10 codes if provided
            if ($request->icd10_codes) {
                $diagnosis->icd10Codes()->attach($request->icd10_codes);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Diagnosis created successfully',
                'data' => Diagnosis::with(['encounter', 'patient', 'doctor', 'createdBy', 'icd10Codes'])->find($diagnosis->id)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create diagnosis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific diagnosis
     */
    public function show($id)
    {
        $diagnosis = Diagnosis::with(['encounter', 'patient', 'doctor', 'createdBy', 'icd10Codes'])->find($id);
        
        if (!$diagnosis) {
            return response()->json([
                'success' => false,
                'message' => 'Diagnosis not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this diagnosis
        if (!$user->isAdmin() && $diagnosis->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this diagnosis'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $diagnosis
        ]);
    }

    /**
     * Update a diagnosis
     */
    public function update(DiagnosisUpdateRequest $request, $id)
    {
        $diagnosis = Diagnosis::find($id);
        
        if (!$diagnosis) {
            return response()->json([
                'success' => false,
                'message' => 'Diagnosis not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to update this diagnosis
        if (!$user->isAdmin() && $diagnosis->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this diagnosis'
            ], 403);
        }
        
        // Only creator or admin can update the diagnosis
        if ($diagnosis->created_by != $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the creator or admin can update this diagnosis'
            ], 403);
        }
        
        $validatedData = $request->validated();
        
        try {
            $diagnosis->update($validatedData);
            
            // Update ICD-10 codes if provided
            if ($request->icd10_codes) {
                $diagnosis->icd10Codes()->sync($request->icd10_codes);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Diagnosis updated successfully',
                'data' => Diagnosis::with(['encounter', 'patient', 'doctor', 'createdBy', 'icd10Codes'])->find($diagnosis->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update diagnosis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get diagnoses for a specific encounter
     */
    public function getEncounterDiagnoses($encounterId)
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
        
        $diagnoses = $encounter->diagnoses()->with(['doctor', 'createdBy', 'icd10Codes'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $diagnoses
        ]);
    }

    /**
     * Get diagnoses for a specific patient
     */
    public function getPatientDiagnoses($patientId)
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
        
        $diagnoses = $patient->diagnoses()->with(['encounter', 'doctor', 'createdBy', 'icd10Codes'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $diagnoses
        ]);
    }

    /**
     * Get diagnoses by type
     */
    public function getDiagnosesByType($type)
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $diagnoses = Diagnosis::with(['encounter', 'patient', 'doctor', 'createdBy', 'icd10Codes'])
                ->where('diagnosis_type', $type)
                ->get();
        } else {
            $diagnoses = Diagnosis::with(['encounter', 'patient', 'doctor', 'createdBy', 'icd10Codes'])
                ->whereHas('encounter', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->where('diagnosis_type', $type)
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $diagnoses
        ]);
    }

    /**
     * Get diagnoses by status
     */
    public function getDiagnosesByStatus($status)
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $diagnoses = Diagnosis::with(['encounter', 'patient', 'doctor', 'createdBy', 'icd10Codes'])
                ->where('diagnosis_status', $status)
                ->get();
        } else {
            $diagnoses = Diagnosis::with(['encounter', 'patient', 'doctor', 'createdBy', 'icd10Codes'])
                ->whereHas('encounter', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->where('diagnosis_status', $status)
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $diagnoses
        ]);
    }

    /**
     * Search diagnoses
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        
        $query = Diagnosis::query();
        
        if (!$user->isAdmin()) {
            $query->whereHas('encounter', function($query) use ($user) {
                $query->where('clinic_id', $user->clinic_id);
            });
        }
        
        if ($request->patient_id) {
            $query->where('patient_id', $request->patient_id);
        }
        
        if ($request->doctor_id) {
            $query->where('doctor_id', $request->doctor_id);
        }
        
        if ($request->diagnosis_type) {
            $query->where('diagnosis_type', $request->diagnosis_type);
        }
        
        if ($request->diagnosis_status) {
            $query->where('diagnosis_status', $request->diagnosis_status);
        }
        
        if ($request->icd10_code) {
            $query->whereHas('icd10Codes', function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->icd10_code . '%');
            });
        }
        
        if ($request->search_text) {
            $searchText = $request->search_text;
            $query->where(function($q) use ($searchText) {
                $q->where('clinical_description', 'like', '%' . $searchText . '%')
                  ->orWhere('supporting_evidence', 'like', '%' . $searchText . '%');
            });
        }
        
        $diagnoses = $query->with(['encounter', 'patient', 'doctor', 'createdBy', 'icd10Codes'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $diagnoses
        ]);
    }

    /**
     * Confirm a diagnosis
     */
    public function confirm($id)
    {
        $diagnosis = Diagnosis::find($id);
        
        if (!$diagnosis) {
            return response()->json([
                'success' => false,
                'message' => 'Diagnosis not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to confirm this diagnosis
        if (!$user->isAdmin() && $diagnosis->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to confirm this diagnosis'
            ], 403);
        }
        
        // Only doctor or admin can confirm a diagnosis
        if (!$user->isDoctor() && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only doctors or admins can confirm diagnoses'
            ], 403);
        }
        
        try {
            $diagnosis->update([
                'is_confirmed' => true,
                'confirmed_by' => $user->id,
                'confirmed_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Diagnosis confirmed successfully',
                'data' => Diagnosis::with(['encounter', 'patient', 'doctor', 'createdBy', 'icd10Codes'])->find($diagnosis->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm diagnosis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve a diagnosis
     */
    public function resolve($id)
    {
        $diagnosis = Diagnosis::find($id);
        
        if (!$diagnosis) {
            return response()->json([
                'success' => false,
                'message' => 'Diagnosis not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to resolve this diagnosis
        if (!$user->isAdmin() && $diagnosis->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to resolve this diagnosis'
            ], 403);
        }
        
        // Only doctor or admin can resolve a diagnosis
        if (!$user->isDoctor() && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only doctors or admins can resolve diagnoses'
            ], 403);
        }
        
        try {
            $diagnosis->update([
                'diagnosis_status' => 'resolved',
                'resolved_by' => $user->id,
                'resolved_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Diagnosis resolved successfully',
                'data' => Diagnosis::with(['encounter', 'patient', 'doctor', 'createdBy', 'icd10Codes'])->find($diagnosis->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve diagnosis',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}