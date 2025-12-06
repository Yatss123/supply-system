<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Department;
use App\Models\ProfileUpdateRequest as ProfileUpdateRequestModel;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $departments = Department::all();
        
        return view('profile.edit', [
            'user' => $request->user(),
            'departments' => $departments,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        // Check if user has a pending profile update request
        if ($user->hasPendingProfileUpdate()) {
            return Redirect::route('profile.edit')->with('error', 'You have a pending profile update request. Please wait for admin approval before making new changes.');
        }

        // Validate the request
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'address' => ['nullable', 'string', 'max:500'],
            'date_of_birth' => ['nullable', 'date'],
            'civil_status' => ['nullable', 'string', 'in:single,married,divorced,widowed'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'department_id' => ['required', 'exists:departments,id'],
            'role' => ['required', 'string', Rule::in(['student', 'adviser', 'dean'])],
        ];

        // Year level is required only for students
        if ($request->role === 'student') {
            $validationRules['year_level'] = ['required', 'integer', 'min:1', 'max:6'];
        } else {
            $validationRules['year_level'] = ['nullable', 'integer', 'min:1', 'max:6'];
        }

        $validated = $request->validate($validationRules);

        // Get only the fields that have changed
        $changes = [];
        foreach ($validated as $field => $value) {
            // Special handling for role field - compare with role_id
            if ($field === 'role') {
                $role = \App\Models\Role::where('name', $value)->first();
                $newRoleId = $role ? $role->id : null;
                if ($user->role_id != $newRoleId) {
                    $changes['role_id'] = $newRoleId;
                }
            } else {
                if ($user->$field != $value) {
                    $changes[$field] = $value;
                }
            }
        }

        // If no changes, redirect back
        if (empty($changes)) {
            return Redirect::route('profile.edit')->with('info', 'No changes detected.');
        }

        // Create profile update request
        ProfileUpdateRequestModel::create([
            'user_id' => $user->id,
            'requested_changes' => $changes,
            'status' => 'pending',
        ]);

        return Redirect::route('profile.edit')->with('success', 'Profile update request submitted successfully. Please wait for admin approval.');
    }

    /**
     * Cancel pending profile update request.
     */
    public function cancelRequest(Request $request): RedirectResponse
    {
        $user = $request->user();
        $pendingRequest = $user->pendingProfileUpdateRequest();

        if ($pendingRequest) {
            $pendingRequest->delete();
            return Redirect::route('profile.edit')->with('success', 'Profile update request cancelled successfully.');
        }

        return Redirect::route('profile.edit')->with('error', 'No pending profile update request found.');
    }
}
