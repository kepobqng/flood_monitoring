<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardLayoutController;
use App\Http\Controllers\WorkerStatusController;
use App\Http\Controllers\SirenAudioController;

Route::middleware('apikey')->group(function () {
    Route::post('/ingest', [SensorController::class, 'ingest']);
    Route::get('/sensors', [SensorController::class, 'index']);

    Route::post('/command/send', [CommandController::class, 'send']);
    Route::get('/command/get', [CommandController::class, 'get']);
    Route::post('/command/done', [CommandController::class, 'done']);
    Route::post('/status/update', [WorkerStatusController::class, 'update']);

    Route::get('/dashboard/data', [DashboardController::class, 'data']);
    Route::get('/dashboard/log', [DashboardController::class, 'log']);

    Route::get('/dashboard/layout', [DashboardLayoutController::class, 'index']);
    Route::post('/dashboard/layout', [DashboardLayoutController::class, 'store']);
    Route::delete('/dashboard/layout', [DashboardLayoutController::class, 'destroy']);

    Route::get('/dashboard/siren-audio', [SirenAudioController::class, 'show']);
    Route::post('/dashboard/siren-audio', [SirenAudioController::class, 'store']);
    Route::delete('/dashboard/siren-audio', [SirenAudioController::class, 'destroy']);
});