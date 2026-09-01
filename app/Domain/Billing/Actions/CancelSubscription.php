<?php

namespace App\Domain\Billing\Actions;

use App\Domain\Billing\Contracts\BillingGateway;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class CancelSubscription
{
    use AsAction;

    public function handle(Tenant $tenant): bool
    {
        return app(BillingGateway::class)->cancelSubscription($tenant);
    }

    public function asController(Request $request)
    {
        $tenant = tenant();
        $this->handle($tenant);

        return back()->with('success', 'Your subscription has been canceled.');
    }
}
