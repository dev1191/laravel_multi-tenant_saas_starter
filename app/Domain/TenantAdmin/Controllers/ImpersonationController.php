<?php

namespace App\Domain\TenantAdmin\Controllers;

use App\Domain\TenantAdmin\Models\ImpersonationLog;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function start(Request $request, string $token): RedirectResponse
    {
        $log = ImpersonationLog::where('token', $token)
            ->whereNull('ended_at')
            ->first();

        if (! $log) {
            abort(403, 'Invalid or expired impersonation token.');
        }

        $user = null;
        if ($log->impersonated_user_id) {
            $user = User::find($log->impersonated_user_id);
        }

        if (! $user) {
            $user = User::first();
        }

        if (! $user) {
            abort(404, 'No workspace user available to impersonate.');
        }

        Auth::login($user);

        session(['impersonation_token' => $token]);
        session(['impersonating_staff_id' => $log->central_user_id]);

        return redirect()->route('dashboard')->with('status', 'Impersonation session started.');
    }

    public function leave(Request $request): RedirectResponse
    {
        $token = session('impersonation_token');

        if ($token) {
            ImpersonationLog::where('token', $token)
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);
        }

        Auth::logout();
        session()->forget(['impersonation_token', 'impersonating_staff_id']);

        $centralDomain = config('tenancy.central_domains')[0] ?? 'localhost';

        return redirect()->away('http://'.$centralDomain.'/admin/tenants');
    }
}
