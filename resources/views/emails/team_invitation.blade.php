<x-emails.layouts.default 
    :brandName="$teamName"
    :primaryColor="$primaryColor ?? '#4f46e5'"
    :logoUrl="$logoUrl ?? null"
    :subject="'Invitation to join ' . $teamName"
    footerText="You received this email because you were invited to a workspace."
>
    <h1>You're invited to join {{ $teamName }}</h1>
    
    <p><strong>{{ $inviterName }}</strong> has invited you to join the <strong>{{ $teamName }}</strong> workspace as a <strong>{{ $role }}</strong>.</p>
    
    <p>Click the button below to accept your invitation and access the workspace dashboard:</p>
    
    <div class="button-wrapper">
        <a href="{{ $inviteUrl }}" class="action-button" target="_blank">Accept Invitation</a>
    </div>

    <div class="meta-box">
        <strong>Role:</strong> {{ $role }}<br>
        <strong>Expires:</strong> {{ $expiresAt }}
    </div>

    <p style="font-size: 13px; color: #64748b; margin-bottom: 0;">
        If you were not expecting this invitation, you can safely ignore this email.
    </p>
</x-emails.layouts.default>
