<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fb; margin: 0; }
        .wrap { min-height: 100vh; display: grid; place-items: center; }
        .card { width: 100%; max-width: 420px; background: #fff; border: 1px solid #e3e8ef; border-radius: 12px; padding: 24px; }
        h1 { margin: 0 0 8px; font-size: 22px; }
        p { margin: 0 0 20px; color: #6b7280; font-size: 14px; }
        label { display: block; margin: 10px 0 6px; font-size: 14px; }
        input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; box-sizing: border-box; }
        button { margin-top: 16px; width: 100%; padding: 10px 12px; border: 0; border-radius: 8px; background: #2563eb; color: #fff; cursor: pointer; }
        .error { color: #b91c1c; font-size: 13px; margin-top: 8px; }
        .hint { margin-top: 16px; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Admin Dashboard</h1>
        <p>Sign in to manage users.</p>

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <button type="submit">Sign In</button>
        </form>

        <div class="hint">
            Configure credentials in <strong>.env</strong> using <strong>ADMIN_EMAIL</strong> and <strong>ADMIN_PASSWORD</strong>.
        </div>
    </div>
</div>
</body>
</html>
