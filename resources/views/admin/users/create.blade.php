<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create User</title>
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
        <h1>Create User</h1>

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <label for="name">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}">

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>

            <label for="phone">Phone</label>
            <input id="phone" type="text" name="phone" value="{{ old('phone') }}">

            <div class="row">
                <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <label for="is_active" style="margin:0;">User active</label>
            </div>

            <div class="actions">
                <button class="btn btn-primary" type="submit">Create User</button>
                <a class="btn btn-neutral" href="{{ route('admin.users.index') }}">Back</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
