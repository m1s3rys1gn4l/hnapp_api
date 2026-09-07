<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;

class AdminPlanController extends Controller
{
    public function index()
    {
        $plans = Plan::query()->orderBy('sort_order')->get();

        return view('admin.plans.index', [
            'plans' => $plans,
        ]);
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:plans,key'],
            'label' => ['required', 'string', 'max:255'],
            'book_limit' => ['nullable', 'integer', 'min:0'],
            'customer_limit' => ['nullable', 'integer', 'min:0'],
            'show_ads' => ['nullable', 'boolean'],
            'monthly_price_bdt' => ['required', 'integer', 'min:0'],
            'yearly_price_bdt' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['show_ads'] = $request->boolean('show_ads');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Plan::create($validated);
        User::forgetPlanDefinitionsCache();

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', [
            'plan' => $plan,
        ]);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'book_limit' => ['nullable', 'integer', 'min:0'],
            'customer_limit' => ['nullable', 'integer', 'min:0'],
            'show_ads' => ['nullable', 'boolean'],
            'monthly_price_bdt' => ['required', 'integer', 'min:0'],
            'yearly_price_bdt' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['show_ads'] = $request->boolean('show_ads');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $plan->update($validated);
        User::forgetPlanDefinitionsCache();

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->key === 'free') {
            return redirect()
                ->route('admin.plans.index')
                ->with('status', 'The free plan cannot be deleted.');
        }

        if ($plan->paymentRequests()->exists()) {
            return redirect()
                ->route('admin.plans.index')
                ->with('status', 'Cannot delete a plan that has payment request history. Deactivate it instead.');
        }

        $plan->delete();
        User::forgetPlanDefinitionsCache();

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'Plan deleted successfully.');
    }
}
