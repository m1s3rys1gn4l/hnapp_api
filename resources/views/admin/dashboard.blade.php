@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Total Users</div>
            <div class="text-3xl font-bold mt-1 text-slate-900">{{ number_format($totalUsers) }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ number_format($activeUsers) }} active</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Paid Subscribers</div>
            <div class="text-3xl font-bold mt-1 text-slate-900">{{ number_format($paidUsers) }}</div>
            <div class="text-xs text-slate-400 mt-1">Currently on a paid plan</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Pending Payment Requests</div>
            <div class="text-3xl font-bold mt-1 {{ $pendingRequests > 0 ? 'text-amber-600' : 'text-slate-900' }}">{{ number_format($pendingRequests) }}</div>
            <a href="{{ route('admin.payment-requests.index') }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">Review now →</a>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Revenue This Month</div>
            <div class="text-3xl font-bold mt-1 text-slate-900">৳{{ number_format($revenueThisMonth) }}</div>
            <div class="text-xs text-slate-400 mt-1">৳{{ number_format($revenueTotal) }} all time</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <h2 class="font-semibold text-slate-900 mb-4">Recent Users</h2>
            <div class="space-y-3">
                @forelse ($recentUsers as $user)
                    <div class="flex items-center justify-between text-sm">
                        <div>
                            <div class="font-medium text-slate-800">{{ $user->name ?: $user->email ?: 'Unknown' }}</div>
                            <div class="text-xs text-slate-400">{{ $user->email }}</div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-700">{{ ucfirst($user->normalizedPlanKey()) }}</span>
                    </div>
                @empty
                    <div class="text-sm text-slate-400">No users yet.</div>
                @endforelse
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-xs text-blue-600 hover:underline mt-4 inline-block">View all users →</a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <h2 class="font-semibold text-slate-900 mb-4">Recent Payment Requests</h2>
            <div class="space-y-3">
                @forelse ($recentPaymentRequests as $r)
                    <div class="flex items-center justify-between text-sm">
                        <div>
                            <div class="font-medium text-slate-800">{{ $r->user->name ?: $r->user->email }}</div>
                            <div class="text-xs text-slate-400">{{ $r->plan->label }} · ৳{{ number_format($r->amount_bdt) }} via {{ strtoupper($r->payment_method) }}</div>
                        </div>
                        <span @class([
                            'text-xs px-2 py-1 rounded-full',
                            'bg-amber-100 text-amber-700' => $r->status === 'pending',
                            'bg-green-100 text-green-700' => $r->status === 'approved',
                            'bg-red-100 text-red-700' => $r->status === 'rejected',
                        ])>{{ ucfirst($r->status) }}</span>
                    </div>
                @empty
                    <div class="text-sm text-slate-400">No payment requests yet.</div>
                @endforelse
            </div>
            <a href="{{ route('admin.payment-requests.index') }}" class="text-xs text-blue-600 hover:underline mt-4 inline-block">View all requests →</a>
        </div>
    </div>
@endsection
