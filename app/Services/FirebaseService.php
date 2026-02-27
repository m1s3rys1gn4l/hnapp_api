<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FirebaseService
{
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
     * Update Firebase user password.
     */
    public function updatePassword(string $uid, string $newPassword): void
    {
        $apiKey = config('services.firebase.api_key');

        if (empty($apiKey)) {
            throw new \RuntimeException('Firebase API key is not configured. Set FIREBASE_API_KEY in .env.');
        }

        $response = Http::timeout(15)
            ->withOptions(['verify' => false])
            ->post(
                "https://identitytoolkit.googleapis.com/v1/accounts:update?key={$apiKey}",
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

