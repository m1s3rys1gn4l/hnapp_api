@extends('admin.layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User #' . $user->id)

@section('content')
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="firebase_uid" class="block text-sm font-medium text-slate-700 mb-1">Firebase UID</label>
                <input id="firebase_uid" type="text" name="firebase_uid" value="{{ old('firebase_uid', $user->firebase_uid) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <hr class="border-slate-200 my-2">

            <div>
                <h3 class="font-semibold text-slate-900 mb-1">Package Assignment</h3>
                <p class="text-xs text-slate-500 mb-3">Set package manually from admin panel. Manage plan pricing/limits under <a href="{{ route('admin.plans.index') }}" class="text-blue-600 hover:underline">Plans</a>.</p>

                <label for="subscription_plan" class="block text-sm font-medium text-slate-700 mb-1">Package</label>
                <select id="subscription_plan" name="subscription_plan"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach ($planDefinitions as $planKey => $plan)
                        <option value="{{ $planKey }}" {{ old('subscription_plan', $user->subscription_plan ?? 'free') === $planKey ? 'selected' : '' }}>
                            {{ $plan['label'] }}
                        </option>
                    @endforeach
                </select>

                <label for="subscription_cycle" class="block text-sm font-medium text-slate-700 mb-1 mt-3">Billing Cycle (for paid plans)</label>
                <select id="subscription_cycle" name="subscription_cycle"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="monthly" {{ old('subscription_cycle', $user->subscription_cycle) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="yearly" {{ old('subscription_cycle', $user->subscription_cycle ?? 'yearly') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                </select>

                <label for="validity_days" class="block text-sm font-medium text-slate-700 mb-1 mt-3">Validity Period (Days)</label>
                <input id="validity_days" type="number" name="validity_days" min="1" max="3650" value="{{ old('validity_days', '') }}"
                       placeholder="Leave blank to use default (30 days for monthly, 365 for yearly)"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-slate-400 mt-1">Package automatically expires and reverts to Free after this period.</p>

                <div class="text-xs text-slate-500 bg-slate-50 rounded-lg px-3 py-2 mt-3">
                    <strong>Current status:</strong>
                    Books {{ $user->book_limit ?? 'Unlimited' }}, Customers {{ $user->customer_limit ?? 'Unlimited' }}, Ads {{ $user->show_ads ? 'On' : 'Off' }}<br>
                    @if ($user->subscription_expires_at)
                        <span class="text-amber-600">Expires: {{ $user->subscription_expires_at->format('Y-m-d H:i') }} ({{ $user->subscription_expires_at->diffForHumans() }})</span>
                    @else
                        No expiry (Free or lifetime plan)
                    @endif
                </div>
            </div>

            <hr class="border-slate-200 my-2">

            <div>
                <h3 class="font-semibold text-slate-900 mb-1">Change Password (Optional)</h3>
                <p class="text-xs text-slate-500 mb-3">Leave blank to keep current password</p>

                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                <input id="password" type="password" name="password" placeholder="Leave blank to keep current"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1 mt-3">Confirm New Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Leave blank to keep current"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <hr class="border-slate-200 my-2">

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_phone_verified" value="1" {{ old('is_phone_verified', $user->is_phone_verified) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600">
                Phone verified
            </label>

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600">
                User active
            </label>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg px-4 py-2">Save Changes</button>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-600 hover:underline">Back</a>
            </div>
        </form>
    </div>
@endsection
