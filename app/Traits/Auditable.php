<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logAudit('create', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $original = $model->getOriginal();
            $changes = $model->getChanges();
            $model->logAudit('update', $original, $changes);
        });

        static::deleted(function ($model) {
            $model->logAudit('delete', $model->getOriginal(), null);
        });
    }

    protected function logAudit($action, $oldValues, $newValues)
    {
        // Skip logging if no user is authenticated (for system operations)
        if (!auth()->check()) {
            return;
        }

        // Get sensitive fields that should be masked or excluded
        $sensitiveFields = $this->getSensitiveFields();

        // Mask sensitive data
        $oldValues = $this->maskSensitiveData($oldValues, $sensitiveFields);
        $newValues = $this->maskSensitiveData($newValues, $sensitiveFields);

        AuditLog::create([
            'user_id' => auth()->id(),
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'action' => $action,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'description' => $this->getAuditDescription($action),
        ]);
    }

    protected function getSensitiveFields()
    {
        // Define sensitive fields that should be masked in logs
        return [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'medical_history',
            'allergies',
            'emergency_contact_name',
            'emergency_contact_phone',
            'insurance_policy_number',
            'content', // for medical records
        ];
    }

    protected function maskSensitiveData($data, $sensitiveFields)
    {
        if (!$data) return $data;

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***MASKED***';
            }
        }

        return $data;
    }

    protected function getAuditDescription($action)
    {
        $modelName = class_basename($this);

        return match($action) {
            'create' => "Created new {$modelName}",
            'update' => "Updated {$modelName}",
            'delete' => "Deleted {$modelName}",
            'view' => "Viewed {$modelName}",
            default => ucfirst($action) . " {$modelName}"
        };
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'model_type', 'model_type', 'model_id');
    }
}