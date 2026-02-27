<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get current user profile
     */
    public function profile(Request $request)
    {
        $user = $request->auth_user;
        $planDefinition = \App\Models\User::getPlanDefinition($user->subscription_plan ?? 'free');

        return response()->json([
            'id' => $user->id,
            'firebase_uid' => $user->firebase_uid,
            'email' => $user->email,
            'name' => $user->name,
            'phone' => $user->phone,
            'subscription' => [
                'plan' => $user->subscription_plan ?? 'free',
                'cycle' => $user->subscription_cycle,
                'book_limit' => $user->effectiveBookLimit(),
                'customer_limit' => $user->effectiveCustomerLimit(),
                'show_ads' => $user->effectiveShowAds(),
                'started_at' => optional($user->subscription_started_at)->toIso8601String(),
                'expires_at' => optional($user->subscription_expires_at)->toIso8601String(),
            ],
            'admob' => [
                'android_app_id' => env('ADMOB_ANDROID_APP_ID', ''),
                'ios_app_id' => env('ADMOB_IOS_APP_ID', ''),
                'android_banner_unit_id' => env('ADMOB_ANDROID_BANNER_UNIT_ID', ''),
                'ios_banner_unit_id' => env('ADMOB_IOS_BANNER_UNIT_ID', ''),
            ],
            'linked_providers' => $user->linked_providers ?? [],
            'created_at' => $user->created_at->toIso8601String(),
            'updated_at' => $user->updated_at->toIso8601String(),
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->auth_user;
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);
        
        $user->update($validated);
        
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'firebase_uid' => $user->firebase_uid,
                'email' => $user->email,
                'name' => $user->name,
                'phone' => $user->phone,
                'linked_providers' => $user->linked_providers ?? [],
                'created_at' => $user->created_at->toIso8601String(),
                'updated_at' => $user->updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get package catalog for app display.
     */
    public function packageCatalog()
    {
        $definitions = \App\Models\User::PLAN_DEFINITIONS;

        return response()->json([
            'payment_method' => 'bKash / Nagad / Rocket (Manual confirmation by admin)',
            'packages' => [
                [
                    'key' => 'free',
                    'name' => $definitions['free']['label'],
                    'book_limit' => $definitions['free']['book_limit'],
                    'customer_limit' => $definitions['free']['customer_limit'],
                    'show_ads' => $definitions['free']['show_ads'],
                    'yearly_price_bdt' => $definitions['free']['yearly_price_bdt'],
                    'monthly_price_bdt' => $definitions['free']['monthly_price_bdt'],
                ],
                [
                    'key' => 'premium',
                    'name' => $definitions['premium']['label'],
                    'book_limit' => $definitions['premium']['book_limit'],
                    'customer_limit' => $definitions['premium']['customer_limit'],
                    'show_ads' => $definitions['premium']['show_ads'],
                    'yearly_price_bdt' => $definitions['premium']['yearly_price_bdt'],
                    'monthly_price_bdt' => $definitions['premium']['monthly_price_bdt'],
                ],
                [
                    'key' => 'business',
                    'name' => $definitions['business']['label'],
                    'book_limit' => $definitions['business']['book_limit'],
                    'customer_limit' => $definitions['business']['customer_limit'],
                    'show_ads' => $definitions['business']['show_ads'],
                    'yearly_price_bdt' => $definitions['business']['yearly_price_bdt'],
                    'monthly_price_bdt' => $definitions['business']['monthly_price_bdt'],
                ],
            ],
        ]);
    }

    /**
     * Get user statistics
     */
    public function stats(Request $request)
    {
        $user = $request->auth_user;
        
        return response()->json([
            'total_books' => $user->books()->count(),
            'total_clients' => $user->clients()->count(),
            'total_transactions' => $user->transactions()->count(),
            'total_amount_in' => $user->transactions()->where('type', 'in')->sum('amount'),
            'total_amount_out' => $user->transactions()->where('type', 'out')->sum('amount'),
        ]);
    }

    /**
     * Get linked authentication providers
     */
    public function linkedProviders(Request $request)
    {
        $user = $request->auth_user;
        
        return response()->json([
            'email' => $user->email,
            'linked_providers' => $user->linked_providers ?? [],
        ]);
    }

    /**
     * Delete user account and all associated data
     * WARNING: This is irreversible
     */
    public function deleteAccount(Request $request, FirebaseService $firebaseService)
    {
        $user = $request->auth_user;
        
        try {
            // Start database transaction
            \DB::beginTransaction();
            
            // Delete all user's data in cascade order
            // Delete transactions first
            $user->transactions()->delete();
            
            // Delete books
            $user->books()->delete();
            
            // Delete clients
            $user->clients()->delete();
            
            // Store Firebase UID before deleting user record
            $firebaseUid = $user->firebase_uid;
            $email = $user->email;
            
            // Delete the user account itself
            $user->delete();
            
            \DB::commit();
            
            // Delete from Firebase (after successful database deletion)
            // This prevents data inconsistency if database deletion fails
            try {
                $firebaseService->deleteUser($firebaseUid);
                \Log::info('Firebase user deleted', ['uid' => $firebaseUid, 'email' => $email]);
            } catch (\Exception $e) {
                \Log::warning('Firebase user deletion failed', [
                    'uid' => $firebaseUid,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the response - database deletion was successful
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully. All your data has been removed.',
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('Account deletion failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account. Please try again later.',
            ], 500);
        }
    }
}
