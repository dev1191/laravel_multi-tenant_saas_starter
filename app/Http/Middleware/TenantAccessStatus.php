<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantAccessStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (! $tenant) {
            return $next($request);
        }

        // 1. Suspended tenant
        if ($tenant->isSuspended()) {
            abort(403, 'This workspace has been suspended. Please contact administrator support.');
        }

        // 2. Provisioning tenant
        if ($tenant->isProvisioning()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'provisioning',
                    'message' => 'Workspace is currently being prepared. Please check back in a few moments.',
                ], 503);
            }

            return response()->view('provisioning', [
                'tenant' => $tenant,
            ], 503);
        }

        // 3. Expired trial / Unsubscribed check
        if ($tenant->hasExpiredTrial()) {
            // Allow access to billing, auth/logout routes
            if ($request->is('billing*') || $request->is('logout') || $request->is('login') || $request->is('register')) {
                return $next($request);
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'trial_expired',
                    'message' => 'Your 14-day trial has expired. Please upgrade your plan to continue using this workspace.',
                    'redirect' => url('/billing'),
                ], 402);
            }

            return redirect()->to('/billing')->with('warning', 'Your free trial has ended. Please choose a plan to keep full access to your workspace.');
        }

        return $next($request);
    }
}
