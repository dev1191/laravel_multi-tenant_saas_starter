<?php

namespace App\Domain\Tasks\Actions;

use App\Domain\Tasks\Models\Task;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteTask
{
    use AsAction;

    public function handle(Task $task): bool
    {
        return (bool) $task->delete();
    }

    public function asController(Request $request, Task $task)
    {
        $this->handle($task);

        return back()->with('success', 'Task deleted.');
    }
}
