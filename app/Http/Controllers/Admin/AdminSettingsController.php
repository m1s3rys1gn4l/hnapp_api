<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminSettingsController extends Controller
{
    public function edit(Request $request)
    {
        $currentEmail = $request->session()->get('admin_email') ?? env('ADMIN_EMAIL', 'admin@hisabnikash.local');

        return view('admin.settings.edit', [
            'currentEmail' => $currentEmail,
            'mailMailer' => env('MAIL_MAILER', 'log'),
            'mailHost' => env('MAIL_HOST', '127.0.0.1'),
            'mailPort' => env('MAIL_PORT', '2525'),
            'mailUsername' => env('MAIL_USERNAME', ''),
            'mailEncryption' => env('MAIL_ENCRYPTION', 'tls'),
            'mailFromAddress' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'mailFromName' => env('MAIL_FROM_NAME', config('app.name')),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'email' => ['required', 'email'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $sessionAdminEmail = $request->session()->get('admin_email');
        $storedAdmin = AdminCredential::query()->where('email', $sessionAdminEmail)->first();

        $isCurrentPasswordValid = false;

        if ($storedAdmin) {
            $isCurrentPasswordValid = Hash::check($validated['current_password'], $storedAdmin->password);
        } else {
            $envEmail = env('ADMIN_EMAIL', 'admin@hisabnikash.local');
            $envPassword = env('ADMIN_PASSWORD', 'change-me');

            $isCurrentPasswordValid = hash_equals($envEmail, (string) $sessionAdminEmail)
                && hash_equals($envPassword, $validated['current_password']);
        }

        if (!$isCurrentPasswordValid) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        if ($storedAdmin) {
            $storedAdmin->update([
                'email' => $validated['email'],
                'password' => Hash::make($validated['new_password']),
            ]);
        } else {
            AdminCredential::query()->updateOrCreate(
                ['email' => $sessionAdminEmail],
                [
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['new_password']),
                ]
            );
        }

        $request->session()->put('admin_email', $validated['email']);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Admin credentials updated. Future logins will use the new credentials.');
    }

    public function updateEmailSettings(Request $request)
    {
        $validated = $request->validate([
            'mail_mailer' => ['required', 'string', 'in:smtp,sendmail,mailgun,ses,postmark,log'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'string', 'in:tls,ssl,null'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
        ]);

        try {
            $envUpdates = [
                'MAIL_MAILER' => $validated['mail_mailer'],
                'MAIL_HOST' => $validated['mail_host'] ?? '127.0.0.1',
                'MAIL_PORT' => $validated['mail_port'] ?? '2525',
                'MAIL_USERNAME' => $validated['mail_username'] ?? 'null',
                'MAIL_ENCRYPTION' => $validated['mail_encryption'] === 'null' ? 'null' : ($validated['mail_encryption'] ?? 'null'),
                'MAIL_FROM_ADDRESS' => $validated['mail_from_address'],
                'MAIL_FROM_NAME' => '"' . $validated['mail_from_name'] . '"',
            ];

            // Only update password if provided
            if (!empty($validated['mail_password'])) {
                $envUpdates['MAIL_PASSWORD'] = $validated['mail_password'];
            }

            $this->updateEnv($envUpdates);
        } catch (\Exception $e) {
            return back()->withErrors([
                'email_settings' => 'Failed to update email settings: ' . $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Email provider settings updated successfully.');
    }

    private function updateEnv(array $data): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            throw new \RuntimeException('.env file not found');
        }

        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $escapedKey = preg_quote($key, '/');
            $pattern = "/^{$escapedKey}=.*/m";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $envContent);
    }
}
