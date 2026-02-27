<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckExpiredSubscriptions
{
    /**
     * Handle an incoming request and check if user's subscription has expired.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via firebase.auth middleware
        $user = $request->user();
        
        if ($user && $user->subscription_expires_at) {
            // Check if subscription has expired
            if ($user->subscription_expires_at->isPast() && $user->subscription_plan !== 'free') {
                // Automatically revert to free plan
                $user->applyPlan('free');
                $user->save();
                
                // Optionally log this event
                \Log::info("User {$user->id} subscription expired, reverted to free plan");
            }
        }
        
        return $next($request);
    }
}
