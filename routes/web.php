<?php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// API routes - must be defined before the catch-all route
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleAuth']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Catch-all route for SPA - must be last
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');