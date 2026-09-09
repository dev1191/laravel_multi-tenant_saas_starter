<?php

namespace App\Http\Controllers\Central;

use App\Domain\TenantAdmin\Actions\RetryTenantProvisioning;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    /**
     * Display the onboarding progress screen for the given tenant.
     */
    public function show(Tenant $tenant): Response|RedirectResponse
    {
        // If already ready, redirect straight to tenant dashboard
        if (! $tenant->isProvisioning() && ! $tenant->isFailed()) {
            return redirect()->away($this->resolveDashboardUrl($tenant));
        }

        return Inertia::render('Onboarding/Progress', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'status' => $tenant->status->value ?? (string) $tenant->status,
                'step' => $tenant->getProvisioningStep(),
                'progress_percent' => $tenant->getProvisioningPercent(),
                'error' => $tenant->getProvisioningError(),
            ],
            'dashboard_url' => $this->resolveDashboardUrl($tenant),
            'central_domain' => config('tenancy.central_domains.0') ?? request()->getHost(),
        ]);
    }

    /**
     * Return JSON status of the tenant provisioning pipeline for live polling.
     */
    public function status(Tenant $tenant): JsonResponse
    {
        $tenant->refresh();

        $isReady = ! $tenant->isProvisioning() && ! $tenant->isFailed();

        return response()->json([
            'tenant_id' => $tenant->id,
            'name' => $tenant->name,
            'status' => $tenant->status->value ?? (string) $tenant->status,
            'step' => $tenant->getProvisioningStep(),
            'progress_percent' => $tenant->getProvisioningPercent(),
            'is_ready' => $isReady,
            'error' => $tenant->getProvisioningError(),
            'dashboard_url' => $this->resolveDashboardUrl($tenant),
        ]);
    }

    /**
     * Retry a failed tenant provisioning pipeline.
     */
    public function retry(Tenant $tenant): JsonResponse|RedirectResponse
    {
        RetryTenantProvisioning::run($tenant);

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Provisioning restarted successfully.',
                'status' => 'provisioning',
                'step' => 'initializing',
            ]);
        }

        return back();
    }

    /**
     * Resolve the absolute URL to the tenant workspace dashboard.
     */
    protected function resolveDashboardUrl(Tenant $tenant): string
    {
        $scheme = request()->getScheme();
        $domain = $tenant->primary_domain;
        $port = request()->getPort();
        $portSuffix = ($port && ! in_array($port, [80, 443])) ? ":{$port}" : '';

        return "{$scheme}://{$domain}{$portSuffix}/dashboard";
    }
}
