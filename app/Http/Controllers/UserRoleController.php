<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check()) {
                return redirect('/');
            }
            
            $user = Auth::user();
            
            // Load role relationship to prevent null reference errors
            if (!$user->relationLoaded('role')) {
                $user->load('role');
            }
            
            if (!$user->hasAdminPrivileges()) {
                abort(403, 'Unauthorized access.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of users with their roles.
     */
    public function index(Request $request): View
    {
        $query = User::with(['role', 'department']);

        // Search functionality
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role_id') && $request->role_id !== '') {
            $query->where('role_id', $request->role_id);
        }

        $users = $query->orderBy('name')->paginate(15);
        $roles = Role::all();
        $departments = Department::active()->get();

        return view('admin.user-roles.index', compact('users', 'roles', 'departments'));
    }

    /**
     * Update the user's role.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        // Prevent users from changing their own role
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot change your own role.');
        }

        $newRole = Role::find($request->role_id);
        $departmentId = $request->department_id ?? $user->department_id;

        // Validate Dean uniqueness constraint
        if (!$user->validateDeanUniqueness($departmentId, $request->role_id)) {
            $department = Department::find($departmentId);
            $existingDean = User::getDeanOfDepartment($departmentId);
            
            return redirect()->back()->with('error', 
                "Cannot assign Dean role. {$existingDean->name} is already the Dean of {$department->department_name}. " .
                "Only one Dean can be assigned per department."
            );
        }

        $oldRole = $user->role->name ?? 'No Role';

        // Update user role and department if provided
        $updateData = ['role_id' => $request->role_id];
        if ($request->has('department_id')) {
            $updateData['department_id'] = $request->department_id;
        }

        $user->update($updateData);

        return redirect()->back()->with('success', "User role updated from '{$oldRole}' to '{$newRole->name}' successfully.");
    }
}
