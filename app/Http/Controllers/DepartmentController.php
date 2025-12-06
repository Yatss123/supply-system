<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (!$user || !method_exists($user, 'hasAdminPrivileges') || !$user->hasAdminPrivileges()) {
                abort(403);
            }
            return $next($request);
        })->only(['create', 'store', 'edit', 'update', 'destroy', 'toggleStatus']);
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $departments = Department::with('dean')->when($search, function ($query, $search) {
            return $query->where('department_name', 'LIKE', "%{$search}%")
                         ->orWhereHas('dean', function ($q) use ($search) {
                             $q->where('name', 'LIKE', "%{$search}%");
                         });
        })->paginate(10);

        return view('departments.index', compact('departments', 'search'));
    }

    public function show(Department $department)
    {
        // Load the department with all related data
        $department->load([
            'dean',
            'users' => function ($query) {
                $query->with('role');
            }
        ]);

        // Get borrowed items for this department
        $borrowedItems = \App\Models\BorrowedItem::with(['supply', 'loanRequest'])
            ->where('department_id', $department->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'borrowed_page');

        // Get supply requests for this department
        $supplyRequests = \App\Models\SupplyRequest::with('supply')
            ->where('department_id', $department->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'supply_page');

        // Get issued items for this department
        $issuedItems = \App\Models\IssuedItem::with(['supply', 'supplyVariant'])
            ->where('department_id', $department->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'issued_page');

        // Get loan requests for this department
        $loanRequests = \App\Models\LoanRequest::with(['supply', 'requestedBy', 'approvedBy'])
            ->where('department_id', $department->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'loan_page');

        // Get users by role for this department
        $deans = $department->users->where('role.name', 'dean');
        $advisers = $department->users->where('role.name', 'adviser');
        $students = $department->users->where('role.name', 'student');

        return view('departments.show', compact(
            'department', 
            'borrowedItems', 
            'supplyRequests', 
            'issuedItems', 
            'loanRequests',
            'deans',
            'advisers', 
            'students'
        ));
    }

    public function create()
    {
        // Get all users with dean role who are not assigned to any department
        $deans = User::whereHas('role', function ($query) {
            $query->where('name', 'dean');
        })->whereDoesntHave('departmentAsHead')->get();

        return view('departments.create', compact('deans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_name' => 'required|string|max:255',
            'dean_id' => 'nullable|exists:users,id',
        ]);

        // If dean_id is provided, verify the selected user has dean role and is not assigned to another department
        if ($request->dean_id) {
            $dean = User::with('role')->find($request->dean_id);
            if (!$dean || $dean->role->name !== 'dean') {
                return back()->withErrors(['dean_id' => 'Selected user must have dean role.']);
            }

            // Check if dean is already assigned to another department
            $existingDepartment = Department::where('dean_id', $request->dean_id)->first();
            if ($existingDepartment) {
                return back()->withErrors(['dean_id' => 'This dean is already assigned to another department.']);
            }
        }

        Department::create($request->only('department_name', 'dean_id'));

        return redirect()->route('departments.index')->with('success', 'Department added successfully.');
    }

    public function edit(Department $department)
    {
        // Get all users with dean role who are not assigned to any department
        // OR the current dean of this department
        $deans = User::whereHas('role', function ($query) {
            $query->where('name', 'dean');
        })->where(function ($query) use ($department) {
            $query->whereDoesntHave('departmentAsHead')
                  ->orWhere('id', $department->dean_id);
        })->get();

        return view('departments.edit', compact('department', 'deans'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'department_name' => 'required|string|max:255',
            'dean_id' => 'nullable|exists:users,id',
        ]);

        // If dean_id is provided, verify the selected user has dean role and is not assigned to another department
        if ($request->dean_id) {
            $dean = User::with('role')->find($request->dean_id);
            if (!$dean || $dean->role->name !== 'dean') {
                return back()->withErrors(['dean_id' => 'Selected user must have dean role.']);
            }

            // Check if dean is already assigned to another department (excluding current department)
            $existingDepartment = Department::where('dean_id', $request->dean_id)
                                           ->where('id', '!=', $department->id)
                                           ->first();
            if ($existingDepartment) {
                return back()->withErrors(['dean_id' => 'This dean is already assigned to another department.']);
            }
        }

        $department->update($request->only('department_name', 'dean_id'));

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }

    /**
     * Toggle department status (activate/deactivate)
     */
    public function toggleStatus(Request $request, Department $department)
    {
        $request->validate([
            'status' => 'nullable|in:active,inactive'
        ]);

        $newStatus = $request->input('status');
        if (!$newStatus) {
            $newStatus = $department->isActive() ? 'inactive' : 'active';
        }

        if ($newStatus === 'active') {
            $department->activate();
            return redirect()->route('departments.show', $department)
                ->with('success', 'Department activated successfully.');
        } else {
            $department->deactivate();
            return redirect()->route('departments.show', $department)
                ->with('success', 'Department deactivated successfully.');
        }
    }

    /**
     * Dean-only overview page for their assigned department.
     */
    public function dean(Request $request)
    {
        $user = $request->user();
        abort_unless($user && method_exists($user, 'hasRole') && $user->hasRole('dean'), 403);

        $department = $user->department;
        if (!$department) {
            return view('departments.dean', [
                'department' => null,
                'advisers' => collect(),
                'studentsByYear' => collect(),
                'issuedItems' => collect(),
                'errorMessage' => 'No department assigned to your dean profile.'
            ]);
        }

        $advisers = $department->users()
            ->whereHas('role', function ($q) { $q->where('name', 'adviser'); })
            ->orderBy('name')
            ->get();

        $students = $department->users()
            ->whereHas('role', function ($q) { $q->where('name', 'student'); })
            ->orderBy('year_level')
            ->orderBy('name')
            ->get();
        $studentsByYear = $students->groupBy('year_level');

        // Optional issued items filter by type via nav bar
        $issuedType = $request->get('issued_type'); // 'consumable' or 'grantable'
        $search = trim($request->get('search', ''));
        $issuedItemsQuery = \App\Models\IssuedItem::with(['supply', 'supplyVariant'])
            ->where('department_id', $department->id);
        if (in_array($issuedType, ['consumable', 'grantable'])) {
            $issuedItemsQuery->whereHas('supply', function ($q) use ($issuedType) {
                $q->where('supply_type', $issuedType);
            });
        }
        // Text search across supply name/description, variant name, and notes
        if ($search !== '') {
            $issuedItemsQuery->where(function ($q) use ($search) {
                $q->whereHas('supply', function ($sq) use ($search) {
                    $sq->where('name', 'LIKE', "%{$search}%")
                       ->orWhere('description', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('supplyVariant', function ($vq) use ($search) {
                    $vq->where('variant_name', 'LIKE', "%{$search}%");
                })
                ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        $issuedItems = $issuedItemsQuery->orderBy('created_at', 'desc')->get();

        return view('departments.dean', compact(
            'department',
            'advisers',
            'studentsByYear',
            'issuedItems'
        ) + ['issuedType' => $issuedType, 'search' => $search]);
    }

    /**
     * Dynamic search for members within dean's department.
     * Supports multi-select year levels and issued item type filters.
     * Returns JSON for dynamic UI updates.
     */
    public function searchMembers(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('dean'), 403);

        $department = $user->department;
        if (!$department) {
            return response()->json(['error' => 'Dean is not assigned to any department'], 422);
        }

        // Validate input filters
        $validated = $request->validate([
            'years' => ['array'],
            'years.*' => ['integer', 'min:1', 'max:10'],
            // issued item filters moved to Issued Items nav; keep for backward compatibility
            'issued_types' => ['array'],
            'issued_types.*' => ['string', 'in:consumable,grantable'],
            // optional name search
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $years = collect($validated['years'] ?? [])->map(function($y){ return (int)$y; });
        $issuedTypes = collect($validated['issued_types'] ?? []);
        $search = trim($validated['search'] ?? '');

        // Advisers in department
        $advisersQuery = User::whereHas('role', function ($q) {
                $q->where('name', 'adviser');
            })
            ->where('department_id', $department->id);

        // Students in department
        $studentsQuery = User::whereHas('role', function ($q) {
                $q->where('name', 'student');
            })
            ->where('department_id', $department->id);

        // Filter students by selected years
        if ($years->isNotEmpty()) {
            $studentsQuery->whereIn('year_level', $years->all());
        }

        // Filter advisers and students by issued item types if provided
        if ($issuedTypes->isNotEmpty()) {
            $advisersQuery->whereHas('issuedItems', function ($q) use ($issuedTypes) {
                $q->whereHas('supply', function ($sq) use ($issuedTypes) {
                    $sq->whereIn('supply_type', $issuedTypes->all());
                });
            });

            $studentsQuery->whereHas('issuedItems', function ($q) use ($issuedTypes) {
                $q->whereHas('supply', function ($sq) use ($issuedTypes) {
                    $sq->whereIn('supply_type', $issuedTypes->all());
                });
            });
        }

        // Filter by name search if provided
        if ($search !== '') {
            $advisersQuery->where('name', 'LIKE', "%{$search}%");
            $studentsQuery->where('name', 'LIKE', "%{$search}%");
        }

        $advisers = $advisersQuery->orderBy('name')
            ->get(['id', 'name', 'email']);

        $students = $studentsQuery->orderBy('year_level')->orderBy('name')
            ->get(['id', 'name', 'email', 'year_level'])
            ->groupBy('year_level');

        return response()->json([
            'department' => [
                'id' => $department->id,
                'name' => $department->department_name,
            ],
            'filters' => [
                'years' => $years->all(),
                'issued_types' => $issuedTypes->all(),
                'search' => $search,
            ],
            'advisers' => $advisers,
            'students' => $students,
        ]);
    }
}
