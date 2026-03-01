<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DiagnosticController;

// Public health check
Route::get('/', function () {
    return response()->json(['status' => 'ok', 'service' => 'Hisab Nikash API']);
});

// Public diagnostic endpoints (for debugging)
Route::post('/debug/find-user', [DiagnosticController::class, 'findUser']);

// Protected routes (require Firebase auth)
Route::middleware('firebase.auth')->group(function () {
    
    // Diagnostic endpoints (protected)
    Route::get('/debug/users', [DiagnosticController::class, 'listUsers']);
    
    // User endpoints
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::get('/user/packages', [UserController::class, 'packageCatalog']);
    Route::get('/user/stats', [UserController::class, 'stats']);
    Route::get('/user/linked-providers', [UserController::class, 'linkedProviders']);
    Route::delete('/user/account', [UserController::class, 'deleteAccount']);
    
    // Sync endpoints
    Route::post('/sync/pull', [SyncController::class, 'pull']);
    Route::post('/sync/push', [SyncController::class, 'push']);
    Route::post('/sync', [SyncController::class, 'sync']);
    
    // Books
    // Custom routes BEFORE apiResource to avoid conflict with {id} parameter
    Route::get('/books/shared', [BookController::class, 'sharedBooks']);
    Route::post('/books/{id}/share', [BookController::class, 'share']);
    Route::delete('/book-shares/{shareId}', [BookController::class, 'revokeShare']);
    
    // Main resource routes
    Route::apiResource('books', BookController::class);
    
    // Clients
    Route::apiResource('clients', ClientController::class);
    
    // Transactions
    Route::apiResource('transactions', TransactionController::class);
});
