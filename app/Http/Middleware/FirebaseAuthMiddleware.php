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
            
            // Check if firebase_uid exists
            $user = User::where('firebase_uid', $decoded->sub)->first();
            
            if ($user) {
                // User exists with this firebase_uid
                // Update email if changed
                if ($decoded->email && $user->email !== $decoded->email) {
                    $user->update(['email' => $decoded->email]);
                }
            } else {
                // User doesn't exist with this firebase_uid
                // Check if user with same email already exists
                $existingUser = $decoded->email 
                    ? User::where('email', $decoded->email)->first() 
                    : null;
                
                if ($existingUser) {
                    // Merge: Update existing user with new firebase_uid
                    $existingUser->update([
                        'firebase_uid' => $decoded->sub,
                        'name' => $decoded->name ?? $existingUser->name,
                    ]);
                    $user = $existingUser;
                    \Log::info("Account linked: Email {$decoded->email} now linked to Firebase UID {$decoded->sub}");
                } else {
                    // Create new user
                    $freePlan = User::getPlanDefinition('free');
                    $normalizedEmail = $decoded->email ? strtolower(trim($decoded->email)) : null;
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
