<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    /**
     * Submit a new payment request (bKash/Nagad/Rocket) for a paid plan.
     * The admin reviews and approves/rejects it from the admin panel.
     */
    public function store(Request $request)
    {
        $user = $request->auth_user;

        $validated = $request->validate([
            'plan_key' => ['required', 'string', Rule::exists('plans', 'key')->where('is_active', true)],
            'cycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'payment_method' => ['required', Rule::in(['bkash', 'nagad', 'rocket'])],
            'sender_number' => ['required', 'string', 'max:20'],
            'transaction_id' => ['required', 'string', 'max:100'],
            'screenshot' => ['nullable', 'image', 'max:4096'],
        ]);

        $plan = Plan::where('key', $validated['plan_key'])->firstOrFail();

        if ($plan->key === 'free') {
            return response()->json([
                'success' => false,
                'message' => 'The free plan does not require a payment request.',
            ], 422);
        }

        if ($user->paymentRequests()->where('status', 'pending')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending payment request. Please wait for it to be reviewed.',
            ], 422);
        }

        $screenshotPath = null;
        if ($request->hasFile('screenshot')) {
            $screenshotPath = $request->file('screenshot')->store('payment-screenshots', 'public');
        }

        $paymentRequest = PaymentRequest::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'cycle' => $validated['cycle'],
            'amount_bdt' => $plan->priceFor($validated['cycle']),
            'payment_method' => $validated['payment_method'],
            'sender_number' => $validated['sender_number'],
            'transaction_id' => $validated['transaction_id'],
            'screenshot_path' => $screenshotPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment request submitted. It will be reviewed by an admin shortly.',
            'request' => $this->formatRequest($paymentRequest->load('plan')),
        ], 201);
    }

    /**
     * The authenticated user's own payment request history.
     */
    public function index(Request $request)
    {
        $user = $request->auth_user;

        $requests = $user->paymentRequests()
            ->with('plan')
            ->latest('id')
            ->get();

        return response()->json([
            'requests' => $requests->map(fn ($r) => $this->formatRequest($r)),
        ]);
    }

    private function formatRequest(PaymentRequest $r): array
    {
        return [
            'id' => $r->id,
            'plan_key' => $r->plan->key,
            'plan_label' => $r->plan->label,
            'cycle' => $r->cycle,
            'amount_bdt' => $r->amount_bdt,
            'payment_method' => $r->payment_method,
            'transaction_id' => $r->transaction_id,
            'status' => $r->status,
            'admin_note' => $r->admin_note,
            'created_at' => $r->created_at->toIso8601String(),
            'reviewed_at' => optional($r->reviewed_at)->toIso8601String(),
        ];
    }
}
