<x-emails.layouts.default 
    :brandName="config('app.name', 'TenantForge')"
    :primaryColor="$primaryColor ?? '#4f46e5'"
    :logoUrl="$logoUrl ?? null"
    :subject="'Payment Receipt - Invoice #' . ($invoiceNumber ?? '1001')"
    footerText="TenantForge Platform Billing"
>
    <h1>Payment Receipt</h1>
    
    <p>Thank you for your payment! We've successfully processed your subscription payment for <strong>{{ $tenantName }}</strong>.</p>
    
    <div class="meta-box">
        <strong>Invoice Number:</strong> #{{ $invoiceNumber ?? 'INV-2026-0841' }}<br>
        <strong>Amount Paid:</strong> {{ $amountPaid ?? '$49.00 USD' }}<br>
        <strong>Plan:</strong> {{ $planName ?? 'Scale Annual' }}<br>
        <strong>Billing Period:</strong> {{ $billingPeriod ?? now()->format('M Y') . ' - ' . now()->addMonth()->format('M Y') }}
    </div>

    <div class="button-wrapper">
        <a href="{{ $invoiceUrl ?? '#' }}" class="action-button" target="_blank">Download PDF Invoice</a>
    </div>

    <p style="font-size: 13px; color: #64748b; margin-bottom: 0;">
        You can manage your subscription and payment methods at any time from your platform billing settings.
    </p>
</x-emails.layouts.default>
