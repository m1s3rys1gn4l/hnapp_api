<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login · Hisab Nikash</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
        <div class="text-center mb-6">
            <div class="text-3xl mb-2">📒</div>
            <h1 class="text-xl font-bold text-slate-900">Hisab Nikash Admin</h1>
            <p class="text-sm text-slate-500 mt-1">Sign in to manage the platform</p>
        </div>

        <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input id="password" type="password" name="password" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            @if ($errors->any())
                <div class="text-sm text-red-600">{{ $errors->first() }}</div>
            @endif

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg px-4 py-2.5 text-sm transition">
                Sign In
            </button>
        </form>

        <p class="text-xs text-slate-400 text-center mt-6">
            Configure fallback credentials via <code class="bg-slate-100 px-1 rounded">ADMIN_EMAIL</code> / <code class="bg-slate-100 px-1 rounded">ADMIN_PASSWORD</code> in .env
        </p>
    </div>
</body>
</html>
