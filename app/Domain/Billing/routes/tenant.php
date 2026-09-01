<?php

use App\Domain\Billing\Actions\CancelSubscription;
use App\Domain\Billing\Actions\CreateCheckoutSession;
use App\Domain\Billing\Actions\ResumeSubscription;
use App\Domain\Billing\Controllers\BillingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/checkout', CreateCheckoutSession::class)->name('billing.checkout');
    Route::post('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
    Route::post('/billing/cancel', CancelSubscription::class)->name('billing.cancel');
    Route::post('/billing/resume', ResumeSubscription::class)->name('billing.resume');
});
