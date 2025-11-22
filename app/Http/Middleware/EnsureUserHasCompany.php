<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = $request->user();

        // Skip company check for super admin and registration pages
        if ($user->hasRole('super-admin') || $request->is('company/register*')) {
            return $next($request);
        }

        // If user doesn't have a company, redirect to registration
        if (!$user->company_id) {
            return redirect()->route('company.register');
        }

        // If company is not active, show suspension notice
        if (!$user->company->is_active) {
            return redirect()->route('company.suspended');
        }

        // If trial has ended and no active subscription
        if ($user->company->subscription_status === 'trial' &&
            $user->company->trial_ends_at &&
            $user->company->trial_ends_at->isPast()) {
            return redirect()->route('company.subscription');
        }

        // Update last login time
        if (!$user->last_login_at || $user->last_login_at->diffInHours(now()) > 1) {
            $user->update(['last_login_at' => now()]);
        }

        return $next($request);
    }
}
