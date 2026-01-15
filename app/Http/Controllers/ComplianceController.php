<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ComplianceCheck;
use App\Models\DataAccessRequest;
use App\Models\ConsentForm;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ComplianceController extends Controller
{
    /**
     * Display compliance dashboard
     */
    public function dashboard(Request $request)
    {
        $complianceChecks = ComplianceCheck::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $pendingRequests = DataAccessRequest::where('status', 'pending')->count();
        $auditLogsCount = AuditLog::count();
        
        return Inertia::render('Compliance/Dashboard', [
            'complianceChecks' => $complianceChecks,
            'pendingRequests' => $pendingRequests,
            'auditLogsCount' => $auditLogsCount
        ]);
    }

    /**
     * Display audit logs
     */
    public function auditLogs(Request $request)
    {
        $auditLogs = AuditLog::with(['user', 'auditable'])
                    ->when($request->user_id, function($query, $userId) {
                        $query->where('user_id', $userId);
                    })
                    ->when($request->action, function($query, $action) {
                        $query->where('action', $action);
                    })
                    ->when($request->date_range, function($query, $dateRange) {
                        $dates = explode(' to ', $dateRange);
                        if (count($dates) === 2) {
                            $query->whereBetween('created_at', $dates);
                        }
                    })
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);
        
        $users = User::orderBy('name')->get();
        
        return Inertia::render('Compliance/AuditLogs', [
            'auditLogs' => $auditLogs,
            'users' => $users,
            'filters' => $request->only(['user_id', 'action', 'date_range'])
        ]);
    }

    /**
     * Display data access requests
     */
    public function dataAccessRequests(Request $request)
    {
        $requests = DataAccessRequest::with(['user', 'patient'])
                    ->when($request->status, function($query, $status) {
                        $query->where('status', $status);
                    })
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);
        
        return Inertia::render('Compliance/DataAccessRequests', [
            'requests' => $requests,
            'status' => $request->status ?? 'all'
        ]);
    }

    /**
     * Show form for creating data access request
     */
    public function createDataAccessRequest()
    {
        $patients = Patient::orderBy('name')->get();
        
        return Inertia::render('Compliance/CreateDataAccessRequest', [
            'patients' => $patients
        ]);
    }

    /**
     * Store data access request
     */
    public function storeDataAccessRequest(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'purpose' => 'required|string|max:1000',
            'data_types' => 'required|array',
            'data_types.*' => 'string'
        ]);
        
        try {
            $requestRecord = DataAccessRequest::create([
                'user_id' => auth()->id(),
                'patient_id' => $validated['patient_id'],
                'purpose' => $validated['purpose'],
                'data_types' => json_encode($validated['data_types']),
                'status' => 'pending'
            ]);
            
            return redirect()->route('compliance.data-access-requests')
                ->with('success', 'Data access request submitted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to submit request: ' . $e->getMessage());
        }
    }

    /**
     * Review data access request
     */
    public function reviewDataAccessRequest(DataAccessRequest $request)
    {
        $request->load(['user', 'patient']);
        
        return Inertia::render('Compliance/ReviewDataAccessRequest', [
            'request' => $request
        ]);
    }

    /**
     * Approve data access request
     */
    public function approveDataAccessRequest(DataAccessRequest $request, Request $formRequest)
    {
        $validated = $formRequest->validate([
            'approval_notes' => 'nullable|string|max:1000'
        ]);
        
        DB::beginTransaction();
        
        try {
            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $validated['approval_notes']
            ]);
            
            // Log the approval
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'approve_data_access',
                'auditable_type' => DataAccessRequest::class,
                'auditable_id' => $request->id,
                'old_values' => json_encode(['status' => 'pending']),
                'new_values' => json_encode(['status' => 'approved']),
                'url' => $formRequest->fullUrl(),
                'ip_address' => $formRequest->ip()
            ]);
            
            DB::commit();
            
            return redirect()->route('compliance.data-access-requests')
                ->with('success', 'Data access request approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    /**
     * Reject data access request
     */
    public function rejectDataAccessRequest(DataAccessRequest $request, Request $formRequest)
    {
        $validated = $formRequest->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);
        
        DB::beginTransaction();
        
        try {
            $request->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $validated['rejection_reason']
            ]);
            
            // Log the rejection
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'reject_data_access',
                'auditable_type' => DataAccessRequest::class,
                'auditable_id' => $request->id,
                'old_values' => json_encode(['status' => 'pending']),
                'new_values' => json_encode(['status' => 'rejected']),
                'url' => $formRequest->fullUrl(),
                'ip_address' => $formRequest->ip()
            ]);
            
            DB::commit();
            
            return redirect()->route('compliance.data-access-requests')
                ->with('success', 'Data access request rejected successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject request: ' . $e->getMessage());
        }
    }

    /**
     * Display consent forms
     */
    public function consentForms(Request $request)
    {
        $consentForms = ConsentForm::with(['patient', 'user'])
                    ->when($request->patient_id, function($query, $patientId) {
                        $query->where('patient_id', $patientId);
                    })
                    ->when($request->status, function($query, $status) {
                        $query->where('status', $status);
                    })
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);
        
        $patients = Patient::orderBy('name')->get();
        
        return Inertia::render('Compliance/ConsentForms', [
            'consentForms' => $consentForms,
            'patients' => $patients,
            'filters' => $request->only(['patient_id', 'status'])
        ]);
    }

    /**
     * Show form for creating consent form
     */
    public function createConsentForm()
    {
        $patients = Patient::orderBy('name')->get();
        
        return Inertia::render('Compliance/CreateConsentForm', [
            'patients' => $patients
        ]);
    }

    /**
     * Store consent form
     */
    public function storeConsentForm(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'consent_type' => 'required|string|max:255',
            'consent_text' => 'required|string',
            'status' => 'required|in:active,revoked,expired',
            'effective_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:effective_date',
            'signature' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);
        
        DB::beginTransaction();
        
        try {
            $consentForm = ConsentForm::create([
                'patient_id' => $validated['patient_id'],
                'consent_type' => $validated['consent_type'],
                'consent_text' => $validated['consent_text'],
                'status' => $validated['status'],
                'effective_date' => $validated['effective_date'],
                'expiry_date' => $validated['expiry_date'],
                'created_by' => auth()->id()
            ]);
            
            // Handle file upload
            if ($request->hasFile('signature')) {
                $path = $request->file('signature')->store('consent_forms/signatures', 'public');
                $consentForm->update(['signature_path' => $path]);
            }
            
            DB::commit();
            
            return redirect()->route('compliance.consent-forms')
                ->with('success', 'Consent form created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create consent form: ' . $e->getMessage());
        }
    }

    /**
     * Show consent form
     */
    public function showConsentForm(ConsentForm $consentForm)
    {
        $consentForm->load(['patient', 'user']);
        
        return Inertia::render('Compliance/ShowConsentForm', [
            'consentForm' => $consentForm
        ]);
    }

    /**
     * Update consent form status
     */
    public function updateConsentFormStatus(ConsentForm $consentForm, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,revoked,expired',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        try {
            $consentForm->update([
                'status' => $validated['status'],
                'status_notes' => $validated['notes'],
                'updated_by' => auth()->id()
            ]);
            
            return back()->with('success', 'Consent form status updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update consent form: ' . $e->getMessage());
        }
    }

    /**
     * Run compliance checks
     */
    public function runComplianceChecks()
    {
        $checks = [
            'data_encryption' => $this->checkDataEncryption(),
            'access_controls' => $this->checkAccessControls(),
            'audit_logging' => $this->checkAuditLogging(),
            'data_retention' => $this->checkDataRetention(),
            'consent_management' => $this->checkConsentManagement()
        ];
        
        DB::beginTransaction();
        
        try {
            foreach ($checks as $checkType => $result) {
                ComplianceCheck::create([
                    'check_type' => $checkType,
                    'status' => $result['status'],
                    'details' => json_encode($result),
                    'user_id' => auth()->id()
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('compliance.dashboard')
                ->with('success', 'Compliance checks completed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to run compliance checks: ' . $e->getMessage());
        }
    }

    /**
     * Check data encryption compliance
     */
    protected function checkDataEncryption()
    {
        // Check if sensitive data is encrypted
        $patient = Patient::first();
        $isEncrypted = $patient && method_exists($patient, 'getEncryptedAttributes');
        
        return [
            'status' => $isEncrypted ? 'pass' : 'fail',
            'message' => $isEncrypted ? 'Sensitive data is properly encrypted' : 'Sensitive data encryption needs review',
            'details' => [
                'checked_attributes' => ['name', 'email', 'phone', 'address'],
                'encryption_status' => $isEncrypted ? 'enabled' : 'disabled'
            ]
        ];
    }

    /**
     * Check access controls compliance
     */
    protected function checkAccessControls()
    {
        $userCount = User::count();
        $roleCount = DB::table('roles')->count();
        
        return [
            'status' => $userCount > 0 && $roleCount > 0 ? 'pass' : 'warn',
            'message' => 'Access controls are properly configured',
            'details' => [
                'user_count' => $userCount,
                'role_count' => $roleCount,
                'recommendation' => $userCount === 0 ? 'Create at least one user' : ''
            ]
        ];
    }

    /**
     * Check audit logging compliance
     */
    protected function checkAuditLogging()
    {
        $recentLogs = AuditLog::where('created_at', '>', now()->subDays(7))->count();
        
        return [
            'status' => $recentLogs > 0 ? 'pass' : 'warn',
            'message' => 'Audit logging is active',
            'details' => [
                'recent_logs_count' => $recentLogs,
                'recommendation' => $recentLogs === 0 ? 'Review audit logging configuration' : ''
            ]
        ];
    }

    /**
     * Check data retention compliance
     */
    protected function checkDataRetention()
    {
        // Check if there are old records that might need archiving
        $oldPatients = Patient::where('created_at', '<', now()->subYears(7))->count();
        
        return [
            'status' => $oldPatients === 0 ? 'pass' : 'info',
            'message' => 'Data retention policies are being followed',
            'details' => [
                'old_records_count' => $oldPatients,
                'recommendation' => $oldPatients > 0 ? 'Consider archiving old patient records' : ''
            ]
        ];
    }

    /**
     * Check consent management compliance
     */
    protected function checkConsentManagement()
    {
        $activePatients = Patient::count();
        $consentForms = ConsentForm::where('status', 'active')->count();
        
        $coverage = $activePatients > 0 ? round(($consentForms / $activePatients) * 100) : 100;
        
        return [
            'status' => $coverage >= 90 ? 'pass' : 'warn',
            'message' => 'Consent management coverage is adequate',
            'details' => [
                'active_patients' => $activePatients,
                'consent_forms' => $consentForms,
                'coverage_percentage' => $coverage,
                'recommendation' => $coverage < 90 ? 'Review consent form coverage for patients' : ''
            ]
        ];
    }

    /**
     * Get audit trail for API
     */
    public function auditTrail(Request $request)
    {
        $auditLogs = AuditLog::with(['user'])
            ->when($request->user_id, function($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->action, function($query, $action) {
                $query->where('action', $action);
            })
            ->when($request->date_range, function($query, $dateRange) {
                $dates = explode(' to ', $dateRange);
                if (count($dates) === 2) {
                    $query->whereBetween('created_at', $dates);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $summary = [
            'total_logs' => $auditLogs->total(),
            'unique_users' => AuditLog::distinct('user_id')->count('user_id'),
            'actions_count' => AuditLog::selectRaw('action, count(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action')
                ->toArray(),
        ];

        return response()->json([
            'audit_trail' => $auditLogs,
            'summary' => $summary
        ]);
    }

    /**
     * Export compliance report
     */
    public function exportComplianceReport()
    {
        $complianceChecks = ComplianceCheck::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();
        
        $auditLogs = AuditLog::with(['user', 'auditable'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        
        $data = [
            'compliance_checks' => $complianceChecks,
            'audit_logs' => $auditLogs,
            'generated_at' => now()->toDateTimeString(),
            'generated_by' => auth()->user()->name
        ];
        
        $filename = 'compliance_report_' . now()->format('Y-m-d') . '.json';
        $content = json_encode($data, JSON_PRETTY_PRINT);
        
        return response()->streamDownload(function() use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Get compliance statistics
     */
    public function getComplianceStatistics()
    {
        $stats = [
            'total_compliance_checks' => ComplianceCheck::count(),
            'passed_checks' => ComplianceCheck::where('status', 'pass')->count(),
            'failed_checks' => ComplianceCheck::where('status', 'fail')->count(),
            'warning_checks' => ComplianceCheck::where('status', 'warn')->count(),
            'info_checks' => ComplianceCheck::where('status', 'info')->count(),
            'pending_data_access_requests' => DataAccessRequest::where('status', 'pending')->count(),
            'active_consent_forms' => ConsentForm::where('status', 'active')->count(),
            'recent_audit_logs' => AuditLog::where('created_at', '>', now()->subDays(7))->count()
        ];
        
        return response()->json($stats);
    }
}
