<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DeanAccessController extends Controller
{
    public function __construct()
    {
        // Restrict to authenticated users with dean role
        $this->middleware(function ($request, $next) {
            $authUser = auth()->user();
            if (!$authUser || !$authUser->hasRole('dean')) {
                abort(403, 'Dean access only.');
            }
            return $next($request);
        });
    }

    /**
     * Dedicated profile for deans to manage department-scoped dean-level access.
     */
    public function showProfile(User $user)
    {
        $authUser = auth()->user();

        // Load role relationship to avoid null references
        if (!$user->relationLoaded('role')) {
            $user->load('role', 'department');
        }

        $sameDepartment = $authUser->department_id && $user->department_id && $authUser->department_id === $user->department_id;
        $canManage = $sameDepartment && $user->id !== $authUser->id && $this->isAssignableRole($user);

        return view('dean.users.profile', [
            'user' => $user,
            'authUser' => $authUser,
            'canManage' => $canManage,
            'sameDepartment' => $sameDepartment,
        ]);
    }

    /**
     * Assign temporary dean-level privileges scoped to department.
     */
    public function assign(Request $request, User $user)
    {
        $authUser = auth()->user();

        // Authorization checks adhering to RBAC
        $errorRedirect = $this->authorizeDepartmentDeanAction($authUser, $user);
        if ($errorRedirect) {
            return $errorRedirect;
        }

        $validated = $request->validate([
            'duration' => 'required|in:1_day,1_week,custom,indefinite',
            'expires_at' => 'nullable|date',
        ]);

        $expiresAt = null;
        switch ($validated['duration']) {
            case '1_day':
                $expiresAt = now()->addDay();
                break;
            case '1_week':
                $expiresAt = now()->addWeek();
                break;
            case 'custom':
                $expiresAt = isset($validated['expires_at']) ? Carbon::parse($validated['expires_at']) : null;
                if (!$expiresAt) {
                    return back()->withErrors(['expires_at' => 'Please specify a valid custom expiration date.']);
                }
                if (!$expiresAt->isFuture()) {
                    return back()->withErrors(['expires_at' => 'Custom expiration must be in the future.']);
                }
                break;
            case 'indefinite':
                $expiresAt = null;
                break;
        }

        $user->temp_privilege_type = 'dean';
        $user->temp_privilege_expires_at = $expiresAt;
        $user->save();

        return redirect()->route('dean.users.profile', $user)->with('status', 'Temporary dean-level privileges assigned.');
    }

    /**
     * Revoke temporary dean-level privileges scoped to department.
     */
    public function revoke(User $user)
    {
        $authUser = auth()->user();

        // Authorization checks adhering to RBAC
        $errorRedirect = $this->authorizeDepartmentDeanAction($authUser, $user);
        if ($errorRedirect) {
            return $errorRedirect;
        }

        // Only clear dean temp privileges; leave other temp privileges untouched
        if ($user->temp_privilege_type === 'dean') {
            $user->temp_privilege_type = null;
            $user->temp_privilege_expires_at = null;
            $user->save();
        }

        return redirect()->route('dean.users.profile', $user)->with('status', 'Temporary dean-level privileges revoked.');
    }

    /**
     * Authorization function enforcing department-level RBAC constraints for deans.
     */
    private function authorizeDepartmentDeanAction(User $authUser, User $targetUser)
    {
        // Must be dean
        if (!$authUser || !$authUser->hasRole('dean')) {
            return redirect()->route('dean.users.profile', $targetUser)
                ->withErrors(['authorization' => 'Only deans can manage dean-level access.']);
        }

        // Prevent self-assignment
        if ($authUser->id === $targetUser->id) {
            return redirect()->route('dean.users.profile', $targetUser)
                ->withErrors(['authorization' => 'You cannot assign privileges to yourself.']);
        }

        // Must be within the same department
        if (!$authUser->department_id || !$targetUser->department_id || $authUser->department_id !== $targetUser->department_id) {
            return redirect()->route('dean.users.profile', $targetUser)
                ->withErrors(['authorization' => 'You can only manage privileges for members of your department.']);
        }

        // Target must be eligible (student or adviser) for dean-level delegation
        if (!$this->isAssignableRole($targetUser)) {
            return redirect()->route('dean.users.profile', $targetUser)
                ->withErrors(['authorization' => 'Dean-level access can only be delegated to students or advisers.']);
        }

        return null; // Authorized
    }

    /**
     * Check if the target user role is eligible for dean-level delegation.
     */
    private function isAssignableRole(User $targetUser): bool
    {
        $roleName = $targetUser->role->name ?? null;
        return in_array($roleName, ['student', 'adviser']);
    }
}