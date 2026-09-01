<?php

namespace App\Filament\Widgets;

use App\Domain\TenantAdmin\Models\ImpersonationLog;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $trialTenants = Tenant::where('status', 'trial')->count();
        $impersonationCount = ImpersonationLog::count();

        return [
            Stat::make('Total Tenants', $totalTenants)
                ->description('Total provisioned workspaces')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make('Active Workspaces', $activeTenants)
                ->description('Paying / fully active tenants')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Trials in Progress', $trialTenants)
                ->description('Tenants in 14-day trial')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Staff Impersonations', $impersonationCount)
                ->description('Total logged impersonation sessions')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info'),
        ];
    }
}
