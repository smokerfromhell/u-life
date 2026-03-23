<?php

use App\Http\Controllers\CharacterController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\Admin\SharedDecisionLogController;
use App\Http\Controllers\ProfessionalAccountRequestController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    // Guest mode
    Route::post('/guest/start', [GuestController::class, 'start']);
    Route::post('/professional-account-requests', [ProfessionalAccountRequestController::class, 'store']);
    
    // Feedback - public, no auth required
    Route::post('/feedback', [FeedbackController::class, 'store']);

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
        Route::post('/characters/{character}/end-day', [EventController::class, 'endDay']);
        Route::get('/characters/{character}/life-summary', [EventController::class, 'getLifeSummary']);
        Route::post('/characters/{character}/reincarnate', [EventController::class, 'reincarnate']);
        Route::post('/characters/{character}/set-profession', [EventController::class, 'setProfession']);
        Route::post('/characters/{character}/redraw-events', [EventController::class, 'redrawEvents']);
        Route::post('/characters/{character}/redraw-event-type', [EventController::class, 'redrawEventType']);
        
        // Mini-game routes
        Route::get('/characters/{character}/mini-game', [EventController::class, 'getMiniGame']);
        Route::post('/characters/{character}/mini-game', [EventController::class, 'submitMiniGame']);
        
        // Luck/Random events routes
        Route::get('/characters/{character}/luck-event', [EventController::class, 'getLuckEvent']);
        Route::post('/characters/{character}/luck-event', [EventController::class, 'applyLuckEvent']);
        
        // Achievements routes
        Route::get('/characters/{character}/achievements', [EventController::class, 'getAchievements']);
        Route::post('/characters/{character}/achievements/check', [EventController::class, 'checkAchievements']);
        Route::get('/characters/{character}/decision-logs', [CharacterController::class, 'decisionLogs']);
        Route::get('/characters/{character}/analytics', [CharacterController::class, 'analytics']);

        // Admin analytics (RBAC via role/permission)
        Route::get('/admin/shared-decision-logs', [SharedDecisionLogController::class, 'index']);
        
        // Professional analytics
        Route::get('/professional/analytics', [AnalyticsController::class, 'dashboard']);
    });
});

