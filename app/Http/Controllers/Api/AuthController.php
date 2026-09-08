<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private FirebaseService $firebaseService)
    {
    }

    /**
     * Send a login/signup OTP to a phone number via the SMS gateway.
     */
    public function requestOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $result = OtpService::sendOtp($validated['phone']);

        return response()->json($result, $result['success'] ? 200 : 429);
    }

    /**
     * Verify a login/signup OTP. On success, mints a Firebase custom token
     * so the client can sign in via signInWithCustomToken() and use the
     * rest of the API exactly like a Google/email user.
     */
    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'otp' => ['required', 'string', 'max:10'],
        ]);

        $result = OtpService::verifyOtp($validated['phone'], $validated['otp']);

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        $normalizedPhone = $result['phone'];

        // Reuse an existing account if this phone is already verified on one
        // (e.g. a Google/email user who verified their phone from the profile
        // screen can also log in with that phone). Never match an
        // unverified phone claim on another account.
        $user = User::where('phone', $normalizedPhone)
            ->where('is_phone_verified', true)
            ->first();

        if ($user) {
            if (empty($user->firebase_uid)) {
                // Shouldn't normally happen, but keep it self-healing.
                $user->firebase_uid = (string) Str::uuid();
            }

            $linkedProviders = $user->linked_providers ?? [];
            if (!in_array('phone', $linkedProviders, true)) {
                $linkedProviders[] = 'phone';
            }
            $user->linked_providers = $linkedProviders;
            $user->save();
        } else {
            $freePlan = User::getPlanDefinition('free');

            $user = User::create([
                'firebase_uid' => (string) Str::uuid(),
                'phone' => $normalizedPhone,
                'is_phone_verified' => true,
                'phone_verified_at' => now(),
                'linked_providers' => ['phone'],
                'is_active' => true,
                'subscription_plan' => 'free',
                'subscription_cycle' => null,
                'book_limit' => $freePlan['book_limit'],
                'customer_limit' => $freePlan['customer_limit'],
                'show_ads' => $freePlan['show_ads'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account disabled. Please contact support.',
            ], 403);
        }

        try {
            $customToken = $this->firebaseService->createCustomToken($user->firebase_uid);
        } catch (\Throwable $e) {
            \Log::error('Failed to mint Firebase custom token: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Login succeeded but sign-in token could not be created. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'token' => $customToken,
            'is_new_user' => $user->wasRecentlyCreated,
            'user' => [
                'id' => $user->id,
                'phone' => $user->phone,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
