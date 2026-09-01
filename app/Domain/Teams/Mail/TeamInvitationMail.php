<?php

declare(strict_types=1);

namespace App\Domain\Teams\Mail;

use App\Domain\Teams\Models\TeamInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public TeamInvite $invite,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been invited to join {$this->invite->team->name}",
        );
    }

    public function content(): Content
    {
        $settings = null;
        try {
            $settings = app(\App\Domain\Settings\Settings\SiteSettings::class);
        } catch (\Throwable) {
        }

        return new Content(
            view: 'emails.team_invitation',
            with: [
                'inviteUrl' => route('teams.invites.accept', ['token' => $this->invite->token]),
                'teamName' => $settings?->site_name ?? $this->invite->team->name,
                'inviterName' => $this->invite->inviter?->name ?? 'A team admin',
                'role' => ucfirst($this->invite->role),
                'expiresAt' => $this->invite->expires_at?->toFormattedDateString() ?? '7 days',
                'primaryColor' => $settings?->primary_color ?? '#4f46e5',
                'logoUrl' => $settings?->logo_path ? asset($settings->logo_path) : null,
            ],
        );
    }
}
