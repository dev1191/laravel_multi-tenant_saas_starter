<?php

use App\Domain\Billing\Controllers\StripeWebhookController;
use App\Domain\Billing\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    if (function_exists('tenant') && tenant()) {
        return redirect()->route('dashboard');
    }

    $plans = Plan::with('prices')->get()->map(function (Plan $plan) {
        $usdPrice = $plan->prices->firstWhere('currency', 'USD') ?? $plan->prices->first();

        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'billing_period' => $plan->billing_period,
            'features' => $plan->features ?? [],
            'price_formatted' => $usdPrice?->formatted_amount ?? '$0.00',
            'prices' => $plan->prices->map(fn ($p) => [
                'currency' => $p->currency,
                'formatted' => $p->formatted_amount,
            ]),
        ];
    });

    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'plans' => $plans,
        'metrics' => [
            'tenants_count' => Tenant::count(),
            'plans_count' => $plans->count(),
        ],
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

// Central unauthenticated payment webhooks
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handleWebhook'])->name('cashier.webhook');

require __DIR__.'/settings.php';
