<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Activitylog\Facades\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;
use Symfony\Component\HttpFoundation\Response;

class TagImpersonatedActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $impersonationToken = session('impersonation_token');

        if ($impersonationToken) {
            // Listen to creating activity event and attach token
            ActivityModel::creating(function (ActivityModel $activity) use ($impersonationToken) {
                if (empty($activity->impersonation_token)) {
                    $activity->impersonation_token = $impersonationToken;
                }
            });
        }

        return $next($request);
    }
}
