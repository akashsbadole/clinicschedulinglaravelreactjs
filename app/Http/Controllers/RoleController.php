<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index(Request $request)
    {
        $roles = Role::with(['permissions', 'users'])
                    ->when($request->search, function($query, $search) {
                        $query->where('name', 'like', '%' . $search . '%')
                              ->orWhere('description', 'like', '%' . $search . '%');
                    })
                    ->orderBy('name')
                    ->paginate(10);
        
        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'search' => $request->search ?? ''
        ]);
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        
        return Inertia::render('Roles/Create', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a newly created role in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);
        
        DB::beginTransaction();
        
        try {
            $role = Role::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'created_by' => auth()->id()
            ]);
            
            // Attach permissions to role
            $role->permissions()->attach($validated['permissions']);
            
            DB::commit();
            
            return redirect()->route('roles.index')
                ->with('success', 'Role created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'users']);
        
        return Inertia::render('Roles/Show', [
            'role' => $role
        ]);
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return Inertia::render('Roles/Edit', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions
        ]);
    }

    /**
     * Update the specified role in storage
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($role->id)
            ],
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);
        
        DB::beginTransaction();
        
        try {
            $role->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'updated_by' => auth()->id()
            ]);
            
            // Sync permissions
            $role->permissions()->sync($validated['permissions']);
            
            DB::commit();
            
            return redirect()->route('roles.index')
                ->with('success', 'Role updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update role: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified role from storage
     */
    public function destroy(Role $role)
    {
        // Check if role is used by any users
        if ($role->users()->exists()) {
            return back()->with('error', 'Cannot delete role that is assigned to users');
        }
        
        DB::beginTransaction();
        
        try {
            $role->permissions()->detach();
            $role->delete();
            
            DB::commit();
            
            return redirect()->route('roles.index')
                ->with('success', 'Role deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete role: ' . $e->getMessage());
        }
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);
        
        try {
            $user->roles()->syncWithoutDetaching([$validated['role_id']]);
            
            return back()->with('success', 'Role assigned successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to assign role: ' . $e->getMessage());
        }
    }

    /**
     * Remove role from user
     */
    public function removeRole(User $user, Role $role)
    {
        try {
            $user->roles()->detach($role->id);
            
            return back()->with('success', 'Role removed successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to remove role: ' . $e->getMessage());
        }
    }

    /**
     * Get permissions for a role
     */
    public function getPermissions(Role $role)
    {
        $role->load('permissions');
        
        return response()->json([
            'role' => $role,
            'permissions' => $role->permissions
        ]);
    }

    /**
     * Check if user has permission
     */
    public function checkPermission(Request $request)
    {
        $validated = $request->validate([
            'permission' => 'required|string'
        ]);
        
        $hasPermission = $request->user()->hasPermission($validated['permission']);
        
        return response()->json([
            'hasPermission' => $hasPermission
        ]);
    }

    /**
     * Get all permissions
     */
    public function getAllPermissions()
    {
        $permissions = Permission::orderBy('name')->get();
        
        return response()->json([
            'permissions' => $permissions
        ]);
    }

    /**
     * Get user roles
     */
    public function getUserRoles(User $user)
    {
        $user->load('roles.permissions');
        
        return response()->json([
            'user' => $user,
            'roles' => $user->roles
        ]);
    }
}