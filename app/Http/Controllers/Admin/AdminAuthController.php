<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (session('admin_authenticated') === true) {
            return redirect()->route('admin.users.index');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $storedAdmin = AdminCredential::query()
            ->where('email', $credentials['email'])
            ->first();

        if ($storedAdmin) {
            $isValid = Hash::check($credentials['password'], $storedAdmin->password);
            $authenticatedEmail = $storedAdmin->email;
        } else {
            $adminEmail = env('ADMIN_EMAIL', 'admin@hisabnikash.local');
            $adminPassword = env('ADMIN_PASSWORD', 'change-me');
            $isValid = hash_equals($adminEmail, $credentials['email'])
                && hash_equals($adminPassword, $credentials['password']);
            $authenticatedEmail = $adminEmail;
        }

        if (!$isValid) {
            return back()->withErrors([
                'email' => 'Invalid admin credentials.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->session()->put('admin_authenticated', true);
        $request->session()->put('admin_email', $authenticatedEmail);

        return redirect()->route('admin.users.index');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['admin_authenticated', 'admin_email']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
