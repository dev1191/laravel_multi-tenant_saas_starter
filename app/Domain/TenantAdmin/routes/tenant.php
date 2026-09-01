<?php

use App\Domain\TenantAdmin\Actions\LeaveImpersonation;
use App\Domain\TenantAdmin\Actions\StartImpersonation;
use Illuminate\Support\Facades\Route;

// Impersonation bridge endpoints
Route::get('/impersonate/{token}', StartImpersonation::class)->name('impersonate.start');
Route::post('/impersonate/leave', LeaveImpersonation::class)->name('impersonate.leave');
