<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Users</title>
    <style>
        :root {
            --bg-a: #f5f7ff;
            --bg-b: #effaf7;
            --panel: #ffffff;
            --line: #e2e8f0;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-soft: #dbeafe;
            --danger: #dc2626;
            --warn: #d97706;
            --success: #16a34a;
        }
        * { box-sizing: border-box; }
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            color: var(--text);
            background: radial-gradient(circle at top left, var(--bg-a), transparent 55%),
                        radial-gradient(circle at top right, var(--bg-b), transparent 45%),
                        #f8fafc;
        }
        .container { max-width: 1320px; margin: 0 auto; padding: 28px 20px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; gap: 14px; flex-wrap: wrap; }
        .title { margin: 0; font-size: 28px; letter-spacing: -0.02em; }
        .card {
            background: linear-gradient(180deg, #ffffff, #fcfdff);
            border: 1px solid #e5eaf4;
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }
        .toolbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
        .search { display: flex; gap: 8px; margin: 0; flex: 1; min-width: 260px; }
        input[type="text"] {
            flex: 1;
            min-width: 140px;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        input[type="text"]:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
        }
        input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--primary); }
        button, .btn {
            padding: 8px 13px;
            border: 0;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-warning { background: var(--warn); color: #fff; }
        .btn-success { background: var(--success); color: #fff; }
        .btn-neutral { background: #e8edf5; color: #111827; }
        .btn:hover { opacity: 0.92; }
        .status {
            margin-bottom: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            background: #ecfeff;
            color: #155e75;
            border: 1px solid #a5f3fc;
            font-size: 14px;
        }
        .errors {
            margin-bottom: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            font-size: 14px;
        }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .meta { color: var(--muted); font-size: 12px; line-height: 1.35; }
        .bulk-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            background: #f8fbff;
            border: 1px solid #e6eefb;
            border-radius: 12px;
            padding: 10px 12px;
            flex-wrap: wrap;
        }
        .table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: auto;
            background: #fff;
        }
        table { width: 100%; border-collapse: collapse; min-width: 980px; }
        th, td {
            text-align: left;
            padding: 12px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            vertical-align: top;
        }
        th {
            background: #f8fbff;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #475569;
            font-weight: 700;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        tr:hover td { background: #fcfdff; }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge-plan { background: #f1f5f9; color: #0f172a; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-disabled { background: #fee2e2; color: #991b1b; }
        .pagination {
            margin-top: 14px;
            display: flex;
            justify-content: center;
        }
        .pagination .pagination { margin: 0; }
        .pagination .page-item { list-style: none; }
        .pagination .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            margin: 0 3px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            color: #1f2937;
            text-decoration: none;
            background: #fff;
            font-size: 13px;
            padding: 0 12px;
        }
        .pagination .active .page-link {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .pagination .disabled .page-link {
            color: #94a3b8;
            background: #f8fafc;
        }
        @media (max-width: 900px) {
            .container { padding: 18px 12px; }
            .title { font-size: 24px; }
        }
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

        <div class="toolbar">
            <div class="actions">
                <a class="btn btn-primary" href="{{ route('admin.users.create') }}">Create User</a>
            </div>

            <form method="GET" class="search">
                <input type="text" name="search" placeholder="Search by name, email, phone, firebase uid" value="{{ $search }}">
                <button class="btn btn-primary" type="submit">Search</button>
                <a class="btn btn-neutral" href="{{ route('admin.users.index') }}">Reset</a>
            </form>
        </div>

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

            <div class="table-wrap">
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
                        <div class="badge badge-plan">{{ ucfirst($user->subscription_plan ?? 'free') }}</div>
                        <div class="meta">{{ $user->subscription_cycle ? ucfirst($user->subscription_cycle) : 'N/A' }}</div>
                            @if ($user->subscription_expires_at)
                                @if ($user->subscription_expires_at->isPast())
                                    <div class="meta" style="color: #dc2626;">❌ Expired {{ $user->subscription_expires_at->diffForHumans() }}</div>
                                @else
                                    <div class="meta" style="color: #16a34a;">✓ Expires {{ $user->subscription_expires_at->diffForHumans() }}</div>
                                @endif
                            @endif
                    </td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'badge-active' : 'badge-disabled' }}">
                            {{ $user->is_active ? 'Active' : 'Disabled' }}
                        </span>
                    </td>
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
            </div>
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
