<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Events\Dispatcher;
use Spatie\Activitylog\Models\Activity;

class AuthEventSubscriber
{
    public function handleUserLogin(Login $event): void
    {
        try {
            if ($event->user && function_exists('activity')) {
                activity('auth')
                    ->causedBy($event->user)
                    ->event('login')
                    ->log('User logged in');
            }
        } catch (\Throwable) {
            // Silently ignore during migrations/uninitialized contexts
        }
    }

    public function handleUserFailedLogin(Failed $event): void
    {
        try {
            if (function_exists('activity')) {
                $email = $event->credentials['email'] ?? 'unknown';
                activity('auth')
                    ->event('failed_login')
                    ->withProperties(['email' => $email, 'ip' => request()->ip()])
                    ->log("Failed login attempt for {$email}");
            }
        } catch (\Throwable) {
            // Silently ignore
        }
    }

    public function handleUserLogout(Logout $event): void
    {
        try {
            if ($event->user && function_exists('activity')) {
                activity('auth')
                    ->causedBy($event->user)
                    ->event('logout')
                    ->log('User logged out');
            }
        } catch (\Throwable) {
            // Silently ignore
        }
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        try {
            if ($event->user && function_exists('activity')) {
                activity('auth')
                    ->causedBy($event->user)
                    ->event('password_reset')
                    ->log('Password was reset');
            }
        } catch (\Throwable) {
            // Silently ignore
        }
    }

    public function handleEmailVerified(Verified $event): void
    {
        try {
            if ($event->user && function_exists('activity')) {
                activity('auth')
                    ->causedBy($event->user)
                    ->event('email_verified')
                    ->log('Email verified');
            }
        } catch (\Throwable) {
            // Silently ignore
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleUserLogin',
            Failed::class => 'handleUserFailedLogin',
            Logout::class => 'handleUserLogout',
            PasswordReset::class => 'handlePasswordReset',
            Verified::class => 'handleEmailVerified',
        ];
    }
}
