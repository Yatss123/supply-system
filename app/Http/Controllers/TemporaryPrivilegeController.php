<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TemporaryPrivilegeController extends Controller
{
    public function __construct()
    {
        // Restrict to Super Admin, Admin, and Dean
        $this->middleware(function ($request, $next) {
            $authUser = auth()->user();
            if (!$authUser) {
                abort(403);
            }

            if (!$authUser->hasAdminPrivileges() && !$authUser->isDean()) {
                abort(403, 'Unauthorized');
            }

            return $next($request);
        });
    }

    /**
    * Assign temporary admin privileges to a user.
    */
    public function assign(Request $request, User $user)
    {
        // Department-level constraint: Deans can only assign within their department
        $authUser = auth()->user();
        if ($authUser && $authUser->isDean() && $authUser->department_id !== $user->department_id) {
            return redirect()->route('users.show', $user)
                ->withErrors(['authorization' => 'Deans may grant privileges only to members of their department.']);
        }

        $validated = $request->validate([
            'duration' => 'required|in:1_day,1_week,custom,indefinite',
            'expires_at' => 'nullable|date',
        ]);

        $duration = $validated['duration'];
        $expiresAt = null;

        switch ($duration) {
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

        $user->temp_privilege_type = 'admin';
        $user->temp_privilege_expires_at = $expiresAt;
        $user->save();

        return redirect()->route('users.show', $user)->with('status', 'Temporary admin privileges assigned.');
    }

    /**
    * Revoke temporary admin privileges from a user.
    */
    public function revoke(User $user)
    {
        // Department-level constraint: Deans can only revoke within their department
        $authUser = auth()->user();
        if ($authUser && $authUser->isDean() && $authUser->department_id !== $user->department_id) {
            return redirect()->route('users.show', $user)
                ->withErrors(['authorization' => 'Deans may revoke privileges only from members of their department.']);
        }

        $user->temp_privilege_type = null;
        $user->temp_privilege_expires_at = null;
        $user->save();

        return redirect()->route('users.show', $user)->with('status', 'Temporary admin privileges revoked.');
    }
}