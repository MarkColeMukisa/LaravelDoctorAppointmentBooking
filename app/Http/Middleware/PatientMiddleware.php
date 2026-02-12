<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PatientMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (! auth()->check()) {
            return redirect('/login');
        }

        // Check if user has patient role
        if (auth()->user()->role == User::ROLE_PATIENT) {
            return $next($request);
        }

        // If user is authenticated but not a patient, redirect to their appropriate dashboard
        $role = auth()->user()->role;
        if ($role == User::ROLE_DOCTOR) {
            return redirect()->route('doctor-dashboard');
        } elseif ($role == User::ROLE_ADMIN) {
            return redirect()->route('admin-dashboard');
        }

        // Fallback to login if role is invalid
        return redirect('/login');
    }
}
