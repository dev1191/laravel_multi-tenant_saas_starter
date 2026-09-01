<?php

namespace App\Domain\TenantAdmin\Actions;

use App\Domain\TenantAdmin\Models\ImpersonationLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class StartImpersonation
{
    use AsAction;

    public function handle(string $token): void
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
    }

    public function asController(Request $request, string $token): RedirectResponse
    {
        $this->handle($token);

        return redirect()->route('dashboard')->with('status', 'Impersonation session started.');
    }
}
