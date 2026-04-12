<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Users</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; margin: 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .title { margin: 0; font-size: 24px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; }
        .search { display: flex; gap: 8px; margin-bottom: 14px; }
        input[type="text"] { flex: 1; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 8px; }
        input[type="checkbox"] { width: 16px; height: 16px; }
        button, .btn { padding: 8px 12px; border: 0; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-warning { background: #d97706; color: #fff; }
        .btn-success { background: #16a34a; color: #fff; }
        .btn-neutral { background: #e2e8f0; color: #111827; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #eef2f7; font-size: 14px; vertical-align: top; }
        .status { margin-bottom: 10px; padding: 10px; border-radius: 8px; background: #ecfeff; color: #155e75; border: 1px solid #a5f3fc; }
        .errors { margin-bottom: 10px; padding: 10px; border-radius: 8px; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .actions { display: flex; gap: 8px; }
        .meta { color: #64748b; font-size: 12px; }
        .pagination {
            margin-top: 14px;
            display: flex;
            justify-content: center;
        }
        .pagination nav,
        .pagination > div {
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .pagination ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .pagination li {
            margin: 0;
            display: inline-flex;
        }
        .pagination a,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            text-decoration: none;
            color: #1f2937;
            background: #fff;
            font-size: 13px;
            white-space: nowrap;
            box-sizing: border-box;
        }
        .pagination .active span,
        .pagination [aria-current="page"] span {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }
        .pagination .disabled span,
        .pagination [aria-disabled="true"] span {
            color: #94a3b8;
            background: #f8fafc;
        }
        .pagination svg {
            width: 14px;
            height: 14px;
        }
        .pagination .flex.justify-between {
            width: auto;
            display: flex;
            justify-content: center;
            gap: 8px;
        }
        .bulk-bar { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="container">
    <div class="topbar">
        <h1 class="title">User Management</h1>
        <div class="actions">
            <a class="btn btn-neutral" href="{{ route('admin.settings.edit') }}">Settings</a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="btn btn-neutral" type="submit">Logout</button>
            </form>
        </div>
    </div>

    <div class="card">
        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="actions" style="margin-bottom: 12px;">
            <a class="btn btn-primary" href="{{ route('admin.users.create') }}">Create User</a>
        </div>

        <form method="GET" class="search">
            <input type="text" name="search" placeholder="Search by name, email, phone, firebase uid" value="{{ $search }}">
            <button class="btn btn-primary" type="submit">Search</button>
            <a class="btn btn-neutral" href="{{ route('admin.users.index') }}">Reset</a>
        </form>

        <form method="POST" action="{{ route('admin.users.bulk-destroy') }}" onsubmit="return confirm('Delete selected users? This cannot be undone.')">
            @csrf
            @method('DELETE')

            <div class="bulk-bar">
                <label>
                    <input type="checkbox" id="select-all">
                    Select all on this page
                </label>
                <div class="actions" style="align-items:center;">
                    <input type="text" name="confirm_text" placeholder="Type DELETE" style="max-width: 140px;">
                    <button class="btn btn-danger" type="submit">Bulk Delete Selected</button>
                </div>
            </div>

            <table>
            <thead>
            <tr>
                <th style="width:40px;"></th>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Package</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($users as $user)
                @php($isMappedAdminUser = isset($adminMappedUserId) && $adminMappedUserId === $user->id)
                <tr>
                    <td>
                        @if (!$isMappedAdminUser)
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="row-checkbox">
                        @endif
                    </td>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name ?: '-' }}</td>
                    <td>
                        <div>{{ $user->email }}</div>
                        <div class="meta">{{ $user->email_verified_at ? 'Email verified' : 'Email not verified' }}</div>
                    </td>
                    <td>{{ $user->phone ?: '-' }}</td>
                    <td>
                        <div>{{ ucfirst($user->subscription_plan ?? 'free') }}</div>
                        <div class="meta">{{ $user->subscription_cycle ? ucfirst($user->subscription_cycle) : 'N/A' }}</div>
                            @if ($user->subscription_expires_at)
                                @if ($user->subscription_expires_at->isPast())
                                    <div class="meta" style="color: #dc2626;">❌ Expired {{ $user->subscription_expires_at->diffForHumans() }}</div>
                                @else
                                    <div class="meta" style="color: #16a34a;">✓ Expires {{ $user->subscription_expires_at->diffForHumans() }}</div>
                                @endif
                            @endif
                    </td>
                    <td>{{ $user->is_active ? 'Active' : 'Disabled' }}</td>
                    <td>{{ optional($user->created_at)->format('Y-m-d H:i') }}</td>
                    <td>
                        <div class="actions">
                            <a class="btn btn-primary" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                            @if ($user->email)
                                <form method="POST" action="{{ route('admin.users.send-password-reset', $user) }}">
                                    @csrf
                                    <button class="btn btn-neutral" type="submit" title="Send password reset email">🔑 Reset Password</button>
                                </form>
                            @endif
                            @if ($isMappedAdminUser)
                                <span class="meta">Protected admin user</span>
                            @else
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn {{ $user->is_active ? 'btn-warning' : 'btn-success' }}" type="submit">
                                        {{ $user->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" type="submit">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">No users found.</td>
                </tr>
            @endforelse
            </tbody>
            </table>
        </form>

        <div class="pagination">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
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
</body>
</html>
