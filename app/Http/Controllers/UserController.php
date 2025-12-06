<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\InterDepartmentLoanRequest;
use App\Models\ProfileUpdateRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\BulkUserActionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Constructor - Restrict access to admin users only
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check()) {
                return redirect('/')->with('error', 'Please login to access this page.');
            }

            $user = auth()->user();

            // Load role relationship to prevent null reference errors
            if (!$user->relationLoaded('role')) {
                $user->load('role');
            }

            // Allow policy-gated profile viewing for non-admins (e.g., deans)
            $method = $request->route()->getActionMethod();
            if ($method === 'profile') {
                return $next($request);
            }

            // Admin-only for all other actions
            if (!$user->hasAdminPrivileges()) {
                abort(403, 'Unauthorized access. Admin privileges required.');
            }

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with(['role', 'department']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('department', function ($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Role filter
        if ($request->has('role_filter') && $request->role_filter) {
            $query->where('role_id', $request->role_filter);
        }

        // Department filter
        if ($request->has('department_filter') && $request->department_filter) {
            $query->where('department_id', $request->department_filter);
        }

        // Status filter (active/inactive based on email verification)
        if ($request->has('status_filter') && $request->status_filter !== '') {
            if ($request->status_filter == '1') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortField, ['name', 'email', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        $users = $query->paginate(15);
        $roles = Role::all();
        $departments = Department::all();

        // Statistics
        $stats = [
            'total_users' => User::count(),
            'super_admins' => User::whereHas('role', function ($q) {
                $q->where('name', 'super_admin');
            })->count(),
            'admins' => User::whereHas('role', function ($q) {
                $q->where('name', 'admin');
            })->count(),
            'deans' => User::whereHas('role', function ($q) {
                $q->where('name', 'dean');
            })->count(),
            'advisers' => User::whereHas('role', function ($q) {
                $q->where('name', 'adviser');
            })->count(),
            'students' => User::whereHas('role', function ($q) {
                $q->where('name', 'student');
            })->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'unverified_users' => User::whereNull('email_verified_at')->count(),
        ];

        // Profile update requests for admins
        $profileUpdateRequests = collect();
        if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
            $profileUpdateRequests = ProfileUpdateRequest::with(['user', 'reviewer'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
        }

        return view('users.index', compact('users', 'roles', 'departments', 'stats', 'profileUpdateRequests'));
    }

    /**
     * Search users via AJAX (for autocomplete functionality)
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'role' => 'nullable|string'
        ]);

        $query = User::with(['role', 'department'])
            ->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                  ->orWhere('email', 'like', "%{$request->q}%");
            });

        // Filter by role if specified
        if ($request->has('role') && $request->role) {
            $query->whereHas('role', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->limit(10)->get(['id', 'name', 'email']);

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $departments = Department::active()->get();
        return view('users.create', compact('roles', 'departments'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        
        $validated['password'] = Hash::make($validated['password']);
        
        // Set email verification status
        if (isset($validated['email_verified']) && $validated['email_verified']) {
            $validated['email_verified_at'] = now();
        }
        unset($validated['email_verified']);

        $user = User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['role', 'department']);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $departments = Department::active()->get();
        return view('users.edit', compact('user', 'roles', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        // Handle password update
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle email verification status
        if (isset($validated['email_verified'])) {
            if ($validated['email_verified'] && !$user->email_verified_at) {
                $validated['email_verified_at'] = now();
            } elseif (!$validated['email_verified'] && $user->email_verified_at) {
                $validated['email_verified_at'] = null;
            }
        }
        unset($validated['email_verified']);

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deletion of the current user
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Handle bulk actions on users.
     */
    public function bulkAction(BulkUserActionRequest $request)
    {
        $validated = $request->validated();
        $userIds = $validated['user_ids'];
        $action = $validated['action'];
        
        $affectedCount = 0;
        $message = '';

        switch ($action) {
            case 'delete':
                $affectedCount = User::whereIn('id', $userIds)->delete();
                $message = "Successfully deleted {$affectedCount} user(s).";
                break;

            case 'activate':
                $affectedCount = User::whereIn('id', $userIds)
                    ->whereNull('email_verified_at')
                    ->update(['email_verified_at' => now()]);
                $message = "Successfully activated {$affectedCount} user(s).";
                break;

            case 'deactivate':
                $affectedCount = User::whereIn('id', $userIds)
                    ->whereNotNull('email_verified_at')
                    ->update(['email_verified_at' => null]);
                $message = "Successfully deactivated {$affectedCount} user(s).";
                break;

            case 'assign_role':
                $roleId = $validated['role_id'];
                $affectedCount = User::whereIn('id', $userIds)
                    ->update(['role_id' => $roleId]);
                $role = Role::find($roleId);
                $message = "Successfully assigned {$role->name} role to {$affectedCount} user(s).";
                break;
        }

        return redirect()->route('users.index')
            ->with('success', $message);
    }

    /**
     * Export users data
     */
    public function export(Request $request)
    {
        $query = User::with(['role', 'department']);
        
        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('department', function ($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('role_filter') && $request->role_filter) {
            $query->where('role_id', $request->role_filter);
        }

        if ($request->has('department_filter') && $request->department_filter) {
            $query->where('department_id', $request->department_filter);
        }

        $users = $query->get();

        $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Email', 'Role', 'Department', 'Status', 'Created At']);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->name,
                    $user->email,
                    $user->role->display_name ?? 'N/A',
                    $user->department->name ?? 'N/A',
                    $user->email_verified_at ? 'Active' : 'Inactive',
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display user profile with related information
     */
    public function profile(User $user)
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $user);
        $user->load(['role', 'department']);
        
        // Get user's borrowed items
        $borrowedItems = $user->borrowedItems()
            ->with(['supply'])
            ->orderBy('borrowed_at', 'desc')
            ->paginate(10, ['*'], 'borrowed_page');
        
        // Get user's supply requests
        $supplyRequests = $user->supplyRequests()
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'supply_page');
        
        // Get user's loan requests
        $loanRequests = $user->loanRequests()
            ->with(['supply'])
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'loan_page');

        // Get user's inter-department loan requests (batch-aware)
        $interDeptLoanRequests = InterDepartmentLoanRequest::with([
                'issuedItem.supply',
                'requestItems.issuedItem.supply',
                'lendingDepartment',
                'borrowingDepartment',
                'requestedBy'
            ])
            ->where('requested_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'interdept_page');
        
        // Get user's issued items (if they are an admin/dean)
        $issuedItems = collect();
        if ($user->hasAdminPrivileges() || $user->isDean()) {
            $issuedItems = $user->issuedItems()
                ->with(['supply', 'supplyVariant'])
                ->orderBy('created_at', 'desc')
                ->paginate(10, ['*'], 'issued_page');
        }
        
        // Statistics
        $stats = [
            'total_borrowed' => $user->borrowedItems()->count(),
            'active_borrowed' => $user->borrowedItems()->whereNull('returned_at')->count(),
            'total_supply_requests' => $user->supplyRequests()->count(),
            'pending_supply_requests' => $user->supplyRequests()->where('status', 'pending')->count(),
            'total_loan_requests' => $user->loanRequests()->count(),
            'pending_loan_requests' => $user->loanRequests()->where('status', 'pending')->count(),
            'total_inter_dept_loan_requests' => InterDepartmentLoanRequest::where('requested_by', $user->id)->count(),
            'pending_inter_dept_loan_requests' => InterDepartmentLoanRequest::where('requested_by', $user->id)->where('status', 'pending')->count(),
        ];
        
        return view('users.profile', compact('user', 'borrowedItems', 'supplyRequests', 'loanRequests', 'interDeptLoanRequests', 'issuedItems', 'stats'));
    }
}
