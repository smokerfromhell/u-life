<?php

use App\Http\Controllers\CharacterController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\Admin\SharedDecisionLogController;
use App\Http\Controllers\ProfessionalAccountRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    // Guest mode
    Route::post('/guest/start', [GuestController::class, 'start']);
    Route::post('/professional-account-requests', [ProfessionalAccountRequestController::class, 'store']);

    Route::middleware(['auth'])->group(function () {
        // Session info
        Route::get('/me', [GuestController::class, 'me']);

        // Consent (guest + registered users)
        Route::post('/consent', [GuestController::class, 'setConsent']);
        Route::post('/guest/consent', [GuestController::class, 'setConsent']);

        // Guest controls
        Route::post('/guest/exit', [GuestController::class, 'exit']);

        // Character routes
        Route::post('/characters', [CharacterController::class, 'store']);
        Route::get('/characters', [CharacterController::class, 'index']);
        Route::get('/characters/{character}', [CharacterController::class, 'show']);
        Route::put('/characters/{character}', [CharacterController::class, 'update']);
        Route::post('/characters/{character}/upload-image', [CharacterController::class, 'uploadImage']);

        // Event routes
        Route::get('/characters/{character}/events', [EventController::class, 'getAvailableEvents']);
        Route::get('/characters/{character}/random-event', [EventController::class, 'getRandomEvent']);
        Route::post('/characters/{character}/apply-event', [EventController::class, 'applyEventOutcome']);
        Route::post('/characters/{character}/redraw-events', [EventController::class, 'redrawEvents']);
        Route::post('/characters/{character}/redraw-event-type', [EventController::class, 'redrawEventType']);

        // Admin analytics (RBAC via role/permission)
        Route::get('/admin/shared-decision-logs', [SharedDecisionLogController::class, 'index']);
    });
});
