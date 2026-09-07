<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Services\SmsService;
use Illuminate\Http\Request;

class AdminPaymentRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $requests = PaymentRequest::query()
            ->with(['user', 'plan'])
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.payment-requests.index', [
            'requests' => $requests,
            'status' => $status,
            'pendingCount' => PaymentRequest::where('status', 'pending')->count(),
        ]);
    }

    public function approve(Request $request, PaymentRequest $paymentRequest)
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!$paymentRequest->isPending()) {
            return back()->with('status', 'This request has already been reviewed.');
        }

        $user = $paymentRequest->user;
        $plan = $paymentRequest->plan;

        $user->applyPlan($plan->key, $paymentRequest->cycle);
        $user->save();

        $paymentRequest->update([
            'status' => 'approved',
            'admin_note' => $validated['admin_note'] ?? null,
            'reviewed_by' => $request->session()->get('admin_email'),
            'reviewed_at' => now(),
        ]);

        if ($user->phone) {
            try {
                SmsService::send(
                    $user->phone,
                    "Your {$plan->label} subscription has been activated. Thank you for using Hisab Nikash!"
                );
            } catch (\Throwable $e) {
                \Log::warning('Failed to send subscription approval SMS: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('admin.payment-requests.index')
            ->with('status', "Payment approved. {$user->name} is now on the {$plan->label} plan.");
    }

    public function reject(Request $request, PaymentRequest $paymentRequest)
    {
        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ]);

        if (!$paymentRequest->isPending()) {
            return back()->with('status', 'This request has already been reviewed.');
        }

        $paymentRequest->update([
            'status' => 'rejected',
            'admin_note' => $validated['admin_note'],
            'reviewed_by' => $request->session()->get('admin_email'),
            'reviewed_at' => now(),
        ]);

        $user = $paymentRequest->user;
        if ($user->phone) {
            try {
                SmsService::send(
                    $user->phone,
                    "Your subscription payment request could not be verified: {$validated['admin_note']}. Please contact support."
                );
            } catch (\Throwable $e) {
                \Log::warning('Failed to send subscription rejection SMS: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('admin.payment-requests.index')
            ->with('status', 'Payment request rejected.');
    }
}
