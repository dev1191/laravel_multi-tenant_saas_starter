<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;

class InitializeTenancyIfSubdomain
{
    /**
     * Handle an incoming request.
     * Initializes tenancy if visiting a tenant subdomain/domain, or passes through for central domains.
     */
    public function handle(Request $request, Closure $next)
    {
        $hostname = $request->getHost();
        $isCentral = in_array($hostname, config('tenancy.central_domains', []), true);

        if (! $isCentral && (! function_exists('tenant') || ! tenant())) {
            return app(InitializeTenancyByDomainOrSubdomain::class)->handle($request, $next);
        }

        return $next($request);
    }
}
