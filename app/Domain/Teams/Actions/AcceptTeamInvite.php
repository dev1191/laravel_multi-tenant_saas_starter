<?php

namespace App\Domain\Teams\Actions;

use App\Domain\Teams\Events\TeamInviteAccepted;
use App\Domain\Teams\Models\TeamInvite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\Concerns\AsAction;

class AcceptTeamInvite
{
    use AsAction;

    public function handle(TeamInvite $invite, User $user): void
    {
        $invite->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $invite->team->members()->syncWithoutDetaching([
            $user->id => ['joined_at' => now()],
        ]);

        $user->assignTeamRole($invite->role, $invite->team);

        event(new TeamInviteAccepted($invite->team, $user, $invite));
    }

    public function asController(Request $request, string $token)
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

        $this->handle($invite, $user);
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', "Welcome to {$invite->team->name}!");
    }
}
