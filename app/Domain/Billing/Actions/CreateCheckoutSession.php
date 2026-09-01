<?php

namespace App\Domain\Billing\Actions;

use App\Domain\Billing\Contracts\BillingGateway;
use App\Domain\Billing\Models\PlanPrice;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCheckoutSession
{
    use AsAction;

    public function handle(Tenant $tenant, PlanPrice $planPrice, string $returnUrl): string
    {
        return app(BillingGateway::class)->createCheckoutSession($tenant, $planPrice, $returnUrl);
    }

    public function asController(Request $request)
    {
        $validated = $request->validate([
            'plan_price_id' => ['required', \Illuminate\Validation\Rule::exists(PlanPrice::class, 'id')],
        ]);

        $planPrice = PlanPrice::with('plan')->findOrFail($validated['plan_price_id']);
        $tenant = tenant();

        $returnUrl = route('billing.index');
        $checkoutUrl = $this->handle($tenant, $planPrice, $returnUrl);

        return Inertia::location($checkoutUrl);
    }
}
