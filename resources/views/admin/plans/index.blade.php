@extends('admin.layouts.app')

@section('title', 'Plans')
@section('page-title', 'Subscription Plans')
@section('page-actions')
    <a href="{{ route('admin.plans.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg px-4 py-2">
        + New Plan
    </a>
@endsection

@section('content')
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[820px]">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="text-left px-4 py-3">Plan</th>
                        <th class="text-left px-4 py-3">Limits</th>
                        <th class="text-left px-4 py-3">Ads</th>
                        <th class="text-left px-4 py-3">Monthly</th>
                        <th class="text-left px-4 py-3">Yearly</th>
                        <th class="text-left px-4 py-3">Status</th>
                        <th class="text-left px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($plans as $plan)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $plan->label }}</div>
                                <div class="text-xs text-slate-400">{{ $plan->key }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $plan->book_limit ?? 'Unlimited' }} books · {{ $plan->customer_limit ?? 'Unlimited' }} customers
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded-full {{ $plan->show_ads ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $plan->show_ads ? 'Shown' : 'Hidden' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-700">৳{{ number_format($plan->monthly_price_bdt) }}</td>
                            <td class="px-4 py-3 text-slate-700">৳{{ number_format($plan->yearly_price_bdt) }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.plans.edit', $plan) }}" class="text-blue-600 hover:underline">Edit</a>
                                    @if ($plan->key !== 'free')
                                        <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" onsubmit="return confirm('Delete this plan?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
