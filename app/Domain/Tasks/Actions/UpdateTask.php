<?php

namespace App\Domain\Tasks\Actions;

use App\Domain\Tasks\Models\Task;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateTask
{
    use AsAction;

    public function handle(Task $task, array $data): Task
    {
        $task->update($data);

        return $task;
    }

    public function asController(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:todo,in_progress,completed'],
            'priority' => ['sometimes', 'in:low,medium,high'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'due_at' => ['nullable', 'date'],
        ]);

        $this->handle($task, $validated);

        return back()->with('success', 'Task updated.');
    }
}
