<x-emails.layouts.default 
    :brandName="config('app.name', 'TenantForge')"
    :primaryColor="$primaryColor ?? '#4f46e5'"
    :logoUrl="$logoUrl ?? null"
    :subject="'Your workspace is ready!'"
    footerText="TenantForge Platform Notifications"
>
    <h1>Welcome to {{ config('app.name', 'TenantForge') }}, {{ $tenantName }}!</h1>
    
    <p>Great news! Your workspace has been fully provisioned and your database and custom domain are now online and ready to use.</p>
    
    <div class="meta-box">
        <strong>Workspace Name:</strong> {{ $tenantName }}<br>
        <strong>Access Domain:</strong> <a href="{{ $domainUrl }}" target="_blank">{{ $domainUrl }}</a><br>
        <strong>Plan:</strong> {{ $planName ?? 'Pro Plan' }}
    </div>

    <p>Click the button below to sign in and start configuring your workspace:</p>
    
    <div class="button-wrapper">
        <a href="{{ $domainUrl }}" class="action-button" target="_blank">Access Your Workspace</a>
    </div>

    <p style="font-size: 13px; color: #64748b; margin-bottom: 0;">
        If you need help setting up your team or custom domain, check our documentation or contact our support team.
    </p>
</x-emails.layouts.default>
