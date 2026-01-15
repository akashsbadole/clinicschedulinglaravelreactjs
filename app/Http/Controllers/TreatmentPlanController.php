<?php

namespace App\Http\Controllers;

use App\Models\TreatmentPlan;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\MedicationOrder;
use App\Http\Requests\TreatmentPlanStoreRequest;
use App\Http\Requests\TreatmentPlanUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TreatmentPlanController extends Controller
{
    /**
     * Get all treatment plans for the current clinic
     */
    public function index()
    {
        $user = Auth::user();
        
        // Admin can see all treatment plans, doctors can see treatment plans from their clinic
        if ($user->isAdmin()) {
            $treatmentPlans = TreatmentPlan::with(['encounter', 'patient', 'doctor', 'createdBy', 'medicationOrders'])->get();
        } else {
            $treatmentPlans = TreatmentPlan::with(['encounter', 'patient', 'doctor', 'createdBy', 'medicationOrders'])
                ->whereHas('encounter', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $treatmentPlans
        ]);
    }

    /**
     * Generate a unique treatment plan number
     */
    private function generateTreatmentPlanNumber($clinicId)
    {
        $prefix = 'TP-' . date('Y') . '-';
        
        // Find the highest existing treatment plan number for this clinic this year
        $lastPlan = TreatmentPlan::whereHas('encounter', function($query) use ($clinicId) {
                $query->where('clinic_id', $clinicId);
            })
            ->where('plan_number', 'LIKE', $prefix . '%')
            ->orderBy('plan_number', 'desc')
            ->first();
        
        if ($lastPlan) {
            $lastNumber = intval(substr($lastPlan->plan_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $newNumber;
    }

    /**
     * Create a new treatment plan
     */
    public function store(TreatmentPlanStoreRequest $request)
    {
        $validatedData = $request->validated();
        
        $user = Auth::user();
        
        // Check if user has permission to create treatment plans for this encounter
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
                'message' => 'Unauthorized to create treatment plans for this encounter'
            ], 403);
        }
        
        try {
            // Create treatment plan
            $treatmentPlan = TreatmentPlan::create([
                'encounter_id' => $request->encounter_id,
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'created_by' => $user->id,
                'plan_number' => $this->generateTreatmentPlanNumber($encounter->clinic_id),
                'plan_type' => $request->plan_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'goals' => $request->goals,
                'instructions' => $request->instructions,
                'status' => $request->status,
            ]);
            
            // Create medication orders if provided
            if ($request->medication_orders) {
                foreach ($request->medication_orders as $order) {
                    MedicationOrder::create([
                        'treatment_plan_id' => $treatmentPlan->id,
                        'patient_id' => $request->patient_id,
                        'doctor_id' => $request->doctor_id,
                        'prescribed_by' => $user->id,
                        'medication_id' => $order['medication_id'],
                        'dosage' => $order['dosage'],
                        'frequency' => $order['frequency'],
                        'route' => $order['route'],
                        'duration' => $order['duration'],
                        'instructions' => $order['instructions'] ?? null,
                        'quantity' => $order['quantity'] ?? null,
                        'refills' => $order['refills'] ?? 0,
                        'status' => $order['status'] ?? 'active',
                        'start_date' => $order['start_date'] ?? null,
                        'end_date' => $order['end_date'] ?? null,
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Treatment plan created successfully',
                'data' => TreatmentPlan::with(['encounter', 'patient', 'doctor', 'createdBy', 'medicationOrders'])->find($treatmentPlan->id)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create treatment plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific treatment plan
     */
    public function show($id)
    {
        $treatmentPlan = TreatmentPlan::with(['encounter', 'patient', 'doctor', 'createdBy', 'medicationOrders'])->find($id);
        
        if (!$treatmentPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Treatment plan not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this treatment plan
        if (!$user->isAdmin() && $treatmentPlan->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this treatment plan'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $treatmentPlan
        ]);
    }

    /**
     * Update a treatment plan
     */
    public function update(TreatmentPlanUpdateRequest $request, $id)
    {
        $treatmentPlan = TreatmentPlan::find($id);
        
        if (!$treatmentPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Treatment plan not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to update this treatment plan
        if (!$user->isAdmin() && $treatmentPlan->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this treatment plan'
            ], 403);
        }
        
        $validatedData = $request->validated();
        
        try {
            $treatmentPlan->update($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Treatment plan updated successfully',
                'data' => TreatmentPlan::with(['encounter', 'patient', 'doctor', 'createdBy', 'medicationOrders'])->find($treatmentPlan->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update treatment plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get treatment plans for a specific encounter
     */
    public function getEncounterTreatmentPlans($encounterId)
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
        
        $treatmentPlans = $encounter->treatmentPlans()->with(['doctor', 'createdBy', 'medicationOrders'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $treatmentPlans
        ]);
    }

    /**
     * Get treatment plans for a specific patient
     */
    public function getPatientTreatmentPlans($patientId)
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
        
        $treatmentPlans = $patient->treatmentPlans()->with(['encounter', 'doctor', 'createdBy', 'medicationOrders'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $treatmentPlans
        ]);
    }

    /**
     * Get treatment plans by type
     */
    public function getTreatmentPlansByType($type)
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $treatmentPlans = TreatmentPlan::with(['encounter', 'patient', 'doctor', 'createdBy', 'medicationOrders'])
                ->where('plan_type', $type)
                ->get();
        } else {
            $treatmentPlans = TreatmentPlan::with(['encounter', 'patient', 'doctor', 'createdBy', 'medicationOrders'])
                ->whereHas('encounter', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->where('plan_type', $type)
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $treatmentPlans
        ]);
    }

    /**
     * Get treatment plans by status
     */
    public function getTreatmentPlansByStatus($status)
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $treatmentPlans = TreatmentPlan::with(['encounter', 'patient', 'doctor', 'createdBy', 'medicationOrders'])
                ->where('status', $status)
                ->get();
        } else {
            $treatmentPlans = TreatmentPlan::with(['encounter', 'patient', 'doctor', 'createdBy', 'medicationOrders'])
                ->whereHas('encounter', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->where('status', $status)
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $treatmentPlans
        ]);
    }

    /**
     * Update treatment plan status
     */
    public function updateStatus(Request $request, $id)
    {
        $treatmentPlan = TreatmentPlan::find($id);
        
        if (!$treatmentPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Treatment plan not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to update this treatment plan status
        if (!$user->isAdmin() && $treatmentPlan->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this treatment plan status'
            ], 403);
        }
        
        $request->validate([
            'status' => 'required|string|in:active,completed,discontinued',
        ]);
        
        try {
            $treatmentPlan->update([
                'status' => $request->status,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Treatment plan status updated successfully',
                'data' => TreatmentPlan::with(['encounter', 'patient', 'doctor', 'createdBy', 'medicationOrders'])->find($treatmentPlan->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update treatment plan status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete a treatment plan
     */
    public function complete($id)
    {
        $treatmentPlan = TreatmentPlan::find($id);
        
        if (!$treatmentPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Treatment plan not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to complete this treatment plan
        if (!$user->isAdmin() && $treatmentPlan->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to complete this treatment plan'
            ], 403);
        }
        
        try {
            $treatmentPlan->update([
                'status' => 'completed',
                'completed_by' => $user->id,
                'completed_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Treatment plan completed successfully',
                'data' => TreatmentPlan::with(['encounter', 'patient', 'doctor', 'createdBy', 'medicationOrders'])->find($treatmentPlan->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete treatment plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Discontinue a treatment plan
     */
    public function discontinue($id)
    {
        $treatmentPlan = TreatmentPlan::find($id);
        
        if (!$treatmentPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Treatment plan not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to discontinue this treatment plan
        if (!$user->isAdmin() && $treatmentPlan->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to discontinue this treatment plan'
            ], 403);
        }
        
        try {
            $treatmentPlan->update([
                'status' => 'discontinued',
                'discontinued_by' => $user->id,
                'discontinued_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Treatment plan discontinued successfully',
                'data' => TreatmentPlan::with(['encounter', 'patient', 'doctor', 'createdBy', 'medicationOrders'])->find($treatmentPlan->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to discontinue treatment plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search treatment plans
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        
        $query = TreatmentPlan::query();
        
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
        
        if ($request->plan_type) {
            $query->where('plan_type', $request->plan_type);
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->search_text) {
            $searchText = $request->search_text;
            $query->where(function($q) use ($searchText) {
                $q->where('plan_number', 'like', '%' . $searchText . '%')
                  ->orWhere('goals', 'like', '%' . $searchText . '%')
                  ->orWhere('instructions', 'like', '%' . $searchText . '%');
            });
        }
        
        $treatmentPlans = $query->with(['encounter', 'patient', 'doctor', 'createdBy', 'medicationOrders'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $treatmentPlans
        ]);
    }

    /**
     * Get active treatment plans for a patient
     */
    public function getActivePatientTreatmentPlans($patientId)
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
        
        $treatmentPlans = $patient->treatmentPlans()->with(['encounter', 'doctor', 'createdBy', 'medicationOrders'])
            ->where('status', 'active')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $treatmentPlans
        ]);
    }

    /**
     * Get medication catalog
     */
    public function getMedicationCatalog()
    {
        $medications = \App\Models\Medication::all();
        
        return response()->json([
            'success' => true,
            'data' => $medications
        ]);
    }
}