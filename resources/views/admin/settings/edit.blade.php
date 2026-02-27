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
            <h2>📧 Email Provider Settings</h2>
            <form method="POST" action="{{ route('admin.settings.update-email') }}">
                @csrf
                @method('PUT')

                <label for="mail_mailer">Mail Driver</label>
                <select id="mail_mailer" name="mail_mailer" required>
                    <option value="smtp" {{ old('mail_mailer', $mailMailer) === 'smtp' ? 'selected' : '' }}>SMTP</option>
                    <option value="sendmail" {{ old('mail_mailer', $mailMailer) === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                    <option value="mailgun" {{ old('mail_mailer', $mailMailer) === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                    <option value="ses" {{ old('mail_mailer', $mailMailer) === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                    <option value="postmark" {{ old('mail_mailer', $mailMailer) === 'postmark' ? 'selected' : '' }}>Postmark</option>
                    <option value="log" {{ old('mail_mailer', $mailMailer) === 'log' ? 'selected' : '' }}>Log (Development)</option>
                </select>
                <div class="help-text">Choose SMTP for most providers like Gmail, Outlook, etc.</div>

                <label for="mail_host">SMTP Host</label>
                <input id="mail_host" type="text" name="mail_host" value="{{ old('mail_host', $mailHost) }}" placeholder="smtp.gmail.com">
                <div class="help-text">Gmail: smtp.gmail.com, Outlook: smtp-mail.outlook.com</div>

                <label for="mail_port">SMTP Port</label>
                <input id="mail_port" type="number" name="mail_port" value="{{ old('mail_port', $mailPort) }}" placeholder="587">
                <div class="help-text">TLS: 587, SSL: 465</div>

                <label for="mail_username">SMTP Username</label>
                <input id="mail_username" type="text" name="mail_username" value="{{ old('mail_username', $mailUsername) }}" placeholder="your-email@gmail.com">

                <label for="mail_password">SMTP Password</label>
                <input id="mail_password" type="password" name="mail_password" value="{{ old('mail_password', '') }}" placeholder="Leave blank to keep current">
                <div class="help-text">For Gmail, use App Password (not your regular password)</div>

                <label for="mail_encryption">Encryption</label>
                <select id="mail_encryption" name="mail_encryption">
                    <option value="tls" {{ old('mail_encryption', $mailEncryption) === 'tls' ? 'selected' : '' }}>TLS (Recommended)</option>
                    <option value="ssl" {{ old('mail_encryption', $mailEncryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="null" {{ old('mail_encryption', $mailEncryption) === 'null' ? 'selected' : '' }}>None</option>
                </select>

                <label for="mail_from_address">From Email Address</label>
                <input id="mail_from_address" type="email" name="mail_from_address" value="{{ old('mail_from_address', $mailFromAddress) }}" required>

                <label for="mail_from_name">From Name</label>
                <input id="mail_from_name" type="text" name="mail_from_name" value="{{ old('mail_from_name', $mailFromName) }}" required>

                <div class="actions">
                    <button class="btn btn-primary" type="submit">Save Email Settings</button>
                </div>
            </form>
        </div>

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
</body>
</html>
