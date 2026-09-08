<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FirebaseService
{
    /**
     * Get a short-lived OAuth2 access token for the service account, scoped
     * for Identity Toolkit admin operations (e.g. updating a user's
     * password by uid). Unlike the plain API key, this authorizes
     * admin-level actions on arbitrary users. Cached for slightly less than
     * its ~1 hour lifetime.
     */
    private function getAccessToken(): string
    {
        return Cache::remember('firebase_admin_access_token', 3000, function () {
            $path = config('services.firebase.service_account_path');

            if (!$path || !file_exists($path)) {
                throw new \RuntimeException('Firebase service account file not found. Set FIREBASE_SERVICE_ACCOUNT_PATH or place it at storage/app/secrets/firebase-service-account.json.');
            }

            $credentials = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/identitytoolkit',
                $path
            );

            $token = $credentials->fetchAuthToken();

            if (empty($token['access_token'])) {
                throw new \RuntimeException('Failed to obtain a Firebase admin access token.');
            }

            return $token['access_token'];
        });
    }

    /**
     * Mint a Firebase custom auth token for the given UID, signed with the
     * project's service account private key (RS256). The client exchanges
     * this for an ID token via `signInWithCustomToken()`.
     *
     * If a Firebase user with this UID doesn't exist yet, Firebase creates
     * it automatically on first sign-in with the custom token.
     *
     * @param array<string, mixed> $claims Optional custom claims to embed in the token.
     */
    public function createCustomToken(string $uid, array $claims = []): string
    {
        $path = config('services.firebase.service_account_path');

        if (!$path || !file_exists($path)) {
            throw new \RuntimeException('Firebase service account file not found. Set FIREBASE_SERVICE_ACCOUNT_PATH or place it at storage/app/secrets/firebase-service-account.json.');
        }

        $serviceAccount = json_decode(file_get_contents($path), true);

        $clientEmail = $serviceAccount['client_email'] ?? null;
        $privateKey = $serviceAccount['private_key'] ?? null;

        if (!$clientEmail || !$privateKey) {
            throw new \RuntimeException('Invalid Firebase service account file: missing client_email or private_key.');
        }

        $now = time();

        $payload = [
            'iss' => $clientEmail,
            'sub' => $clientEmail,
            'aud' => 'https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit',
            'iat' => $now,
            'exp' => $now + 3600,
            'uid' => $uid,
        ];

        if (!empty($claims)) {
            $payload['claims'] = $claims;
        }

        return JWT::encode($payload, $privateKey, 'RS256');
    }

    /**
     * Create a Firebase Authentication user with email/password.
     *
     * @return array{uid:string,email:string}
     */
    public function createUser(string $email, string $password, ?string $displayName = null): array
    {
        $apiKey = config('services.firebase.api_key');

        if (empty($apiKey)) {
            throw new \RuntimeException('Firebase API key is not configured. Set FIREBASE_API_KEY in .env.');
        }

        $payload = [
            'email' => $email,
            'password' => $password,
            'returnSecureToken' => false,
        ];

        if (!empty($displayName)) {
            $payload['displayName'] = $displayName;
        }

        $response = Http::timeout(15)
            ->withOptions(['verify' => false]) // Disable SSL verification for local development
            ->post(
                "https://identitytoolkit.googleapis.com/v1/accounts:signUp?key={$apiKey}",
                $payload
            );

        if (!$response->successful()) {
            $message = data_get($response->json(), 'error.message') ?? 'Firebase user creation failed.';
            throw new \RuntimeException($message);
        }

        $uid = data_get($response->json(), 'localId');
        $createdEmail = data_get($response->json(), 'email', $email);

        if (empty($uid)) {
            throw new \RuntimeException('Firebase did not return a user UID.');
        }

        return [
            'uid' => $uid,
            'email' => $createdEmail,
        ];
    }

    /**
     * Send a password reset email to the user.
     */
    public function sendPasswordResetEmail(string $email): void
    {
        $apiKey = config('services.firebase.api_key');

        if (empty($apiKey)) {
            throw new \RuntimeException('Firebase API key is not configured. Set FIREBASE_API_KEY in .env.');
        }

        $response = Http::timeout(15)
            ->withOptions(['verify' => false])
            ->post(
                "https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key={$apiKey}",
                [
                    'requestType' => 'PASSWORD_RESET',
                    'email' => $email,
                ]
            );

        if (!$response->successful()) {
            $message = data_get($response->json(), 'error.message') ?? 'Failed to send password reset email.';
            throw new \RuntimeException($message);
        }
    }

    /**
     * Update Firebase user password (admin operation - setting an
     * arbitrary user's password isn't something a bare API key is
     * authorized to do; it needs the service account's admin token).
     */
    public function updatePassword(string $uid, string $newPassword): void
    {
        $accessToken = $this->getAccessToken();

        $response = Http::timeout(15)
            ->withOptions(['verify' => false])
            ->withToken($accessToken)
            ->post(
                'https://identitytoolkit.googleapis.com/v1/accounts:update',
                [
                    'localId' => $uid,
                    'password' => $newPassword,
                    'returnSecureToken' => false,
                ]
            );

        if (!$response->successful()) {
            $message = data_get($response->json(), 'error.message') ?? 'Failed to update Firebase password.';
            throw new \RuntimeException($message);
        }
    }

    /**
     * Delete a Firebase user by UID
     * Requires Firebase Admin SDK setup with service account credentials
     */
    public function deleteUser(string $uid): bool
    {
        try {
            // Check if Firebase Admin SDK is available
            if (!class_exists('Kreait\Firebase\Factory')) {
                \Log::warning('Firebase Admin SDK not available for user deletion', [
                    'uid' => $uid,
                    'note' => 'Install kreait/firebase-php to enable Firebase user deletion',
                ]);
                return true; // Don't fail the response, but warn admin
            }

            // If you have Firebase service account credentials, use this:
            // This is a placeholder for when credentials are set up
            \Log::info('Firebase user deletion not yet configured', [
                'uid' => $uid,
                'action' => 'Please manually delete Firebase user or set up Admin SDK',
            ]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to delete Firebase user', [
                'uid' => $uid,
                'message' => $e->getMessage(),
            ]);
            return true; // Don't fail - database deletion is more important
        }
    }

    /**
     * Delete a Firebase user by email
     */
    public function deleteUserByEmail(string $email): bool
    {
        try {
            \Log::info('Firebase user email deletion not yet configured', [
                'email' => $email,
                'action' => 'Please manually delete Firebase user or set up Admin SDK',
            ]);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to find/delete Firebase user by email', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);
            return true;
        }
    }
}

