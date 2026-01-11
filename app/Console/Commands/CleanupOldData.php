<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use Illuminate\Console\Command;

class CleanupOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:cleanup {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old data according to retention policies';

    /**
     * Data retention policies (in days)
     */
    protected $retentionPolicies = [
        'activity_logs' => 365, // 1 year
        'audit_logs' => 2555,   // 7 years (HIPAA requirement)
        'notifications' => 90,  // 3 months
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No data will be deleted');
        }

        $this->info('Starting data cleanup process...');

        // Clean up activity logs
        $this->cleanupActivityLogs($dryRun);

        // Clean up audit logs
        $this->cleanupAuditLogs($dryRun);

        // Clean up old notifications (if applicable)
        $this->cleanupNotifications($dryRun);

        $this->info('Data cleanup process completed.');
    }

    protected function cleanupActivityLogs($dryRun)
    {
        $retentionDays = $this->retentionPolicies['activity_logs'];
        $cutoffDate = now()->subDays($retentionDays);

        $count = ActivityLog::where('occurred_at', '<', $cutoffDate)->count();

        if ($dryRun) {
            $this->info("Would delete {$count} activity logs older than {$retentionDays} days");
        } else {
            ActivityLog::where('occurred_at', '<', $cutoffDate)->delete();
            $this->info("Deleted {$count} activity logs older than {$retentionDays} days");
        }
    }

    protected function cleanupAuditLogs($dryRun)
    {
        $retentionDays = $this->retentionPolicies['audit_logs'];
        $cutoffDate = now()->subDays($retentionDays);

        $count = AuditLog::where('created_at', '<', $cutoffDate)->count();

        if ($dryRun) {
            $this->info("Would delete {$count} audit logs older than {$retentionDays} days");
        } else {
            AuditLog::where('created_at', '<', $cutoffDate)->delete();
            $this->info("Deleted {$count} audit logs older than {$retentionDays} days");
        }
    }

    protected function cleanupNotifications($dryRun)
    {
        // This would clean up old notifications if we had a notifications table
        // For now, just show a message
        $this->info('Notification cleanup not implemented yet (no notifications table)');
    }
}
