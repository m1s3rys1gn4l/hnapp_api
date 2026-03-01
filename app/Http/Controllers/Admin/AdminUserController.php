<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function __construct(private FirebaseService $firebaseService)
    {
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        try {
            $firebase = $this->firebaseService->createUser(
                $validated['email'],
                $validated['password'],
                $validated['name'] ?? null
            );
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['email' => 'Failed to create Firebase user: ' . $e->getMessage()])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        unset($validated['password'], $validated['password_confirmation']);

        $validated['firebase_uid'] = $firebase['uid'];
        $validated['email'] = strtolower(trim($firebase['email']));

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['disabled_at'] = $validated['is_active'] ? null : now();
        $freePlan = User::getPlanDefinition('free');
        $validated['subscription_plan'] = 'free';
        $validated['subscription_cycle'] = null;
        $validated['book_limit'] = $freePlan['book_limit'];
        $validated['customer_limit'] = $freePlan['customer_limit'];
        $validated['show_ads'] = $freePlan['show_ads'];

        User::create($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User created successfully.');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $adminMappedUserId = $this->getAdminMappedUserId($request);

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('firebase_uid', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'adminMappedUserId' => $adminMappedUserId,
        ]);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'planDefinitions' => User::PLAN_DEFINITIONS,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'firebase_uid' => ['nullable', 'string', 'max:255'],
            'is_phone_verified' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'subscription_plan' => ['required', Rule::in(array_keys(User::PLAN_DEFINITIONS))],
            'subscription_cycle' => ['nullable', Rule::in(['monthly', 'yearly'])],
                'validity_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
        ]);

        $validated['is_phone_verified'] = $request->boolean('is_phone_verified');
        $validated['phone_verified_at'] = $validated['is_phone_verified'] ? now() : null;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['disabled_at'] = $validated['is_active'] ? null : now();

        $adminMappedUserId = $this->getAdminMappedUserId($request);
        if ($adminMappedUserId !== null && $user->id === $adminMappedUserId && !$validated['is_active']) {
            return redirect()
                ->route('admin.users.index')
                ->with('status', 'Cannot disable the mapped admin user.');
        }

        // Update Firebase password if provided
        if (!empty($validated['password']) && !empty($user->firebase_uid)) {
            try {
                $this->firebaseService->updatePassword($user->firebase_uid, $validated['password']);
            } catch (\Throwable $e) {
                return back()
                    ->withErrors(['password' => 'Failed to update Firebase password: ' . $e->getMessage()])
                    ->withInput($request->except(['password', 'password_confirmation']));
            }
        }

        unset($validated['password'], $validated['password_confirmation']);

        $selectedPlan = $validated['subscription_plan'];
        $selectedCycle = $selectedPlan === 'free' ? null : ($validated['subscription_cycle'] ?? 'yearly');
        
            // Apply plan with custom validity if provided
            $user->applyPlan($selectedPlan, $selectedCycle);
        
            // Override expiry date if validity_days is provided
            if (!empty($validated['validity_days']) && $selectedPlan !== 'free') {
                $user->subscription_expires_at = now()->addDays((int) $validated['validity_days']);
            }

            unset($validated['subscription_plan'], $validated['subscription_cycle'], $validated['validity_days']);

        $user->update($validated);
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User updated successfully.');
    }

    public function sendPasswordReset(Request $request, User $user)
    {
        if (empty($user->email)) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors(['email' => 'User has no email address.']);
        }

        try {
            $this->firebaseService->sendPasswordResetEmail($user->email);
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors(['email' => 'Failed to send password reset email: ' . $e->getMessage()]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Password reset email sent to {$user->email}");
    }

    public function toggleStatus(Request $request, User $user)
    {
        $adminMappedUserId = $this->getAdminMappedUserId($request);
        if ($adminMappedUserId !== null && $user->id === $adminMappedUserId && $user->is_active) {
            return redirect()
                ->route('admin.users.index')
                ->with('status', 'Cannot disable the mapped admin user.');
        }

        $newStatus = !$user->is_active;

        $user->update([
            'is_active' => $newStatus,
            'disabled_at' => $newStatus ? null : now(),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', $newStatus ? 'User enabled successfully.' : 'User disabled successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'confirm_text' => ['required', 'in:DELETE'],
        ]);

        $adminMappedUserId = $this->getAdminMappedUserId($request);

        $ids = collect($validated['user_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->reject(fn ($id) => $adminMappedUserId !== null && $id === (int) $adminMappedUserId)
            ->values()
            ->all();

        if (empty($ids)) {
            return redirect()
                ->route('admin.users.index')
                ->with('status', 'No users deleted. The mapped admin user cannot be bulk deleted.');
        }

        $deleted = DB::table('users')->whereIn('id', $ids)->delete();

        $skippedAdmin = $adminMappedUserId !== null
            && in_array((int) $adminMappedUserId, array_map('intval', $validated['user_ids']), true);

        $message = "{$deleted} user(s) deleted successfully.";
        if ($skippedAdmin) {
            $message .= ' Admin mapped user was skipped for safety.';
        }

        return redirect()
            ->route('admin.users.index')
            ->with('status', $message);
    }

    public function destroy(Request $request, User $user)
    {
        $adminMappedUserId = $this->getAdminMappedUserId($request);
        if ($adminMappedUserId !== null && $user->id === $adminMappedUserId) {
            return redirect()
                ->route('admin.users.index')
                ->with('status', 'Cannot delete the mapped admin user.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User deleted successfully.');
    }

    private function getAdminMappedUserId(Request $request): ?int
    {
        $id = User::query()
            ->where('email', $request->session()->get('admin_email'))
            ->value('id');

        return $id ? (int) $id : null;
    }
}
