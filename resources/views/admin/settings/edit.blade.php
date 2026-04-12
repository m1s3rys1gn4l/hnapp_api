<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Settings</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; margin: 0; }
        .container { max-width: 760px; margin: 0 auto; padding: 20px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 20px; }
        h2 { margin-top: 0; font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; }
        label { display: block; margin: 12px 0 6px; font-size: 14px; }
        input, select { width: 100%; box-sizing: border-box; padding: 9px 10px; border: 1px solid #cbd5e1; border-radius: 8px; }
        .actions { display: flex; gap: 10px; margin-top: 18px; }
        button, .btn { padding: 8px 12px; border: 0; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-neutral { background: #e2e8f0; color: #111827; }
        .status { margin-bottom: 10px; padding: 10px; border-radius: 8px; background: #ecfeff; color: #155e75; border: 1px solid #a5f3fc; }
        .errors { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; margin-bottom: 12px; }
        .help-text { font-size: 12px; color: #64748b; margin-top: 4px; }
    </style>
</head>
<body>
<div class="container">
    <div class="topbar">
        <h1>Admin Settings</h1>
        <a class="btn btn-neutral" href="{{ route('admin.users.index') }}">Back to Users</a>
    </div>

    <div class="card">
        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <h2>🔐 Admin Credentials</h2>
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')

                <label for="current_password">Current password</label>
                <input id="current_password" type="password" name="current_password" required>

                <label for="email">New admin email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $currentEmail) }}" required>

                <label for="new_password">New password</label>
                <input id="new_password" type="password" name="new_password" required>

                <label for="new_password_confirmation">Confirm new password</label>
                <input id="new_password_confirmation" type="password" name="new_password_confirmation" required>

                <div class="actions">
                    <button class="btn btn-primary" type="submit">Update Credentials</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
