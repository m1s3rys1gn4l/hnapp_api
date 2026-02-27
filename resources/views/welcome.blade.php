<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Hisab Nikash API') }}</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f7f9fc;
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --ok-bg: #ecfdf5;
            --ok-text: #065f46;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .container {
            max-width: 980px;
            margin: 0 auto;
            padding: 24px 16px 40px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .brand {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .badge {
            background: var(--ok-bg);
            color: var(--ok-text);
            border: 1px solid #bbf7d0;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .hero {
            background: linear-gradient(135deg, #ffffff 0%, #eef5ff 100%);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .hero h2 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .hero p {
            margin: 0;
            color: var(--muted);
            line-height: 1.55;
        }

        .grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            margin-top: 16px;
        }

        .section-title {
            font-size: 20px;
            margin: 22px 0 10px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
        }

        .card h3 {
            margin: 0 0 8px;
            font-size: 16px;
        }

        .card p {
            margin: 0 0 14px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 6px;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-outline {
            background: #fff;
            color: var(--text);
            border-color: var(--border);
        }

        .meta {
            margin-top: 16px;
            color: var(--muted);
            font-size: 12px;
        }

        .pill {
            display: inline-block;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 4px 8px;
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .feature-list {
            margin: 0;
            padding-left: 18px;
            color: var(--muted);
            line-height: 1.6;
            font-size: 14px;
        }

        .contact-item {
            font-size: 14px;
            margin: 8px 0;
            color: var(--muted);
        }

        .contact-item strong {
            color: var(--text);
        }

        .muted-small {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
<div class="container">
    @php
        $plans = \App\Models\User::PLAN_DEFINITIONS;
        $supportPhone = '01842566315';
        $supportWhatsApp = '+8801842566315';
        $supportEmail = 'support@hisabnikash.app';
    @endphp

    <div class="header">
        <h1 class="brand">{{ config('app.name', 'Hisab Nikash API') }}</h1>
        <span class="badge">Service Online</span>
    </div>

    <section class="hero">
        <h2>Welcome to Hisab Nikash Backend</h2>
        <p>
            This server powers authentication, sync, subscription packages, and admin operations for the app.
            Use the quick actions below to jump to the admin panel and API health endpoint.
        </p>
        <div class="actions">
            <a class="btn btn-primary" href="{{ url('/admin/login') }}">Open Admin Panel</a>
            <a class="btn btn-outline" href="{{ url('/api') }}">Check API Health</a>
        </div>
    </section>

    <h2 class="section-title">Package List</h2>
    <section class="grid">
        @foreach ($plans as $key => $plan)
            <article class="card">
                <span class="pill">{{ strtoupper($key) }}</span>
                <h3>{{ $plan['label'] }}</h3>
                <p class="muted-small">
                    Books: <strong>{{ $plan['book_limit'] ?? 'Unlimited' }}</strong><br>
                    Customers: <strong>{{ $plan['customer_limit'] ?? 'Unlimited' }}</strong><br>
                    Ads: <strong>{{ ($plan['show_ads'] ?? true) ? 'Enabled' : 'No Ads' }}</strong><br>
                    Monthly: <strong>৳{{ $plan['monthly_price_bdt'] ?? 0 }}</strong><br>
                    Yearly: <strong>৳{{ $plan['yearly_price_bdt'] ?? 0 }}</strong>
                </p>
            </article>
        @endforeach
    </section>

    <h2 class="section-title">Features & Functionalities</h2>
    <section class="grid">
        <article class="card">
            <h3>Core App Features</h3>
            <ul class="feature-list">
                <li>Book and customer management</li>
                <li>Income/expense transaction tracking</li>
                <li>Firebase authentication</li>
                <li>Offline-first data sync support</li>
                <li>Package-aware limits and ad visibility</li>
            </ul>
        </article>
        <article class="card">
            <h3>Admin & Backend Features</h3>
            <ul class="feature-list">
                <li>User activation and account control</li>
                <li>Manual package assignment with validity</li>
                <li>Automatic subscription expiry to free plan</li>
                <li>Password reset and security workflows</li>
                <li>Email provider configuration panel</li>
            </ul>
        </article>
    </section>

    <h2 class="section-title">Contact Details</h2>
    <section class="grid">
        <article class="card">
            <h3>Support Contact</h3>
            <p class="contact-item"><strong>Phone:</strong> <a href="tel:{{ $supportPhone }}">{{ $supportPhone }}</a></p>
            <p class="contact-item"><strong>WhatsApp:</strong> <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsApp) }}" target="_blank">{{ $supportWhatsApp }}</a></p>
            <p class="contact-item"><strong>Email:</strong> <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a></p>
        </article>
        <article class="card">
            <h3>Payment Numbers</h3>
            <p class="contact-item"><strong>bKash:</strong> {{ $supportPhone }}</p>
            <p class="contact-item"><strong>Nagad:</strong> {{ $supportPhone }}</p>
            <p class="muted-small">After payment, share reference with support/admin for package activation.</p>
        </article>
    </section>

    <p class="meta">
        Environment: {{ app()->environment() }} • {{ now()->format('Y-m-d H:i') }}
    </p>
</div>
</body>
</html>
