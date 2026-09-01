<?php

use App\Domain\Teams\Actions\AcceptTeamInvite;
use App\Domain\Teams\Actions\CreateTeamInvite;
use App\Domain\Teams\Actions\RemoveTeamMember;
use App\Domain\Teams\Controllers\TeamController;
use App\Domain\Teams\Controllers\TeamInviteController;
use Illuminate\Support\Facades\Route;

// Public Team Invitation acceptance
Route::get('/invite/{token}', [TeamInviteController::class, 'show'])->name('team.invites.accept_view');
Route::post('/invite/{token}/accept', AcceptTeamInvite::class)->name('team.invites.accept');

// Authenticated Team Management
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::post('/team/invite', CreateTeamInvite::class)->name('team.invite');
    Route::delete('/team/members/{member}', RemoveTeamMember::class)->name('team.members.remove');
    Route::delete('/team/invites/{invite}', [TeamController::class, 'removeInvite'])->name('team.invites.remove');
});
