<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\DashboardController;

Route::middleware('apikey')->group(function () {
    Route::post('/ingest', [SensorController::class, 'ingest']);
    Route::get('/sensors', [SensorController::class, 'index']);

    Route::post('/command/send', [CommandController::class, 'send']);
    Route::get('/command/get', [CommandController::class, 'get']);
    Route::post('/command/done', [CommandController::class, 'done']);

    Route::get('/dashboard/data', [DashboardController::class, 'data']);
    Route::get('/dashboard/log', [DashboardController::class, 'log']);
});