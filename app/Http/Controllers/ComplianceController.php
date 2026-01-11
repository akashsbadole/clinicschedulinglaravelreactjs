<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF; // Assuming you have a PDF package like dompdf

class ComplianceController extends Controller
{
    /**
     * Generate audit trail report
     */
    public function auditTrail(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'user_id' => 'nullable|integer',
            'action' => 'nullable|string',
        ]);

        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->start_date) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('created_at', '<=', $request->end_date);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->action) {
            $query->where('action', $request->action);
        }

        $auditLogs = $query->paginate(50);

        return response()->json([
            'audit_trail' => $auditLogs,
            'summary' => [
                'total_logs' => $auditLogs->total(),
                'date_range' => [
                    'start' => $request->start_date,
                    'end' => $request->end_date,
                ],
            ],
        ]);
    }

    /**
     * Generate data access report
     */
    public function dataAccessReport(Request $request)
    {
        $request->validate([
            'patient_id' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $query = AuditLog::with('user')
            ->whereIn('model_type', [Patient::class, MedicalRecord::class])
            ->orderBy('created_at', 'desc');

        if ($request->patient_id) {
            $query->where('model_id', $request->patient_id);
        }

        if ($request->start_date) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('created_at', '<=', $request->end_date);
        }

        $accessLogs = $query->get();

        return response()->json([
            'data_access_report' => $accessLogs,
            'summary' => [
                'total_accesses' => $accessLogs->count(),
                'unique_users' => $accessLogs->pluck('user_id')->unique()->count(),
                'date_range' => [
                    'start' => $request->start_date,
                    'end' => $request->end_date,
                ],
            ],
        ]);
    }

    /**
     * Generate security incident report
     */
    public function securityIncidents(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $query = ActivityLog::whereIn('activity_type', [
            'rate_limit_exceeded',
            'ip_blocked',
            'ip_not_allowed',
            'failed_login',
        ])
        ->orderBy('occurred_at', 'desc');

        if ($request->start_date) {
            $query->where('occurred_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('occurred_at', '<=', $request->end_date);
        }

        $incidents = $query->get();

        $summary = [
            'total_incidents' => $incidents->count(),
            'by_type' => $incidents->groupBy('activity_type')->map->count(),
            'date_range' => [
                'start' => $request->start_date,
                'end' => $request->end_date,
            ],
        ];

        return response()->json([
            'security_incidents' => $incidents,
            'summary' => $summary,
        ]);
    }

    /**
     * Generate compliance summary report
     */
    public function complianceSummary(Request $request)
    {
        $request->validate([
            'period' => 'nullable|in:daily,weekly,monthly,yearly',
        ]);

        $period = $request->period ?? 'monthly';

        // Calculate date ranges based on period
        $now = now();
        switch ($period) {
            case 'daily':
                $startDate = $now->startOfDay();
                $endDate = $now->endOfDay();
                break;
            case 'weekly':
                $startDate = $now->startOfWeek();
                $endDate = $now->endOfWeek();
                break;
            case 'yearly':
                $startDate = $now->startOfYear();
                $endDate = $now->endOfYear();
                break;
            default: // monthly
                $startDate = $now->startOfMonth();
                $endDate = $now->endOfMonth();
        }

        $summary = [
            'period' => $period,
            'date_range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'audit_logs' => [
                'total' => AuditLog::whereBetween('created_at', [$startDate, $endDate])->count(),
                'by_action' => AuditLog::whereBetween('created_at', [$startDate, $endDate])
                    ->select('action', DB::raw('count(*) as count'))
                    ->groupBy('action')
                    ->pluck('count', 'action'),
            ],
            'activity_logs' => [
                'total' => ActivityLog::whereBetween('occurred_at', [$startDate, $endDate])->count(),
                'by_type' => ActivityLog::whereBetween('occurred_at', [$startDate, $endDate])
                    ->select('activity_type', DB::raw('count(*) as count'))
                    ->groupBy('activity_type')
                    ->pluck('count', 'activity_type'),
            ],
            'security_incidents' => ActivityLog::whereIn('activity_type', [
                'rate_limit_exceeded',
                'ip_blocked',
                'ip_not_allowed',
            ])
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->count(),
            'data_access' => [
                'patient_records' => AuditLog::where('model_type', Patient::class)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'medical_records' => AuditLog::where('model_type', MedicalRecord::class)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
            ],
        ];

        return response()->json([
            'compliance_summary' => $summary,
        ]);
    }

    /**
     * Export compliance report as PDF
     */
    public function exportReport(Request $request)
    {
        $request->validate([
            'type' => 'required|in:audit_trail,data_access,security_incidents,compliance_summary',
            'format' => 'required|in:json,pdf',
        ]);

        $data = match($request->type) {
            'audit_trail' => $this->auditTrail($request),
            'data_access' => $this->dataAccessReport($request),
            'security_incidents' => $this->securityIncidents($request),
            'compliance_summary' => $this->complianceSummary($request),
        };

        if ($request->format === 'pdf') {
            // Generate PDF (requires PDF package)
            // $pdf = PDF::loadView('compliance.report', $data->getData());
            // return $pdf->download('compliance-report.pdf');

            return response()->json(['message' => 'PDF export not implemented yet']);
        }

        return $data;
    }
}
