<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileCompletionController extends Controller
{
    /**
     * Show the profile completion form.
     */
    public function show()
    {
        $user = Auth::user();
        
        // If profile is already complete, redirect to dashboard
        if ($this->isProfileComplete($user)) {
            return redirect()->route('dashboard');
        }

        $departments = Department::all();
        
        return view('auth.complete-profile', compact('departments'));
    }

    /**
     * Handle the profile completion form submission.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validationRules = [
            'address' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'civil_status' => ['required', 'string', Rule::in(['single', 'married', 'divorced', 'widowed'])],
            'gender' => ['required', 'string', Rule::in(['male', 'female', 'other'])],
            'contact_number' => ['required', 'string', 'max:20'],
            'role' => ['required', 'string', Rule::in(['student', 'adviser', 'dean'])],
            'department_id' => ['required', 'exists:departments,id'],
        ];

        // Year level is required only for students
        if ($request->role === 'student') {
            $validationRules['year_level'] = ['required', 'integer', 'min:1', 'max:6'];
        } else {
            $validationRules['year_level'] = ['nullable', 'integer', 'min:1', 'max:6'];
        }

        $request->validate($validationRules);

        $user->update([
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'civil_status' => $request->civil_status,
            'gender' => $request->gender,
            'contact_number' => $request->contact_number,
            'department_id' => $request->department_id,
            'year_level' => $request->role === 'student' ? $request->year_level : null,
            'profile_completed' => true,
        ]);

        // Update user's role based on selected role
        $role = Role::where('name', $request->role)->first();
        if ($role) {
            $user->update(['role_id' => $role->id]);
        }

        return redirect()->route('dashboard')->with('success', 'Profile completed successfully!');
    }

    /**
     * Check if user profile is complete.
     */
    private function isProfileComplete($user)
    {
        $basicFieldsComplete = $user->profile_completed && 
                              $user->address && 
                              $user->date_of_birth && 
                              $user->civil_status && 
                              $user->gender && 
                              $user->contact_number &&
                              $user->department_id;

        // If user is a student, year_level is also required
        if ($user->role && $user->role->name === 'student') {
            return $basicFieldsComplete && $user->year_level;
        }

        return $basicFieldsComplete;
    }
}