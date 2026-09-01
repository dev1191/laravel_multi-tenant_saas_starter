<?php

use App\Domain\Tasks\Actions\CreateTask;
use App\Domain\Tasks\Actions\DeleteTask;
use App\Domain\Tasks\Actions\UpdateTask;
use App\Domain\Tasks\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', CreateTask::class)->name('tasks.store');
    Route::patch('/tasks/{task}', UpdateTask::class)->name('tasks.update');
    Route::delete('/tasks/{task}', DeleteTask::class)->name('tasks.destroy');
});
