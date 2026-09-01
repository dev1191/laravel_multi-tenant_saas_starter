<?php

namespace App\Domain\Tasks\Controllers;

use App\Domain\Tasks\Models\Task;
use App\Domain\Teams\Models\Team;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $team = $user->currentTeam() ?? Team::first();

        $tasks = Task::with(['assignedUser', 'creator'])
            ->when($team, fn ($q) => $q->where('team_id', $team->id))
            ->latest()
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'assigned_user' => $task->assignedUser ? [
                    'id' => $task->assignedUser->id,
                    'name' => $task->assignedUser->name,
                ] : null,
                'creator' => $task->creator ? [
                    'id' => $task->creator->id,
                    'name' => $task->creator->name,
                ] : null,
                'due_at' => $task->due_at?->format('M j, Y'),
                'created_at' => $task->created_at->format('M j, Y'),
            ]);

        $teamMembers = $team ? $team->members()->get(['users.id', 'users.name']) : [];

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'team_members' => $teamMembers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $team = $user->currentTeam() ?? Team::first();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'due_at' => ['nullable', 'date'],
        ]);

        Task::create([
            ...$validated,
            'status' => 'todo',
            'created_by' => $user->id,
            'team_id' => $team?->id,
        ]);

        return back()->with('success', 'Task created successfully.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:todo,in_progress,completed'],
            'priority' => ['sometimes', 'in:low,medium,high'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'due_at' => ['nullable', 'date'],
        ]);

        $task->update($validated);

        return back()->with('success', 'Task updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return back()->with('success', 'Task deleted.');
    }
}
