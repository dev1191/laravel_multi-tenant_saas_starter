<?php

namespace App\Domain\TenantAdmin\Actions;

use App\Domain\TenantAdmin\Models\ImpersonationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class LeaveImpersonation
{
    use AsAction;

    public function handle(): void
    {
        $token = session('impersonation_token');

        if ($token) {
            ImpersonationLog::where('token', $token)
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);
        }

        Auth::logout();
        session()->forget(['impersonation_token', 'impersonating_staff_id']);
    }

    public function asController(Request $request): RedirectResponse
    {
        $this->handle();

        $centralDomain = config('tenancy.central_domains')[0] ?? 'localhost';

        return redirect()->away('http://'.$centralDomain.'/admin/tenants');
    }
}
