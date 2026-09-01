<?php

use App\Domain\Settings\Controllers\SiteSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/settings/site', [SiteSettingsController::class, 'edit'])->name('settings.site.edit');
    Route::patch('/settings/site', [SiteSettingsController::class, 'update'])->name('settings.site.update');
    Route::get('/settings/site/email-preview', [SiteSettingsController::class, 'emailPreview'])->name('settings.site.email-preview');
    Route::post('/settings/site/test-email', [SiteSettingsController::class, 'testEmail'])->name('settings.site.test-email');
    Route::post('/settings/locales', [SiteSettingsController::class, 'storeLocale'])->name('settings.locales.store');
});
