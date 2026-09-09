<?php

namespace App\Domain\TenantAdmin\Actions;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Lorisleiva\Actions\Concerns\AsAction;

class RetryTenantProvisioning
{
    use AsAction;

    public function handle(Tenant $tenant): Tenant
    {
        $tenant->update([
            'status' => TenantStatus::Provisioning,
        ]);

        $tenant->setProvisioningStep('initializing');

        ProvisionTenantDatabase::dispatch(
            $tenant,
            $tenant->name.' Administrator',
            $tenant->email,
            null
        );

        return $tenant;
    }
}
