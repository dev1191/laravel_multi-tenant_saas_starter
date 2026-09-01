<?php

use App\Domain\Activity\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');
});
