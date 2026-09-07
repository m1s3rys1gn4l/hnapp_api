@php($plan = $plan ?? null)

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @unless ($plan)
        <div>
            <label for="key" class="block text-sm font-medium text-slate-700 mb-1">Plan Key</label>
            <input id="key" type="text" name="key" value="{{ old('key') }}" required placeholder="e.g. premium_plus"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-slate-400 mt-1">Unique, lowercase, no spaces. Cannot be changed later.</p>
        </div>
    @endunless

    <div>
        <label for="label" class="block text-sm font-medium text-slate-700 mb-1">Display Label</label>
        <input id="label" type="text" name="label" value="{{ old('label', $plan->label ?? '') }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
        <label for="book_limit" class="block text-sm font-medium text-slate-700 mb-1">Book Limit</label>
        <input id="book_limit" type="number" min="0" name="book_limit" value="{{ old('book_limit', $plan->book_limit ?? '') }}" placeholder="Leave blank for unlimited"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
        <label for="customer_limit" class="block text-sm font-medium text-slate-700 mb-1">Customer Limit</label>
        <input id="customer_limit" type="number" min="0" name="customer_limit" value="{{ old('customer_limit', $plan->customer_limit ?? '') }}" placeholder="Leave blank for unlimited"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
        <label for="monthly_price_bdt" class="block text-sm font-medium text-slate-700 mb-1">Monthly Price (BDT)</label>
        <input id="monthly_price_bdt" type="number" min="0" name="monthly_price_bdt" value="{{ old('monthly_price_bdt', $plan->monthly_price_bdt ?? 0) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
        <label for="yearly_price_bdt" class="block text-sm font-medium text-slate-700 mb-1">Yearly Price (BDT)</label>
        <input id="yearly_price_bdt" type="number" min="0" name="yearly_price_bdt" value="{{ old('yearly_price_bdt', $plan->yearly_price_bdt ?? 0) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
        <label for="sort_order" class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
        <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
</div>

<div class="flex items-center gap-6 mt-5">
    <label class="flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="show_ads" value="1" {{ old('show_ads', $plan->show_ads ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600">
        Show ads on this plan
    </label>
    <label class="flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600">
        Active (selectable by clients)
    </label>
</div>

<div class="flex items-center gap-3 mt-6">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg px-4 py-2">
        {{ $plan ? 'Save Changes' : 'Create Plan' }}
    </button>
    <a href="{{ route('admin.plans.index') }}" class="text-sm text-slate-600 hover:underline">Cancel</a>
</div>
