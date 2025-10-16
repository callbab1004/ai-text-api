<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\TextController;

// 보호 라우트 (API 키 필요)
Route::prefix('v1')
    ->middleware(['api_key', 'throttle:api_per_key'])
    ->group(function () {
    Route::get('/ping', fn() => response()->json(['pong' => true, 'ts' => now()->toIso8601String()]));
    Route::post('/text/transform', [TextController::class, 'transform']);
});

// Health check
Route::get("/v1/health", fn() => response()->json(["ok" => true, "ts" => now()->toIso8601String()]));
