<?php

namespace App\Domain\Activity\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $activities = Activity::with('causer')
            ->latest()
            ->paginate(15)
            ->through(function ($activity) {
                return [
                    'id' => $activity->id,
                    'log_name' => $activity->log_name,
                    'description' => $activity->description,
                    'subject_type' => class_basename($activity->subject_type ?? ''),
                    'event' => $activity->event,
                    'causer_name' => $activity->causer?->name ?? 'System / Automatic',
                    'is_impersonated' => ! empty($activity->impersonation_token),
                    'impersonation_token' => $activity->impersonation_token ? substr($activity->impersonation_token, 0, 8).'...' : null,
                    'properties' => $activity->properties,
                    'created_at' => $activity->created_at->format('M j, Y H:i:s'),
                ];
            });

        return Inertia::render('Activity/Index', [
            'activities' => $activities,
        ]);
    }
}
