<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $onboardingTenantId = session()->pull('onboarding_tenant_id');

        if ($onboardingTenantId) {
            $redirectUrl = route('onboarding.show', ['tenant' => $onboardingTenantId]);

            return $request->wantsJson()
                ? response()->json(['redirect' => $redirectUrl])
                : redirect()->to($redirectUrl);
        }

        $home = route('dashboard');

        return $request->wantsJson()
            ? response()->json(['redirect' => $home])
            : redirect()->intended($home);
    }
}
