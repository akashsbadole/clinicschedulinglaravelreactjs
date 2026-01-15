<?php

namespace App\Http\Controllers;

use App\Models\Procedure;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\ProcedureCatalog;
use App\Http\Requests\ProcedureStoreRequest;
use App\Http\Requests\ProcedureUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProcedureController extends Controller
{
    /**
     * Get all procedures for the current clinic
     */
    public function index()
    {
        $user = Auth::user();
        
        // Admin can see all procedures, doctors/staff can see procedures from their clinic
        if ($user->isAdmin()) {
            $procedures = Procedure::with(['encounter', 'patient', 'doctor', 'performedBy', 'procedureCatalog'])->get();
        } else {
            $procedures = Procedure::with(['encounter', 'patient', 'doctor', 'performedBy', 'procedureCatalog'])
                ->whereHas('encounter', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $procedures
        ]);
    }

    /**
     * Generate a unique procedure number
     */
    private function generateProcedureNumber($clinicId)
    {
        $prefix = 'PROC-' . date('Y') . '-';
        
        // Find the highest existing procedure number for this clinic this year
        $lastProcedure = Procedure::whereHas('encounter', function($query) use ($clinicId) {
                $query->where('clinic_id', $clinicId);
            })
            ->where('procedure_number', 'LIKE', $prefix . '%')
            ->orderBy('procedure_number', 'desc')
            ->first();
        
        if ($lastProcedure) {
            $lastNumber = intval(substr($lastProcedure->procedure_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $newNumber;
    }

    /**
     * Create a new procedure
     */
    public function store(ProcedureStoreRequest $request)
    {
        $validatedData = $request->validated();
        
        $user = Auth::user();
        
        // Check if user has permission to create procedures for this encounter
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
                'message' => 'Unauthorized to create procedures for this encounter'
            ], 403);
        }
        
        try {
            // Create procedure
            $procedure = Procedure::create([
                'encounter_id' => $request->encounter_id,
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'performed_by' => $request->performed_by ?? $user->id,
                'procedure_catalog_id' => $request->procedure_catalog_id,
                'procedure_number' => $this->generateProcedureNumber($encounter->clinic_id),
                'scheduled_time' => $request->scheduled_time,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'status' => $request->status,
                'indication' => $request->indication,
                'findings' => $request->findings,
                'complications' => $request->complications,
                'follow_up_instructions' => $request->follow_up_instructions,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Procedure created successfully',
                'data' => Procedure::with(['encounter', 'patient', 'doctor', 'performedBy', 'procedureCatalog'])->find($procedure->id)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create procedure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific procedure
     */
    public function show($id)
    {
        $procedure = Procedure::with(['encounter', 'patient', 'doctor', 'performedBy', 'procedureCatalog'])->find($id);
        
        if (!$procedure) {
            return response()->json([
                'success' => false,
                'message' => 'Procedure not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this procedure
        if (!$user->isAdmin() && $procedure->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this procedure'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $procedure
        ]);
    }

    /**
     * Update a procedure
     */
    public function update(ProcedureUpdateRequest $request, $id)
    {
        $procedure = Procedure::find($id);
        
        if (!$procedure) {
            return response()->json([
                'success' => false,
                'message' => 'Procedure not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to update this procedure
        if (!$user->isAdmin() && $procedure->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this procedure'
            ], 403);
        }
        
        $validatedData = $request->validated();
        
        try {
            $procedure->update($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Procedure updated successfully',
                'data' => Procedure::with(['encounter', 'patient', 'doctor', 'performedBy', 'procedureCatalog'])->find($procedure->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update procedure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get procedures for a specific encounter
     */
    public function getEncounterProcedures($encounterId)
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
        
        $procedures = $encounter->procedures()->with(['doctor', 'performedBy', 'procedureCatalog'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $procedures
        ]);
    }

    /**
     * Get procedures for a specific patient
     */
    public function getPatientProcedures($patientId)
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
        
        $procedures = $patient->procedures()->with(['encounter', 'doctor', 'performedBy', 'procedureCatalog'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $procedures
        ]);
    }

    /**
     * Get procedures by status
     */
    public function getProceduresByStatus($status)
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $procedures = Procedure::with(['encounter', 'patient', 'doctor', 'performedBy', 'procedureCatalog'])
                ->where('status', $status)
                ->get();
        } else {
            $procedures = Procedure::with(['encounter', 'patient', 'doctor', 'performedBy', 'procedureCatalog'])
                ->whereHas('encounter', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->where('status', $status)
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $procedures
        ]);
    }

    /**
     * Update procedure status
     */
    public function updateStatus(Request $request, $id)
    {
        $procedure = Procedure::find($id);
        
        if (!$procedure) {
            return response()->json([
                'success' => false,
                'message' => 'Procedure not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to update this procedure status
        if (!$user->isAdmin() && $procedure->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this procedure status'
            ], 403);
        }
        
        $request->validate([
            'status' => 'required|string|in:scheduled,in-progress,completed,cancelled',
        ]);
        
        try {
            $procedure->update([
                'status' => $request->status,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Procedure status updated successfully',
                'data' => Procedure::with(['encounter', 'patient', 'doctor', 'performedBy', 'procedureCatalog'])->find($procedure->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update procedure status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start a procedure
     */
    public function start($id)
    {
        $procedure = Procedure::find($id);
        
        if (!$procedure) {
            return response()->json([
                'success' => false,
                'message' => 'Procedure not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to start this procedure
        if (!$user->isAdmin() && $procedure->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to start this procedure'
            ], 403);
        }
        
        try {
            $procedure->update([
                'status' => 'in-progress',
                'start_time' => now(),
                'started_by' => $user->id,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Procedure started successfully',
                'data' => Procedure::with(['encounter', 'patient', 'doctor', 'performedBy', 'procedureCatalog'])->find($procedure->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start procedure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete a procedure
     */
    public function complete(Request $request, $id)
    {
        $procedure = Procedure::find($id);
        
        if (!$procedure) {
            return response()->json([
                'success' => false,
                'message' => 'Procedure not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to complete this procedure
        if (!$user->isAdmin() && $procedure->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to complete this procedure'
            ], 403);
        }
        
        $request->validate([
            'findings' => 'nullable|string|max:2000',
            'complications' => 'nullable|string|max:2000',
            'follow_up_instructions' => 'nullable|string|max:2000',
        ]);
        
        try {
            $procedure->update([
                'status' => 'completed',
                'end_time' => now(),
                'completed_by' => $user->id,
                'findings' => $request->findings ?? $procedure->findings,
                'complications' => $request->complications ?? $procedure->complications,
                'follow_up_instructions' => $request->follow_up_instructions ?? $procedure->follow_up_instructions,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Procedure completed successfully',
                'data' => Procedure::with(['encounter', 'patient', 'doctor', 'performedBy', 'procedureCatalog'])->find($procedure->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete procedure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a procedure
     */
    public function cancel($id)
    {
        $procedure = Procedure::find($id);
        
        if (!$procedure) {
            return response()->json([
                'success' => false,
                'message' => 'Procedure not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to cancel this procedure
        if (!$user->isAdmin() && $procedure->encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to cancel this procedure'
            ], 403);
        }
        
        try {
            $procedure->update([
                'status' => 'cancelled',
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Procedure cancelled successfully',
                'data' => Procedure::with(['encounter', 'patient', 'doctor', 'performedBy', 'procedureCatalog'])->find($procedure->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel procedure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search procedures
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        
        $query = Procedure::query();
        
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
        
        if ($request->procedure_catalog_id) {
            $query->where('procedure_catalog_id', $request->procedure_catalog_id);
        }
        
        if ($request->search_text) {
            $searchText = $request->search_text;
            $query->where(function($q) use ($searchText) {
                $q->where('procedure_number', 'like', '%' . $searchText . '%')
                  ->orWhere('indication', 'like', '%' . $searchText . '%')
                  ->orWhere('findings', 'like', '%' . $searchText . '%')
                  ->orWhereHas('procedureCatalog', function($q) use ($searchText) {
                      $q->where('procedure_name', 'like', '%' . $searchText . '%')
                        ->orWhere('procedure_code', 'like', '%' . $searchText . '%');
                  });
            });
        }
        
        $procedures = $query->with(['encounter', 'patient', 'doctor', 'performedBy', 'procedureCatalog'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $procedures
        ]);
    }

    /**
     * Get procedure catalog
     */
    public function getProcedureCatalog()
    {
        $procedureCatalog = ProcedureCatalog::all();
        
        return response()->json([
            'success' => true,
            'data' => $procedureCatalog
        ]);
    }

    /**
     * Get procedure catalog by category
     */
    public function getProcedureCatalogByCategory($category)
    {
        $procedureCatalog = ProcedureCatalog::where('category', $category)->get();
        
        return response()->json([
            'success' => true,
            'data' => $procedureCatalog
        ]);
    }

    /**
     * Get procedure catalog by specialty
     */
    public function getProcedureCatalogBySpecialty($specialty)
    {
        $procedureCatalog = ProcedureCatalog::where('specialty', $specialty)->get();
        
        return response()->json([
            'success' => true,
            'data' => $procedureCatalog
        ]);
    }

    /**
     * Get scheduled procedures for the current clinic
     */
    public function getScheduledProcedures()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $procedures = Procedure::with(['encounter', 'patient', 'doctor', 'performedBy', 'procedureCatalog'])
                ->where('status', 'scheduled')
                ->where('scheduled_time', '>', now())
                ->orderBy('scheduled_time', 'asc')
                ->get();
        } else {
            $procedures = Procedure::with(['encounter', 'patient', 'doctor', 'performedBy', 'procedureCatalog'])
                ->whereHas('encounter', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->where('status', 'scheduled')
                ->where('scheduled_time', '>', now())
                ->orderBy('scheduled_time', 'asc')
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $procedures
        ]);
    }

    /**
     * Get procedure statistics for the current clinic
     */
    public function getProcedureStatistics()
    {
        $user = Auth::user();
        
        $query = Procedure::query();
        
        if (!$user->isAdmin()) {
            $query->whereHas('encounter', function($query) use ($user) {
                $query->where('clinic_id', $user->clinic_id);
            });
        }
        
        $totalProcedures = $query->count();
        $completedProcedures = $query->where('status', 'completed')->count();
        $scheduledProcedures = $query->where('status', 'scheduled')->count();
        $cancelledProcedures = $query->where('status', 'cancelled')->count();
        
        $proceduresByCategory = $query->with('procedureCatalog')
            ->get()
            ->groupBy('procedureCatalog.category')
            ->map(function($group) {
                return $group->count();
            });
        
        $proceduresByMonth = $query->where('status', 'completed')
            ->whereYear('created_at', date('Y'))
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->map(function($item) {
                return $item->count;
            });
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_procedures' => $totalProcedures,
                'completed_procedures' => $completedProcedures,
                'scheduled_procedures' => $scheduledProcedures,
                'cancelled_procedures' => $cancelledProcedures,
                'completion_rate' => $totalProcedures > 0 ? ($completedProcedures / $totalProcedures) * 100 : 0,
                'procedures_by_category' => $proceduresByCategory,
                'procedures_by_month' => $proceduresByMonth,
            ]
        ]);
    }
}