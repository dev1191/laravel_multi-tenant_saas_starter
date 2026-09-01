<?php

namespace App\Http\Controllers\Tenant;

use App\Domain\Tasks\Models\Task;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $team = $user->currentTeam();

        $stats = [
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::where('status', 'completed')->count(),
            'pending_tasks' => Task::where('status', '!=', 'completed')->count(),
            'team_members_count' => $team ? $team->members()->count() : User::count(),
        ];

        $recentTasks = Task::with(['assignedUser', 'creator'])
            ->latest()
            ->take(5)
            ->get();

        $recentActivities = Activity::with('causer')
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'causer_name' => $activity->causer?->name ?? 'System',
                    'event' => $activity->event,
                    'is_impersonated' => ! empty($activity->impersonation_token),
                    'created_at' => $activity->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recent_tasks' => $recentTasks,
            'recent_activities' => $recentActivities,
        ]);
    }
}
