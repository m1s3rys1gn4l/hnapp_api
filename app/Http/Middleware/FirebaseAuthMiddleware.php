<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;

class FirebaseAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized - No token provided'], 401);
        }

        try {
            // Verify Firebase ID token
            $decoded = $this->verifyFirebaseToken($token);
            
            $tokenEmail = isset($decoded->email) && is_string($decoded->email)
                ? strtolower(trim($decoded->email))
                : null;
            $tokenPhone = isset($decoded->phone_number) && is_string($decoded->phone_number)
                ? trim($decoded->phone_number)
                : null;

            // Check if firebase_uid exists
            $user = User::where('firebase_uid', $decoded->sub)->first();
            
            if ($user) {
                // User exists with this firebase_uid, update changed profile fields from token.
                $updates = [];
                if ($tokenEmail && $user->email !== $tokenEmail) {
                    $updates['email'] = $tokenEmail;
                }
                if ($tokenPhone && $user->phone !== $tokenPhone) {
                    $updates['phone'] = $tokenPhone;
                    $updates['is_phone_verified'] = true;
                    $updates['phone_verified_at'] = now();
                }
                if (!empty($updates)) {
                    $user->update($updates);
                }
            } else {
                // User doesn't exist with this firebase_uid
                // Check if user with same email already exists
                $existingUser = $tokenEmail
                    ? User::whereRaw('LOWER(email) = ?', [$tokenEmail])->first()
                    : null;
                
                if ($existingUser) {
                    // Merge: Update existing user with new firebase_uid
                    $mergeUpdates = [
                        'firebase_uid' => $decoded->sub,
                        'name' => $decoded->name ?? $existingUser->name,
                    ];
                    if ($tokenPhone) {
                        $mergeUpdates['phone'] = $tokenPhone;
                        $mergeUpdates['is_phone_verified'] = true;
                        $mergeUpdates['phone_verified_at'] = now();
                    }
                    $existingUser->update($mergeUpdates);
                    $user = $existingUser;
                    \Log::info("Account linked: Email {$tokenEmail} now linked to Firebase UID {$decoded->sub}");
                } else {
                    // Create new user
                    $freePlan = User::getPlanDefinition('free');
                    $normalizedEmail = $tokenEmail;
                    $userName = $decoded->name ?? null;
                    
                    \Log::info("Creating new user from Firebase token", [
                        'firebase_uid' => $decoded->sub,
                        'email_from_token' => $decoded->email,
                        'normalized_email' => $normalizedEmail,
                        'name_from_token' => $userName,
                    ]);
                    
                    $user = User::create([
                        'firebase_uid' => $decoded->sub,
                        'email' => $normalizedEmail,
                        'name' => $userName,
                        'phone' => $tokenPhone,
                        'is_phone_verified' => $tokenPhone ? true : false,
                        'phone_verified_at' => $tokenPhone ? now() : null,
                        'subscription_plan' => 'free',
                        'subscription_cycle' => null,
                        'book_limit' => $freePlan['book_limit'],
                        'customer_limit' => $freePlan['customer_limit'],
                        'show_ads' => $freePlan['show_ads'],
                    ]);
                    
                    \Log::info("User created successfully", [
                        'user_id' => $user->id,
                        'firebase_uid' => $user->firebase_uid,
                        'email' => $user->email,
                        'name' => $user->name,
                    ]);
                }
            }

            if (!$user->is_active) {
                return response()->json([
                    'error' => 'Account disabled. Please contact support.',
                ], 403);
            }

            // Attach user to request
            $request->merge(['auth_user' => $user]);
            $request->setUserResolver(fn () => $user);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized - ' . $e->getMessage()], 401);
        }

        return $next($request);
    }

    private function verifyFirebaseToken($token)
    {
        $projectId = config('services.firebase.project_id');
        
        // Fetch Google's public keys
        $keysUrl = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';
        $keys = json_decode(file_get_contents($keysUrl), true);

        // Decode header to get key ID
        $tks = explode('.', $token);
        if (count($tks) != 3) {
            throw new \Exception('Invalid token format');
        }

        $header = json_decode(JWT::urlsafeB64Decode($tks[0]));
        
        if (!isset($keys[$header->kid])) {
            throw new \Exception('Invalid key ID');
        }

        $publicKey = $keys[$header->kid];
        
        // Verify and decode token
        $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));

        // Verify claims
        if ($decoded->aud !== $projectId) {
            throw new \Exception('Invalid audience');
        }

        if ($decoded->iss !== "https://securetoken.google.com/{$projectId}") {
            throw new \Exception('Invalid issuer');
        }

        if (time() >= $decoded->exp) {
            throw new \Exception('Token expired');
        }

        return $decoded;
    }
}
