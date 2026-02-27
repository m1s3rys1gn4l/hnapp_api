<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit User</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; margin: 0; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; }
        h1 { margin-top: 0; }
        label { display: block; margin: 12px 0 6px; font-size: 14px; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; box-sizing: border-box; padding: 9px 10px; border: 1px solid #cbd5e1; border-radius: 8px; }
        .row { display: flex; gap: 12px; align-items: center; margin-top: 12px; }
        .actions { display: flex; gap: 10px; margin-top: 18px; }
        button, .btn { padding: 8px 12px; border: 0; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-neutral { background: #e2e8f0; color: #111827; }
        .errors { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; margin-bottom: 12px; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h1>Edit User #{{ $user->id }}</h1>

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <label for="name">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}">

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>

            <label for="phone">Phone</label>
            <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}">

            <label for="firebase_uid">Firebase UID</label>
            <input id="firebase_uid" type="text" name="firebase_uid" value="{{ old('firebase_uid', $user->firebase_uid) }}">

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #e2e8f0;">

            <h3 style="margin-bottom: 8px;">Package Assignment</h3>
            <p style="color: #64748b; font-size: 13px; margin-top: 0;">Set package manually from admin panel</p>

            <label for="subscription_plan">Package</label>
            <select id="subscription_plan" name="subscription_plan" style="width: 100%; box-sizing: border-box; padding: 9px 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                @foreach ($planDefinitions as $planKey => $plan)
                    <option value="{{ $planKey }}" {{ old('subscription_plan', $user->subscription_plan ?? 'free') === $planKey ? 'selected' : '' }}>
                        {{ $plan['label'] }}
                    </option>
                @endforeach
            </select>

            <label for="subscription_cycle">Billing Cycle (for paid plans)</label>
            <select id="subscription_cycle" name="subscription_cycle" style="width: 100%; box-sizing: border-box; padding: 9px 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                <option value="monthly" {{ old('subscription_cycle', $user->subscription_cycle) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="yearly" {{ old('subscription_cycle', $user->subscription_cycle ?? 'yearly') === 'yearly' ? 'selected' : '' }}>Yearly</option>
            </select>

                <label for="validity_days">Validity Period (Days)</label>
                <input id="validity_days" type="number" name="validity_days" min="1" max="3650" value="{{ old('validity_days', '') }}" placeholder="Leave blank to use default (30 days for monthly, 365 for yearly)" style="width: 100%; box-sizing: border-box; padding: 9px 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                <p style="color: #64748b; font-size: 12px; margin-top: 4px;">
                    Set custom validity period in days. Package will automatically expire and revert to Free plan after this period.
                </p>

            <p style="color: #64748b; font-size: 12px; margin-top: 8px;">
                    <strong>Current Status:</strong><br>
                    Books {{ $user->book_limit ?? 'Unlimited' }}, Customers {{ $user->customer_limit ?? 'Unlimited' }}, Ads {{ $user->show_ads ? 'On' : 'Off' }}<br>
                    @if ($user->subscription_expires_at)
                        <span style="color: #d97706;">Expires: {{ $user->subscription_expires_at->format('Y-m-d H:i') }} ({{ $user->subscription_expires_at->diffForHumans() }})</span>
                    @else
                        No expiry (Free or lifetime plan)
                    @endif
            </p>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #e2e8f0;">

            <h3 style="margin-bottom: 8px;">Change Password (Optional)</h3>
            <p style="color: #64748b; font-size: 13px; margin-top: 0;">Leave blank to keep current password</p>

            <label for="password">New Password</label>
            <input id="password" type="password" name="password" placeholder="Leave blank to keep current">

            <label for="password_confirmation">Confirm New Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Leave blank to keep current">

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #e2e8f0;">

            <div class="row">
                <input id="is_phone_verified" type="checkbox" name="is_phone_verified" value="1" {{ old('is_phone_verified', $user->is_phone_verified) ? 'checked' : '' }}>
                <label for="is_phone_verified" style="margin:0;">Phone verified</label>
            </div>

            <div class="row">
                <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                <label for="is_active" style="margin:0;">User active</label>
            </div>

            <div class="actions">
                <button class="btn btn-primary" type="submit">Save Changes</button>
                <a class="btn btn-neutral" href="{{ route('admin.users.index') }}">Back</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
