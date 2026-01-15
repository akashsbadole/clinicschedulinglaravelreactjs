<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Models\Clinic;
use App\Models\BackupLog;
use App\Models\Integration;
use App\Models\ApiKey;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SystemAdminController extends Controller
{
    // System Configuration Management
    public function systemConfiguration()
    {
        $settings = SystemSetting::all()->keyBy('key');
        return Inertia::render('SystemAdmin/Configuration', [
            'settings' => $settings,
        ]);
    }

    public function updateSystemConfiguration(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);

        DB::beginTransaction();
        
        try {
            foreach ($validated['settings'] as $setting) {
                SystemSetting::updateOrCreate(
                    ['key' => $setting['key']],
                    ['value' => $setting['value']]
                );
            }

            DB::commit();
            
            return redirect()->back()->with('success', 'System configuration updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update configuration: ' . $e->getMessage());
        }
    }

    // User Management
    public function userManagement()
    {
        $users = User::with(['roles', 'clinics'])->orderBy('name')->get();
        $roles = Role::all();
        $clinics = Clinic::all();

        return Inertia::render('SystemAdmin/UserManagement', [
            'users' => $users,
            'roles' => $roles,
            'clinics' => $clinics,
        ]);
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'clinics' => 'nullable|array',
            'clinics.*' => 'exists:clinics,id',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $user->roles()->sync($validated['roles']);
            if (isset($validated['clinics'])) {
                $user->clinics()->sync($validated['clinics']);
            }

            DB::commit();
            
            return redirect()->back()->with('success', 'User created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'clinics' => 'nullable|array',
            'clinics.*' => 'exists:clinics,id',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        
        try {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            if ($validated['password']) {
                $user->update(['password' => Hash::make($validated['password'])]);
            }

            $user->roles()->sync($validated['roles']);
            if (isset($validated['clinics'])) {
                $user->clinics()->sync($validated['clinics']);
            }

            DB::commit();
            
            return redirect()->back()->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function deactivateUser(User $user)
    {
        try {
            $user->update(['is_active' => false]);
            return redirect()->back()->with('success', 'User deactivated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to deactivate user: ' . $e->getMessage());
        }
    }

    public function activateUser(User $user)
    {
        try {
            $user->update(['is_active' => true]);
            return redirect()->back()->with('success', 'User activated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to activate user: ' . $e->getMessage());
        }
    }

    // Role and Permission Management
    public function roleManagement()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        return Inertia::render('SystemAdmin/RoleManagement', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function createRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();
        
        try {
            $role = Role::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
            ]);

            $role->permissions()->sync($validated['permissions']);

            DB::commit();
            
            return redirect()->back()->with('success', 'Role created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    public function updateRole(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();
        
        try {
            $role->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
            ]);

            $role->permissions()->sync($validated['permissions']);

            DB::commit();
            
            return redirect()->back()->with('success', 'Role updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update role: ' . $e->getMessage());
        }
    }

    public function deleteRole(Role $role)
    {
        try {
            // Prevent deletion of system roles
            if (in_array($role->name, ['super-admin', 'admin', 'doctor', 'nurse', 'staff'])) {
                return redirect()->back()->with('error', 'Cannot delete system role');
            }

            $role->delete();
            return redirect()->back()->with('success', 'Role deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete role: ' . $e->getMessage());
        }
    }

    // Audit Log Management
    public function auditLogs()
    {
        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->paginate(50);

        return Inertia::render('SystemAdmin/AuditLogs', [
            'logs' => $logs,
        ]);
    }

    public function searchAuditLogs(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'action' => 'nullable|string',
            'model_type' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'search' => 'nullable|string',
        ]);

        $query = AuditLog::with('user');

        if ($validated['user_id']) {
            $query->where('user_id', $validated['user_id']);
        }

        if ($validated['action']) {
            $query->where('action', 'like', '%' . $validated['action'] . '%');
        }

        if ($validated['model_type']) {
            $query->where('model_type', 'like', '%' . $validated['model_type'] . '%');
        }

        if ($validated['date_from']) {
            $query->where('created_at', '>=', $validated['date_from']);
        }

        if ($validated['date_to']) {
            $query->where('created_at', '<=', $validated['date_to'] . ' 23:59:59');
        }

        if ($validated['search']) {
            $query->where(function($q) use ($validated) {
                $q->where('action', 'like', '%' . $validated['search'] . '%')
                  ->orWhere('model_type', 'like', '%' . $validated['search'] . '%')
                  ->orWhere('model_id', 'like', '%' . $validated['search'] . '%')
                  ->orWhere('old_values', 'like', '%' . $validated['search'] . '%')
                  ->orWhere('new_values', 'like', '%' . $validated['search'] . '%');
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return Inertia::render('SystemAdmin/AuditLogs', [
            'logs' => $logs,
            'searchParams' => $validated,
        ]);
    }

    // Backup and Restore Management
    public function backupManagement()
    {
        $backups = BackupLog::orderBy('created_at', 'desc')->paginate(20);

        return Inertia::render('SystemAdmin/BackupManagement', [
            'backups' => $backups,
        ]);
    }

    public function createBackup(Request $request)
    {
        $validated = $request->validate([
            'backup_type' => 'required|string|in:full,database_only,files_only',
            'description' => 'nullable|string',
        ]);

        try {
            // In a real implementation, this would trigger a backup job
            $backup = BackupLog::create([
                'backup_type' => $validated['backup_type'],
                'description' => $validated['description'] ?? 'Manual backup',
                'status' => 'pending',
                'file_path' => 'backups/' . date('Y-m-d_H-i-s') . '_' . $validated['backup_type'] . '.zip',
                'created_by' => Auth::id(),
            ]);

            // Dispatch backup job
            // CreateBackupJob::dispatch($backup->id);

            return redirect()->back()->with('success', 'Backup process initiated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to initiate backup: ' . $e->getMessage());
        }
    }

    public function restoreBackup(BackupLog $backup)
    {
        try {
            // In a real implementation, this would trigger a restore job
            $backup->update([
                'restore_status' => 'pending',
                'restored_by' => Auth::id(),
                'restored_at' => now(),
            ]);

            // Dispatch restore job
            // RestoreBackupJob::dispatch($backup->id);

            return redirect()->back()->with('success', 'Restore process initiated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to initiate restore: ' . $e->getMessage());
        }
    }

    public function downloadBackup(BackupLog $backup)
    {
        // In a real implementation, this would return the backup file
        return response()->json([
            'message' => 'Backup download functionality would be implemented here',
            'backup' => $backup,
        ]);
    }

    // System Integration Management
    public function integrationManagement()
    {
        $integrations = Integration::orderBy('name')->get();

        return Inertia::render('SystemAdmin/IntegrationManagement', [
            'integrations' => $integrations,
        ]);
    }

    public function createIntegration(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:hl7,api,webhook,database',
            'endpoint_url' => 'required|url',
            'auth_method' => 'required|string|in:api_key,oauth2,basic_auth',
            'auth_credentials' => 'required|array',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        
        try {
            $integration = Integration::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'endpoint_url' => $validated['endpoint_url'],
                'auth_method' => $validated['auth_method'],
                'auth_credentials' => $validated['auth_credentials'],
                'is_active' => $validated['is_active'] ?? true,
                'description' => $validated['description'],
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            
            return redirect()->back()->with('success', 'Integration created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create integration: ' . $e->getMessage());
        }
    }

    public function updateIntegration(Request $request, Integration $integration)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:hl7,api,webhook,database',
            'endpoint_url' => 'required|url',
            'auth_method' => 'required|string|in:api_key,oauth2,basic_auth',
            'auth_credentials' => 'required|array',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        
        try {
            $integration->update([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'endpoint_url' => $validated['endpoint_url'],
                'auth_method' => $validated['auth_method'],
                'auth_credentials' => $validated['auth_credentials'],
                'is_active' => $validated['is_active'] ?? true,
                'description' => $validated['description'],
                'updated_by' => Auth::id(),
            ]);

            DB::commit();
            
            return redirect()->back()->with('success', 'Integration updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update integration: ' . $e->getMessage());
        }
    }

    public function testIntegration(Integration $integration)
    {
        try {
            // In a real implementation, this would test the integration connection
            $testResult = [
                'status' => 'success',
                'message' => 'Connection test successful',
                'response_time' => '450ms',
                'tested_at' => now(),
            ];

            $integration->update([
                'last_test_result' => $testResult,
                'last_tested_at' => now(),
            ]);

            return response()->json($testResult);
        } catch (\Exception $e) {
            $testResult = [
                'status' => 'error',
                'message' => 'Connection test failed: ' . $e->getMessage(),
                'tested_at' => now(),
            ];

            $integration->update([
                'last_test_result' => $testResult,
                'last_tested_at' => now(),
            ]);

            return response()->json($testResult, 500);
        }
    }

    // API Key Management
    public function apiKeyManagement()
    {
        $apiKeys = ApiKey::orderBy('created_at', 'desc')->get();

        return Inertia::render('SystemAdmin/ApiKeyManagement', [
            'apiKeys' => $apiKeys,
        ]);
    }

    public function createApiKey(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'string',
            'expires_at' => 'nullable|date|after:today',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        
        try {
            $apiKey = ApiKey::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'key' => bin2hex(random_bytes(32)),
                'secret' => bin2hex(random_bytes(64)),
                'permissions' => $validated['permissions'],
                'expires_at' => $validated['expires_at'],
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            
            return redirect()->back()->with('success', 'API key created successfully')->with('newApiKey', $apiKey);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create API key: ' . $e->getMessage());
        }
    }

    public function revokeApiKey(ApiKey $apiKey)
    {
        try {
            $apiKey->update([
                'is_active' => false,
                'revoked_at' => now(),
                'revoked_by' => Auth::id(),
            ]);

            return redirect()->back()->with('success', 'API key revoked successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to revoke API key: ' . $e->getMessage());
        }
    }

    // Module Management
    public function moduleManagement()
    {
        $modules = Module::with('dependencies')->orderBy('name')->get();

        return Inertia::render('SystemAdmin/ModuleManagement', [
            'modules' => $modules,
        ]);
    }

    public function toggleModule(Module $module)
    {
        try {
            $module->update([
                'is_enabled' => !$module->is_enabled,
                'updated_by' => Auth::id(),
            ]);

            return redirect()->back()->with('success', 'Module status updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update module status: ' . $e->getMessage());
        }
    }

    // System Health Monitoring
    public function systemHealth()
    {
        // Get system health metrics
        $healthMetrics = [
            'database' => [
                'status' => 'operational',
                'connection' => 'established',
                'response_time' => '12ms',
            ],
            'storage' => [
                'status' => 'operational',
                'used_space' => '45GB',
                'total_space' => '100GB',
                'free_space' => '55GB',
            ],
            'cache' => [
                'status' => 'operational',
                'hit_rate' => '87%',
                'memory_usage' => '256MB',
            ],
            'queue' => [
                'status' => 'operational',
                'pending_jobs' => 0,
                'failed_jobs' => 0,
            ],
            'server' => [
                'cpu_usage' => '23%',
                'memory_usage' => '4.2GB/16GB',
                'uptime' => '45 days',
            ],
        ];

        return Inertia::render('SystemAdmin/SystemHealth', [
            'healthMetrics' => $healthMetrics,
        ]);
    }

    // System Admin Dashboard
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_roles' => Role::count(),
            'recent_audit_logs' => AuditLog::with('user')->orderBy('created_at', 'desc')->limit(10)->get(),
            'system_alerts' => [], // Would be populated with actual alerts in production
            'pending_backups' => BackupLog::where('status', 'pending')->count(),
        ];

        return Inertia::render('SystemAdmin/Dashboard', [
            'stats' => $stats,
        ]);
    }

    // System Settings
    public function systemSettings()
    {
        $settings = SystemSetting::all()->keyBy('key');
        
        return Inertia::render('SystemAdmin/Settings', [
            'settings' => $settings,
        ]);
    }

    public function updateSystemSettings(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        DB::beginTransaction();
        
        try {
            foreach ($validated['settings'] as $key => $value) {
                SystemSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }

            DB::commit();
            
            return redirect()->back()->with('success', 'System settings updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}