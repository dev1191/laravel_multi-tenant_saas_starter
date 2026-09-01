<?php

namespace App\Domain\Tasks\Actions;

use App\Domain\Tasks\Models\Task;
use App\Domain\Teams\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateTask
{
    use AsAction;

    public function handle(User $user, ?Team $team, array $data): Task
    {
        return Task::create([
            ...$data,
            'status' => $data['status'] ?? 'todo',
            'created_by' => $user->id,
            'team_id' => $team?->id,
        ]);
    }

    public function asController(Request $request)
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

        $this->handle($user, $team, $validated);

        return back()->with('success', 'Task created successfully.');
    }
}
