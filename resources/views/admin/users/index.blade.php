@extends('admin.layouts.app')

@section('title', 'Users')
@section('page-title', 'User Management')
@section('page-actions')
    <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg px-4 py-2">
        + Create User
    </a>
@endsection

@section('content')
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-5">
        <form method="GET" class="flex gap-2 flex-wrap">
            <input type="text" name="search" placeholder="Search by name, email, phone, firebase uid" value="{{ $search }}"
                   class="flex-1 min-w-[240px] rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg px-4 py-2">Search</button>
            <a href="{{ route('admin.users.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-lg px-4 py-2">Reset</a>
        </form>
    </div>

    <form method="POST" action="{{ route('admin.users.bulk-destroy') }}" onsubmit="return confirm('Delete selected users? This cannot be undone.')">
        @csrf
        @method('DELETE')

        <div class="flex items-center justify-between gap-3 flex-wrap bg-white rounded-2xl border border-slate-200 shadow-sm px-4 py-3 mb-4">
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" id="select-all" class="rounded border-slate-300 text-blue-600">
                Select all on this page
            </label>
            <div class="flex items-center gap-2">
                <input type="text" name="confirm_text" placeholder="Type DELETE"
                       class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm w-32 focus:outline-none focus:ring-2 focus:ring-red-500">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg px-4 py-2">Bulk Delete</button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[980px]">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 py-3 w-10"></th>
                            <th class="text-left px-4 py-3">ID</th>
                            <th class="text-left px-4 py-3">Name</th>
                            <th class="text-left px-4 py-3">Email</th>
                            <th class="text-left px-4 py-3">Phone</th>
                            <th class="text-left px-4 py-3">Package</th>
                            <th class="text-left px-4 py-3">Status</th>
                            <th class="text-left px-4 py-3">Created</th>
                            <th class="text-left px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        @php($isMappedAdminUser = isset($adminMappedUserId) && $adminMappedUserId === $user->id)
                        @php($normalizedPlan = $user->normalizedPlanKey())
                        @php($planLabel = \App\Models\User::getPlanDefinition($normalizedPlan)['label'])
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                @if (!$isMappedAdminUser)
                                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="row-checkbox rounded border-slate-300 text-blue-600">
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $user->id }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $user->name ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="text-slate-800">{{ $user->email }}</div>
                                <div class="text-xs text-slate-400">{{ $user->email_verified_at ? 'Email verified' : 'Email not verified' }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->phone ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-700">{{ $planLabel }}</span>
                                <div class="text-xs text-slate-400 mt-1">{{ $normalizedPlan === 'free' ? 'N/A' : ucfirst($user->subscription_cycle ?? 'yearly') }}</div>
                                @if ($user->subscription_expires_at)
                                    @if ($user->subscription_expires_at->isPast())
                                        <div class="text-xs text-red-600 mt-0.5">❌ Expired {{ $user->subscription_expires_at->diffForHumans() }}</div>
                                    @else
                                        <div class="text-xs text-green-600 mt-0.5">✓ Expires {{ $user->subscription_expires_at->diffForHumans() }}</div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $user->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ optional($user->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3 py-1.5">Edit</a>
                                    @if ($user->email)
                                        <form method="POST" action="{{ route('admin.users.send-password-reset', $user) }}">
                                            @csrf
                                            <button type="submit" title="Send password reset email" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg px-3 py-1.5">🔑 Reset</button>
                                        </form>
                                    @endif
                                    @if ($isMappedAdminUser)
                                        <span class="text-xs text-slate-400">Protected admin</span>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-xs rounded-lg px-3 py-1.5 text-white {{ $user->is_active ? 'bg-amber-500 hover:bg-amber-600' : 'bg-green-600 hover:bg-green-700' }}">
                                                {{ $user->is_active ? 'Disable' : 'Enable' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs bg-red-600 hover:bg-red-700 text-white rounded-lg px-3 py-1.5">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-slate-400">No users found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    <div class="mt-4">
        {{ $users->links('pagination::tailwind') }}
    </div>

    <script>
        const selectAll = document.getElementById('select-all');
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.row-checkbox').forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            });
        }
    </script>
@endsection
