<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Domain\TenantAdmin\Actions\ProvisionTenantDatabase;
use App\Filament\Resources\TenantResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $tenantId = $data['id'];
        $domainName = $tenantId.'.'.request()->getHost();

        // 1. Create Tenant in provisioning state
        $data['status'] = \App\Enums\TenantStatus::Provisioning;
        $tenant = static::getModel()::create($data);

        // 2. Create Domains (full domain and subdomain slug)
        $tenant->domains()->create([
            'domain' => $domainName,
        ]);
        $tenant->domains()->create([
            'domain' => $tenantId,
        ]);

        // 3. Dispatch Provisioning Job
        ProvisionTenantDatabase::dispatch(
            $tenant,
            $data['name'].' Admin',
            $data['email'],
            'password' // Default initial password or generated
        );

        return $tenant;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
