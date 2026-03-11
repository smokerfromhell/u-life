<?php

use App\Http\Controllers\CharacterController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
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
});
