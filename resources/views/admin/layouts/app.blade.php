<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · Hisab Nikash</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef4ff', 100: '#dbe6fe', 500: '#2563eb', 600: '#1d4ed8', 700: '#1e40af',
                        },
                    },
                },
            },
        };
    </script>
    <style>[x-cloak]{display:none}</style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="hidden md:flex md:w-64 md:flex-col bg-slate-900 text-slate-200 shrink-0">
        <div class="px-5 py-5 border-b border-slate-800">
            <div class="text-lg font-bold text-white">📒 Hisab Nikash</div>
            <div class="text-xs text-slate-400">Admin Panel</div>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
            @php($navItem = fn($route, $label, $icon) =>
                '<a href="' . route($route) . '" class="flex items-center gap-3 px-3 py-2 rounded-lg ' .
                (request()->routeIs($route . '*') ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white') .
                '">' . $icon . ' <span>' . $label . '</span></a>'
            )
            {!! $navItem('admin.dashboard', 'Dashboard', '📊') !!}
            {!! $navItem('admin.users.index', 'Users', '👥') !!}
            {!! $navItem('admin.plans.index', 'Plans', '💳') !!}
            <a href="{{ route('admin.payment-requests.index') }}" class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.payment-requests*') ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span class="flex items-center gap-3">🧾 <span>Payment Requests</span></span>
                @php($pendingBadge = \App\Models\PaymentRequest::where('status', 'pending')->count())
                @if ($pendingBadge > 0)
                    <span class="text-xs bg-amber-500 text-white rounded-full px-2 py-0.5">{{ $pendingBadge }}</span>
                @endif
            </a>
            {!! $navItem('admin.settings.edit', 'Settings', '⚙️') !!}
        </nav>
        <div class="px-3 py-4 border-t border-slate-800">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white text-sm" type="submit">
                    🚪 <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="md:hidden bg-slate-900 text-white px-4 py-3 flex items-center justify-between">
            <div class="font-bold">📒 Hisab Nikash</div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="text-sm text-slate-300" type="submit">Logout</button>
            </form>
        </header>

        <main class="flex-1 p-4 md:p-8 max-w-7xl w-full mx-auto">
            <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">@yield('page-title', 'Admin')</h1>
                <div>@yield('page-actions')</div>
            </div>

            @if (session('status'))
                <div class="mb-5 rounded-xl border border-cyan-200 bg-cyan-50 text-cyan-800 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
