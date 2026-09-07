<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $paidUsers = User::where('subscription_plan', '!=', 'free')
            ->where(function ($q) {
                $q->whereNull('subscription_expires_at')->orWhere('subscription_expires_at', '>', now());
            })
            ->count();
        $pendingRequests = PaymentRequest::where('status', 'pending')->count();

        $revenueThisMonth = PaymentRequest::where('status', 'approved')
            ->whereMonth('reviewed_at', now()->month)
            ->whereYear('reviewed_at', now()->year)
            ->sum('amount_bdt');

        $revenueTotal = PaymentRequest::where('status', 'approved')->sum('amount_bdt');

        $planBreakdown = User::query()
            ->select('subscription_plan', DB::raw('count(*) as total'))
            ->groupBy('subscription_plan')
            ->pluck('total', 'subscription_plan');

        $recentUsers = User::latest('id')->limit(5)->get();
        $recentPaymentRequests = PaymentRequest::with(['user', 'plan'])->latest('id')->limit(5)->get();

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'paidUsers' => $paidUsers,
            'pendingRequests' => $pendingRequests,
            'revenueThisMonth' => $revenueThisMonth,
            'revenueTotal' => $revenueTotal,
            'planBreakdown' => $planBreakdown,
            'recentUsers' => $recentUsers,
            'recentPaymentRequests' => $recentPaymentRequests,
        ]);
    }
}
