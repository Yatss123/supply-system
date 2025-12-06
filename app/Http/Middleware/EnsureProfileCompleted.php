<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Skip middleware for profile completion routes, logout, and dashboard
        if ($request->routeIs('profile.complete') || 
            $request->routeIs('profile.complete.store') ||
            $request->routeIs('logout') ||
            $request->routeIs('dashboard') ||
            $request->routeIs('profile.show') ||
            $request->routeIs('profile.edit') ||
            $request->routeIs('profile.update') ||
            $request->routeIs('profile.cancel-request')) {
            return $next($request);
        }

        // Skip profile completion requirement for admin roles
        if ($user && $this->hasAdminPrivileges($user)) {
            return $next($request);
        }

        // Check if user profile is complete for restricted routes (only for non-admin users)
        if ($user && !$this->isProfileComplete($user)) {
            // Redirect to dashboard with error message for restricted functionality
            return redirect()->route('dashboard')->with('error', 'Please complete your profile to access this feature.');
        }

        return $next($request);
    }

    /**
     * Check if user profile is complete.
     */
    private function isProfileComplete($user)
    {
        return $user->profile_completed && 
               $user->address && 
               $user->date_of_birth && 
               $user->civil_status && 
               $user->gender && 
               $user->contact_number;
    }

    /**
     * Check if user has admin privileges (admin, super_admin, dean, adviser).
     */
    private function hasAdminPrivileges($user)
    {
        // Load role relationship to prevent null reference errors
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        // Allow admin, super_admin, dean, and adviser roles to bypass profile completion
        return $user->hasRole('admin') || 
               $user->hasRole('super_admin') || 
               $user->hasRole('dean') || 
               $user->hasRole('adviser');
    }
}
