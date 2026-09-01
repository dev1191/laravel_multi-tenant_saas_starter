<?php

namespace App\Models;

use App\Enums\TenantStatus;
use App\Support\Country;
use App\Support\Currency;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use Billable, HasDatabase, HasDomains;

    /**
     * The attributes that are mass assignable or custom columns on the tenant model.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'logo_path',
            'logo_light_path',
            'logo_dark_path',
            'plan',
            'status',
            'trial_ends_at',
            'country_code',
            'default_currency',
            'default_locale',
            'timezone',
            'preferred_gateway',
            'tax_id',
            'tax_exempt',
            'billing_address',
            'db_host',
            'stripe_id',
            'pm_type',
            'pm_last_four',
            'data',
        ];
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
            'trial_ends_at' => 'datetime',
            'tax_exempt' => 'boolean',
            'billing_address' => 'array',
            'data' => 'array',
        ];
    }

    /**
     * Impersonation logs relationship.
     */
    public function impersonationLogs(): HasMany
    {
        return $this->hasMany(ImpersonationLog::class, 'tenant_id', 'id');
    }

    /**
     * Check if tenant is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === TenantStatus::Active || $this->status === 'active';
    }

    /**
     * Check if tenant is in trial.
     */
    public function onTrial(): bool
    {
        if ($this->status !== TenantStatus::Trial && $this->status !== 'trial') {
            return false;
        }

        return $this->trial_ends_at === null || $this->trial_ends_at->isFuture();
    }

    /**
     * Check if tenant's trial has expired.
     */
    public function hasExpiredTrial(): bool
    {
        $isTrial = $this->status === TenantStatus::Trial || $this->status === 'trial';

        return $isTrial && $this->trial_ends_at !== null && $this->trial_ends_at->isPast();
    }

    /**
     * Check if tenant is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === TenantStatus::Suspended || $this->status === 'suspended';
    }

    /**
     * Check if tenant is in provisioning state.
     */
    public function isProvisioning(): bool
    {
        return $this->status === TenantStatus::Provisioning || $this->status === 'provisioning';
    }

    /**
     * Get primary domain name.
     */
    public function getPrimaryDomainAttribute(): ?string
    {
        return $this->domains->first()?->domain
            ?? ($this->id ? $this->id.'.'.(config('tenancy.central_domains.0') ?? 'tenantforge.test') : null);
    }

    /**
     * Get the human-readable country name.
     */
    public function getCountryNameAttribute(): ?string
    {
        return $this->country_code ? Country::getName($this->country_code) : null;
    }

    /**
     * Get the currency symbol for tenant's default currency.
     */
    public function getCurrencySymbolAttribute(): ?string
    {
        return $this->default_currency ? Currency::getSymbol($this->default_currency) : null;
    }
}
