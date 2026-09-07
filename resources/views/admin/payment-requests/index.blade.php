@extends('admin.layouts.app')

@section('title', 'Payment Requests')
@section('page-title', 'Payment Requests')

@section('content')
    <div class="flex gap-2 mb-5">
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.payment-requests.index', ['status' => $key]) }}"
               class="text-sm px-3 py-1.5 rounded-lg {{ $status === $key ? 'bg-blue-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                {{ $label }}
                @if ($key === 'pending' && $pendingCount > 0)
                    <span class="ml-1 text-xs {{ $status === $key ? 'text-blue-100' : 'text-amber-600' }}">({{ $pendingCount }})</span>
                @endif
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[960px]">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="text-left px-4 py-3">User</th>
                        <th class="text-left px-4 py-3">Plan</th>
                        <th class="text-left px-4 py-3">Amount</th>
                        <th class="text-left px-4 py-3">Payment Info</th>
                        <th class="text-left px-4 py-3">Proof</th>
                        <th class="text-left px-4 py-3">Status</th>
                        <th class="text-left px-4 py-3">Submitted</th>
                        <th class="text-left px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($requests as $r)
                        <tr class="hover:bg-slate-50 align-top">
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $r->user->name ?: '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $r->user->email }}</div>
                                <div class="text-xs text-slate-400">{{ $r->user->phone }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">{{ $r->plan->label }}</div>
                                <div class="text-xs text-slate-400">{{ ucfirst($r->cycle) }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-700">৳{{ number_format($r->amount_bdt) }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                <div>{{ strtoupper($r->payment_method) }} · {{ $r->sender_number }}</div>
                                <div class="text-xs text-slate-400">TxnID: {{ $r->transaction_id }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($r->screenshot_path)
                                    <a href="{{ asset('storage/' . $r->screenshot_path) }}" target="_blank" class="text-blue-600 hover:underline text-xs">View</a>
                                @else
                                    <span class="text-xs text-slate-400">None</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'text-xs px-2 py-1 rounded-full',
                                    'bg-amber-100 text-amber-700' => $r->status === 'pending',
                                    'bg-green-100 text-green-700' => $r->status === 'approved',
                                    'bg-red-100 text-red-700' => $r->status === 'rejected',
                                ])>{{ ucfirst($r->status) }}</span>
                                @if ($r->admin_note)
                                    <div class="text-xs text-slate-400 mt-1 max-w-[180px]">{{ $r->admin_note }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $r->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">
                                @if ($r->isPending())
                                    <div class="flex flex-col gap-2 min-w-[160px]">
                                        <form method="POST" action="{{ route('admin.payment-requests.approve', $r) }}">
                                            @csrf
                                            <button type="submit" class="w-full text-xs bg-green-600 hover:bg-green-700 text-white rounded-lg px-3 py-1.5">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.payment-requests.reject', $r) }}" onsubmit="return promptReject(event, this)">
                                            @csrf
                                            <input type="hidden" name="admin_note" class="reject-note">
                                            <button type="submit" class="w-full text-xs bg-red-600 hover:bg-red-700 text-white rounded-lg px-3 py-1.5">Reject</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">Reviewed by {{ $r->reviewed_by ?? '-' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">No payment requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $requests->links('pagination::tailwind') }}
    </div>

    <script>
        function promptReject(event, form) {
            const reason = prompt('Reason for rejecting this payment request:');
            if (!reason) {
                event.preventDefault();
                return false;
            }
            form.querySelector('.reject-note').value = reason;
            return true;
        }
    </script>
@endsection
