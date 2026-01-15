<?php

namespace App\Http\Controllers;

use App\Models\LabOrder;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\TestCatalog;
use App\Models\LabTest;
use App\Http\Requests\LabOrderStoreRequest;
use App\Http\Requests\LabOrderUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LabOrderController extends Controller
{
    /**
     * Get all lab orders for the current clinic
     */
    public function index()
    {
        $user = Auth::user();
        
        // Admin can see all lab orders, doctors/staff can see lab orders from their clinic
        if ($user->isAdmin()) {
            $labOrders = LabOrder::with(['encounter', 'patient', 'doctor', 'orderedBy', 'labTests'])->get();
        } else {
            $labOrders = LabOrder::with(['encounter', 'patient', 'doctor', 'orderedBy', 'labTests'])
                ->whereHas('encounter', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $labOrders
        ]);
    }

    /**
     * Generate a unique lab order number
     */
    private function generateLabOrderNumber($clinicId)
    {
        $prefix = 'LAB-' . date('Y') . '-';
        
        // Find the highest existing lab order number for this clinic this year
        $lastOrder = LabOrder::whereHas('encounter', function($query) use ($clinicId) {
                $query->where('clinic_id', $clinicId);
            })
            ->where('order_number', 'LIKE', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();
        
        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->order_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $newNumber;
    }

    /**
     * Create a new lab order
     */
    public function store(LabOrderStoreRequest $request)
    {
        $validatedData = $request->validated();
        
        $user = Auth::user();
        
        // Check if user has permission to create lab orders for this encounter
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
                'message' => 'Unauthorized to create lab orders for this encounter'
            ], 403);
        }
        
        try {
            // Create lab order
            $labOrder = LabOrder::create([
                'encounter_id' => $request->encounter_id,
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'ordered_by' => $user->id,
                'order_number' => $this->generateLabOrderNumber($encounter->clinic_id),
                'priority' => $request->priority,
                'status' => $request->status,
                'clinical_indication' => $request->clinical_indication,
                'specimen_collection_time' => $request->specimen_collection_time,
                'results_available_time' => $request->results_available_time,
            ]);
            
            // Create lab tests if provided
            if ($request->lab_tests) {
                foreach ($request->lab_tests as $test) {
                    LabTest::create([
                        'lab_order_id' => $labOrder->id,
                        'test_catalog_id' => $test['test_catalog_id'],
                        'test_name' => $test['test_name'],
                        'test_code' => $test['test_code'],
                        'specimen_type' => $test['specimen_type'],
                        'status' => 'pending',
                        'notes' => $test['notes'] ?? null,
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Lab order created successfully',
                'data' => LabOrder::with(['encounter', 'patient', 'doctor', 'orderedBy', 'labTests'])->find($labOrder->id)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create lab order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific lab order
     */
    public function show($id)
    {
        $labOrder = LabOrder::with(['encounter', 'patient', 'doctor', 'orderedBy', 'labTests'])->find($id);
        
        if (!$labOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Lab order not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this lab order
        if (!$user->isAdmin() && $labOrder->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this lab order'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $labOrder
        ]);
    }

    /**
     * Update a lab order
     */
    public function update(LabOrderUpdateRequest $request, $id)
    {
        $labOrder = LabOrder::find($id);
        
        if (!$labOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Lab order not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to update this lab order
        if (!$user->isAdmin() && $labOrder->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this lab order'
            ], 403);
        }
        
        $validatedData = $request->validated();
        
        try {
            $labOrder->update($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Lab order updated successfully',
                'data' => LabOrder::with(['encounter', 'patient', 'doctor', 'orderedBy', 'labTests'])->find($labOrder->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lab order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lab orders for a specific encounter
     */
    public function getEncounterLabOrders($encounterId)
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
        
        $labOrders = $encounter->labOrders()->with(['doctor', 'orderedBy', 'labTests'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $labOrders
        ]);
    }

    /**
     * Get lab orders for a specific patient
     */
    public function getPatientLabOrders($patientId)
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
        
        $labOrders = $patient->labOrders()->with(['encounter', 'doctor', 'orderedBy', 'labTests'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $labOrders
        ]);
    }

    /**
     * Get lab orders by status
     */
    public function getLabOrdersByStatus($status)
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $labOrders = LabOrder::with(['encounter', 'patient', 'doctor', 'orderedBy', 'labTests'])
                ->where('status', $status)
                ->get();
        } else {
            $labOrders = LabOrder::with(['encounter', 'patient', 'doctor', 'orderedBy', 'labTests'])
                ->whereHas('encounter', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->where('status', $status)
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $labOrders
        ]);
    }

    /**
     * Update lab order status
     */
    public function updateStatus(Request $request, $id)
    {
        $labOrder = LabOrder::find($id);
        
        if (!$labOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Lab order not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to update this lab order status
        if (!$user->isAdmin() && $labOrder->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this lab order status'
            ], 403);
        }
        
        $request->validate([
            'status' => 'required|string|in:ordered,processing,completed,cancelled',
        ]);
        
        try {
            $labOrder->update([
                'status' => $request->status,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Lab order status updated successfully',
                'data' => LabOrder::with(['encounter', 'patient', 'doctor', 'orderedBy', 'labTests'])->find($labOrder->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lab order status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a lab order
     */
    public function cancel($id)
    {
        $labOrder = LabOrder::find($id);
        
        if (!$labOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Lab order not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to cancel this lab order
        if (!$user->isAdmin() && $labOrder->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to cancel this lab order'
            ], 403);
        }
        
        try {
            $labOrder->update([
                'status' => 'cancelled',
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Lab order cancelled successfully',
                'data' => LabOrder::with(['encounter', 'patient', 'doctor', 'orderedBy', 'labTests'])->find($labOrder->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel lab order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search lab orders
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        
        $query = LabOrder::query();
        
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
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->priority) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->search_text) {
            $searchText = $request->search_text;
            $query->where(function($q) use ($searchText) {
                $q->where('order_number', 'like', '%' . $searchText . '%')
                  ->orWhere('clinical_indication', 'like', '%' . $searchText . '%');
            });
        }
        
        $labOrders = $query->with(['encounter', 'patient', 'doctor', 'orderedBy', 'labTests'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $labOrders
        ]);
    }

    /**
     * Get test catalog
     */
    public function getTestCatalog()
    {
        $testCatalog = TestCatalog::all();
        
        return response()->json([
            'success' => true,
            'data' => $testCatalog
        ]);
    }

    /**
     * Add test results to a lab test
     */
    public function addTestResults(Request $request, $labTestId)
    {
        $labTest = LabTest::find($labTestId);
        
        if (!$labTest) {
            return response()->json([
                'success' => false,
                'message' => 'Lab test not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to add results to this lab test
        $labOrder = $labTest->labOrder;
        if (!$user->isAdmin() && $labOrder->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to add results to this lab test'
            ], 403);
        }
        
        $request->validate([
            'results' => 'required|string|max:2000',
            'reference_range' => 'nullable|string|max:500',
            'units' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:pending,processing,completed',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        try {
            $labTest->update([
                'results' => $request->results,
                'reference_range' => $request->reference_range,
                'units' => $request->units,
                'status' => $request->status ?? 'completed',
                'notes' => $request->notes,
                'completed_by' => $user->id,
                'completed_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Test results added successfully',
                'data' => $labTest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add test results',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}