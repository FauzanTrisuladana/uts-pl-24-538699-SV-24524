<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/**
 * Status check route.
 * Get /api/status
 */
Route::get('/status', function () {
    return response()->json(['status' => 'ok']);
});

/**
 * Route untuk autentikasi.
 * /api/auth
 */
Route::prefix('auth')
    ->controller(AuthController::class)
    ->group(function () {
        Route::post('/login', 'login');
        Route::post('/logout', 'logout')->middleware('auth:sanctum');
    });

Route::middleware('auth:sanctum')->group(function () {

    /**
     * Profile routes
     * Get /api/profile/me -> get profile user yang sedang login
     * Put /api/profile/update -> update profile user yang sedang login
     * Put /api/profile/update-password -> update password user yang sedang login
     * Delete /api/profile/delete -> delete akun user yang sedang login
     */
    Route::prefix('profile')->controller(ProfileController::class)->group(function () {
        Route::get('/me', 'me');
        Route::put('/update', 'update');
        Route::put('/update-password', 'updatePassword');
        Route::delete('/delete', 'delete');
    });
});
