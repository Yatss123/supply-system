<?php

namespace App\Http\Controllers;

use App\Models\ProfileUpdateRequest;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminProfileController extends Controller
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
     * Display a listing of profile update requests.
     */
    public function index(Request $request): View
    {
        $query = ProfileUpdateRequest::with(['user', 'reviewer']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.profile-requests.index', compact('requests'));
    }

    /**
     * Display the specified profile update request.
     */
    public function show(ProfileUpdateRequest $profileRequest): View
    {
        $profileRequest->load(['user', 'reviewer']);
        $departments = Department::active()->get();

        return view('admin.profile-requests.show', compact('profileRequest', 'departments'));
    }

    /**
     * Approve the profile update request.
     */
    public function approve(Request $request, ProfileUpdateRequest $profileRequest): RedirectResponse
    {
        if (!$profileRequest->isPending()) {
            return redirect()->back()->with('error', 'This request has already been processed.');
        }

        $user = $profileRequest->user;
        $changes = $profileRequest->requested_changes;

        // Apply the changes to the user
        foreach ($changes as $field => $value) {
            $user->$field = $value;
        }

        // Handle role assignment when department is changed
        if (isset($changes['department_id'])) {
            // Get the student role (default role for users with departments)
            $studentRole = \App\Models\Role::where('name', 'student')->first();
            
            if ($studentRole && (!$user->role_id || $user->role->name === 'user')) {
                // Only assign student role if user has no role or is a regular user
                $user->role_id = $studentRole->id;
            }
        }

        // Handle email verification reset if email was changed
        if (isset($changes['email'])) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Update the request status
        $profileRequest->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        return redirect()->route('admin.profile-requests.index')
            ->with('success', 'Profile update request approved successfully.');
    }

    /**
     * Reject the profile update request.
     */
    public function reject(Request $request, ProfileUpdateRequest $profileRequest): RedirectResponse
    {
        if (!$profileRequest->isPending()) {
            return redirect()->back()->with('error', 'This request has already been processed.');
        }

        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $profileRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        return redirect()->route('admin.profile-requests.index')
            ->with('success', 'Profile update request rejected successfully.');
    }
}
