<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? config('app.name', 'TenantForge') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 32px 16px;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            max-width: 580px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }
        .header {
            padding: 28px 32px 20px;
            border-bottom: 1px solid #f1f5f9;
            text-align: left;
        }
        .logo-img {
            max-height: 42px;
            max-width: 180px;
            display: block;
        }
        .brand-name {
            font-size: 20px;
            font-weight: 700;
            color: {{ $primaryColor ?? '#4f46e5' }};
            text-decoration: none;
            letter-spacing: -0.02em;
        }
        .content {
            padding: 32px;
        }
        h1 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 16px;
            line-height: 1.3;
        }
        p {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin-top: 0;
            margin-bottom: 16px;
        }
        .button-wrapper {
            text-align: center;
            margin: 28px 0;
        }
        .action-button {
            display: inline-block;
            background-color: {{ $primaryColor ?? '#4f46e5' }};
            color: #ffffff !important;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            font-size: 14px;
            color: #475569;
        }
        .footer {
            padding: 24px 32px;
            background-color: #f8fafc;
            border-top: 1px solid #f1f5f9;
            font-size: 12px;
            color: #94a3b8;
            text-align: center;
            line-height: 1.5;
        }
        .footer a {
            color: #64748b;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            @if(!empty($logoUrl))
                <img src="{{ $logoUrl }}" alt="{{ $brandName ?? config('app.name') }}" class="logo-img">
            @else
                <div class="brand-name">{{ $brandName ?? config('app.name', 'TenantForge') }}</div>
            @endif
        </div>

        <div class="content">
            {{ $slot }}
        </div>

        <div class="footer">
            <p style="margin: 0;">&copy; {{ date('Y') }} {{ $brandName ?? config('app.name', 'TenantForge') }}. All rights reserved.</p>
            @if(!empty($footerText))
                <p style="margin: 6px 0 0; font-size: 11px;">{{ $footerText }}</p>
            @endif
        </div>
    </div>
</body>
</html>
