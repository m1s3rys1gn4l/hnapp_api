@extends('admin.layouts.app')

@section('title', 'Settings')
@section('page-title', 'Admin Settings')

@section('content')
    <div class="space-y-6 max-w-2xl">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="font-semibold text-slate-900 mb-4 pb-3 border-b border-slate-100">🔐 Admin Credentials</h2>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">Current password</label>
                    <input id="current_password" type="password" name="current_password" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">New admin email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $currentEmail) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-medium text-slate-700 mb-1">New password</label>
                    <input id="new_password" type="password" name="new_password" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm new password</label>
                    <input id="new_password_confirmation" type="password" name="new_password_confirmation" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg px-4 py-2" type="submit">Update Credentials</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="font-semibold text-slate-900 mb-4 pb-3 border-b border-slate-100">📱 SMS Gateway (REVE SMS)</h2>
            <form method="POST" action="{{ route('admin.settings.update-sms') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="sms_api_key" class="block text-sm font-medium text-slate-700 mb-1">API Key</label>
                    <input id="sms_api_key" type="text" name="sms_api_key" value="{{ old('sms_api_key', $smsApiKey) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="sms_secret_key" class="block text-sm font-medium text-slate-700 mb-1">Secret Key</label>
                    <input id="sms_secret_key" type="text" name="sms_secret_key" value="{{ old('sms_secret_key', $smsSecretKey) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="sms_sender_id" class="block text-sm font-medium text-slate-700 mb-1">Sender ID (Caller ID)</label>
                    <input id="sms_sender_id" type="text" name="sms_sender_id" value="{{ old('sms_sender_id', $smsSenderId) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-slate-400 mt-1">The approved sender/mask name shown to recipients.</p>
                </div>

                <div>
                    <label for="sms_base_url" class="block text-sm font-medium text-slate-700 mb-1">Gateway Base URL</label>
                    <input id="sms_base_url" type="url" name="sms_base_url" value="{{ old('sms_base_url', $smsBaseUrl) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-slate-400 mt-1">Default: https://smpp.revesms.com:7790</p>
                </div>

                <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg px-4 py-2" type="submit">Save SMS Settings</button>
            </form>

            <form method="POST" action="{{ route('admin.settings.test-sms') }}" class="mt-5 pt-4 border-t border-slate-100 flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label for="test_phone" class="block text-sm font-medium text-slate-700 mb-1">Send a test SMS</label>
                    <input id="test_phone" type="text" name="test_phone" placeholder="e.g. 8801XXXXXXXXX" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-lg px-4 py-2" type="submit">Send Test SMS</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="font-semibold text-slate-900 mb-4 pb-3 border-b border-slate-100">✉️ Email Provider</h2>
            <form method="POST" action="{{ route('admin.settings.update-email') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="mail_mailer" class="block text-sm font-medium text-slate-700 mb-1">Mailer</label>
                    <select id="mail_mailer" name="mail_mailer"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach (['smtp', 'sendmail', 'mailgun', 'ses', 'postmark', 'log'] as $mailer)
                            <option value="{{ $mailer }}" {{ old('mail_mailer', $mailMailer) === $mailer ? 'selected' : '' }}>{{ ucfirst($mailer) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="mail_host" class="block text-sm font-medium text-slate-700 mb-1">SMTP Host</label>
                        <input id="mail_host" type="text" name="mail_host" value="{{ old('mail_host', $mailHost) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="mail_port" class="block text-sm font-medium text-slate-700 mb-1">SMTP Port</label>
                        <input id="mail_port" type="number" name="mail_port" value="{{ old('mail_port', $mailPort) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="mail_username" class="block text-sm font-medium text-slate-700 mb-1">SMTP Username</label>
                    <input id="mail_username" type="text" name="mail_username" value="{{ old('mail_username', $mailUsername) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="mail_password" class="block text-sm font-medium text-slate-700 mb-1">SMTP Password</label>
                    <input id="mail_password" type="password" name="mail_password" placeholder="Leave blank to keep current"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="mail_encryption" class="block text-sm font-medium text-slate-700 mb-1">Encryption</label>
                    <select id="mail_encryption" name="mail_encryption"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach (['tls' => 'TLS', 'ssl' => 'SSL', 'null' => 'None'] as $value => $label)
                            <option value="{{ $value }}" {{ old('mail_encryption', $mailEncryption) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="mail_from_address" class="block text-sm font-medium text-slate-700 mb-1">From Address</label>
                        <input id="mail_from_address" type="email" name="mail_from_address" value="{{ old('mail_from_address', $mailFromAddress) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="mail_from_name" class="block text-sm font-medium text-slate-700 mb-1">From Name</label>
                        <input id="mail_from_name" type="text" name="mail_from_name" value="{{ old('mail_from_name', $mailFromName) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg px-4 py-2" type="submit">Save Email Settings</button>
            </form>
        </div>
    </div>
@endsection
