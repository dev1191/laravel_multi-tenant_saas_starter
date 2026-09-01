<?php

namespace App\Domain\Billing\Controllers;

use App\Domain\Billing\Contracts\BillingGateway;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\PlanPrice;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function __construct(
        protected BillingGateway $billingGateway
    ) {}

    public function index(Request $request): Response
    {
        $tenant = tenant();
        $currency = $tenant->default_currency ?? 'USD';

        $plans = Plan::with(['prices' => function ($query) use ($currency) {
            $query->where('currency', $currency);
        }])->get()->map(function (Plan $plan) use ($tenant) {
            $price = $plan->prices->first();

            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'billing_period' => $plan->billing_period,
                'features' => $plan->features ?? [],
                'is_current' => $tenant->plan === $plan->slug,
                'price' => $price ? [
                    'id' => $price->id,
                    'amount' => $price->amount,
                    'formatted' => $price->formatted_amount,
                    'currency' => $price->currency,
                ] : null,
            ];
        });

        $subscription = $tenant->subscription('default');

        return Inertia::render('Billing/Index', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'plan' => $tenant->plan,
                'status' => $tenant->status,
                'on_trial' => $tenant->onTrial(),
                'has_expired_trial' => $tenant->hasExpiredTrial(),
                'trial_ends_at' => $tenant->trial_ends_at?->format('M j, Y'),
                'default_currency' => $currency,
            ],
            'subscription' => $subscription ? [
                'name' => $subscription->type,
                'status' => $subscription->stripe_status,
                'ends_at' => $subscription->ends_at?->format('M j, Y'),
                'on_grace_period' => $subscription->onGracePeriod(),
            ] : null,
            'plans' => $plans,
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_price_id' => ['required', \Illuminate\Validation\Rule::exists(PlanPrice::class, 'id')],
        ]);

        $tenant = tenant();
        $subscription = $tenant->subscription('default');

        // If tenant already has an active subscription, redirect to portal to manage plan upgrades/downgrades with proration
        if ($subscription && $subscription->active() && ! $subscription->onGracePeriod()) {
            $returnUrl = route('billing.index');
            $portalUrl = $this->billingGateway->createCustomerPortalSession($tenant, $returnUrl);

            return Inertia::location($portalUrl);
        }

        $planPrice = PlanPrice::with('plan')->findOrFail($validated['plan_price_id']);
        $returnUrl = route('billing.index');
        $checkoutUrl = $this->billingGateway->createCheckoutSession($tenant, $planPrice, $returnUrl);

        return Inertia::location($checkoutUrl);
    }

    public function portal(Request $request): RedirectResponse
    {
        $tenant = tenant();
        $returnUrl = route('billing.index');
        $portalUrl = $this->billingGateway->createCustomerPortalSession($tenant, $returnUrl);

        return Inertia::location($portalUrl);
    }

    public function cancel(Request $request): RedirectResponse
    {
        $tenant = tenant();
        $this->billingGateway->cancelSubscription($tenant);

        return back()->with('success', 'Your subscription has been canceled.');
    }

    public function resume(Request $request): RedirectResponse
    {
        $tenant = tenant();
        $this->billingGateway->resumeSubscription($tenant);

        return back()->with('success', 'Your subscription has been resumed.');
    }
}
