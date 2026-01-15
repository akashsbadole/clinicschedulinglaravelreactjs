<?php

namespace App\Http\Controllers;

use App\Models\ClinicalNote;
use App\Models\Encounter;
use App\Models\Patient;
use App\Http\Requests\ClinicalNotesStoreRequest;
use App\Http\Requests\ClinicalNotesUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClinicalNotesController extends Controller
{
    /**
     * Get all clinical notes for the current clinic
     */
    public function index()
    {
        $user = Auth::user();
        
        // Admin can see all clinical notes, doctors can see notes from their clinic
        if ($user->isAdmin()) {
            $clinicalNotes = ClinicalNote::with(['encounter', 'patient', 'doctor', 'createdBy'])->get();
        } else {
            $clinicalNotes = ClinicalNote::with(['encounter', 'patient', 'doctor', 'createdBy'])
                ->whereHas('encounter', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $clinicalNotes
        ]);
    }

    /**
     * Create a new clinical note
     */
    public function store(ClinicalNotesStoreRequest $request)
    {
        $validatedData = $request->validated();
        
        $user = Auth::user();
        
        // Check if user has permission to create clinical notes for this encounter
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
                'message' => 'Unauthorized to create clinical notes for this encounter'
            ], 403);
        }
        
        try {
            // Create clinical note
            $clinicalNote = ClinicalNote::create([
                'encounter_id' => $request->encounter_id,
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'created_by' => $user->id,
                'note_type' => $request->note_type,
                'subjective' => $request->subjective,
                'objective' => $request->objective,
                'assessment' => $request->assessment,
                'plan' => $request->plan,
                'clinical_impression' => $request->clinical_impression,
                'is_confidential' => $request->is_confidential ?? false,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Clinical note created successfully',
                'data' => ClinicalNote::with(['encounter', 'patient', 'doctor', 'createdBy'])->find($clinicalNote->id)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create clinical note',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific clinical note
     */
    public function show($id)
    {
        $clinicalNote = ClinicalNote::with(['encounter', 'patient', 'doctor', 'createdBy'])->find($id);
        
        if (!$clinicalNote) {
            return response()->json([
                'success' => false,
                'message' => 'Clinical note not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this clinical note
        if (!$user->isAdmin() && $clinicalNote->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this clinical note'
            ], 403);
        }
        
        // Check if note is confidential and user is not the creator or admin
        if ($clinicalNote->is_confidential && !$user->isAdmin() && $clinicalNote->created_by != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this confidential note'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $clinicalNote
        ]);
    }

    /**
     * Update a clinical note
     */
    public function update(ClinicalNotesUpdateRequest $request, $id)
    {
        $clinicalNote = ClinicalNote::find($id);
        
        if (!$clinicalNote) {
            return response()->json([
                'success' => false,
                'message' => 'Clinical note not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to update this clinical note
        if (!$user->isAdmin() && $clinicalNote->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this clinical note'
            ], 403);
        }
        
        // Only creator or admin can update the note
        if ($clinicalNote->created_by != $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the creator or admin can update this clinical note'
            ], 403);
        }
        
        $validatedData = $request->validated();
        
        try {
            $clinicalNote->update($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Clinical note updated successfully',
                'data' => ClinicalNote::with(['encounter', 'patient', 'doctor', 'createdBy'])->find($clinicalNote->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update clinical note',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get clinical notes for a specific encounter
     */
    public function getEncounterNotes($encounterId)
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
        
        $clinicalNotes = $encounter->clinicalNotes()->with(['doctor', 'createdBy'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $clinicalNotes
        ]);
    }

    /**
     * Get clinical notes for a specific patient
     */
    public function getPatientNotes($patientId)
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
        
        $clinicalNotes = $patient->clinicalNotes()->with(['encounter', 'doctor', 'createdBy'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $clinicalNotes
        ]);
    }

    /**
     * Get clinical notes by type
     */
    public function getNotesByType($type)
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $clinicalNotes = ClinicalNote::with(['encounter', 'patient', 'doctor', 'createdBy'])
                ->where('note_type', $type)
                ->get();
        } else {
            $clinicalNotes = ClinicalNote::with(['encounter', 'patient', 'doctor', 'createdBy'])
                ->whereHas('encounter', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->where('note_type', $type)
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $clinicalNotes
        ]);
    }

    /**
     * Search clinical notes
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        
        $query = ClinicalNote::query();
        
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
        
        if ($request->note_type) {
            $query->where('note_type', $request->note_type);
        }
        
        if ($request->search_text) {
            $searchText = $request->search_text;
            $query->where(function($q) use ($searchText) {
                $q->where('subjective', 'like', '%' . $searchText . '%')
                  ->orWhere('objective', 'like', '%' . $searchText . '%')
                  ->orWhere('assessment', 'like', '%' . $searchText . '%')
                  ->orWhere('plan', 'like', '%' . $searchText . '%')
                  ->orWhere('clinical_impression', 'like', '%' . $searchText . '%');
            });
        }
        
        $clinicalNotes = $query->with(['encounter', 'patient', 'doctor', 'createdBy'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $clinicalNotes
        ]);
    }
}