<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\MetricsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', [HealthController::class, 'check'])
    ->withoutMiddleware('web');

Route::get('/metrics', [MetricsController::class, 'index'])
    ->withoutMiddleware('web');
