<?php

declare(strict_types=1);

namespace App\Domain\Teams\Jobs;

use App\Domain\Teams\Mail\TeamInvitationMail;
use App\Domain\Teams\Models\TeamInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTeamInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public TeamInvite $invite,
    ) {
    }

    public function handle(): void
    {
        Mail::to($this->invite->email)->send(new TeamInvitationMail($this->invite));
    }
}
