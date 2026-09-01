<x-emails.layouts.default 
    :brandName="config('app.name', 'TenantForge')"
    :primaryColor="$primaryColor ?? '#4f46e5'"
    :logoUrl="$logoUrl ?? null"
    :subject="'Reset Your Password'"
    footerText="TenantForge Security Alerts"
>
    <h1>Reset Your Password</h1>
    
    <p>Hello {{ $userName ?? 'Administrator' }},</p>
    
    <p>You recently requested to reset the password for your account. Click the button below to proceed with resetting your password:</p>
    
    <div class="button-wrapper">
        <a href="{{ $resetUrl ?? '#' }}" class="action-button" target="_blank">Reset Password</a>
    </div>

    <div class="meta-box">
        <strong>Requested at:</strong> {{ now()->toFormattedDateString() }}<br>
        <strong>Security Notice:</strong> This link will expire in 60 minutes.
    </div>

    <p style="font-size: 13px; color: #64748b; margin-bottom: 0;">
        If you did not request a password reset, no further action is required and your account remains safe.
    </p>
</x-emails.layouts.default>
