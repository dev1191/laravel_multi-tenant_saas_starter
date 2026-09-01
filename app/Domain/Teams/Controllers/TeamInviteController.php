<?php

namespace App\Domain\Teams\Controllers;

use App\Domain\Teams\Models\TeamInvite;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class TeamInviteController extends Controller
{
    public function show(string $token): Response
    {
        $invite = TeamInvite::with(['team', 'inviter'])
            ->where('token', $token)
            ->first();

        if (! $invite || ! $invite->isPending()) {
            return Inertia::render('Teams/InviteInvalid', [
                'reason' => 'This team invitation link is invalid or has expired.',
            ]);
        }

        $existingUser = User::where('email', $invite->email)->first();

        return Inertia::render('Teams/AcceptInvite', [
            'invite' => [
                'token' => $invite->token,
                'email' => $invite->email,
                'role' => $invite->role,
                'team_name' => $invite->team->name,
                'invited_by' => $invite->inviter?->name ?? 'A team administrator',
                'is_existing_user' => (bool) $existingUser,
            ],
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invite = TeamInvite::with('team')
            ->where('token', $token)
            ->firstOrFail();

        if (! $invite->isPending()) {
            return redirect()->route('login')->withErrors(['email' => 'This invitation has expired or already been accepted.']);
        }

        $existingUser = User::where('email', $invite->email)->first();

        if ($existingUser) {
            $request->validate([
                'password' => ['required', 'string'],
            ]);

            if (! Hash::check($request->password, $existingUser->password)) {
                return back()->withErrors(['password' => 'The provided password does not match our records.']);
            }

            $user = $existingUser;
        } else {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $invite->email,
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
            ]);
        }

        $invite->accept($user);
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', "Welcome to {$invite->team->name}!");
    }
}
