<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 키별 레이트리밋: X-API-KEY 기준, 없으면 'guest'로 묶음
        RateLimiter::for('api_per_key', function (Request $request) {
            $apiKey = $request->header('X-API-KEY', 'guest');
            $steady = (int) env('RATE_PER_MIN', 60);
            
            return [
                Limit::perMinute($steady)->by($apiKey),

                // 응용
                // 기본: 1분에 steady 건
                # Limit::perMinutes(1, $steady)->by($key),

                // 버스트 모드: 5분에 3배까지(짧은 순간 몰아치기 허용하되,
                // 5분 누적은 과도하게 못 넘기도록 뚜렷한 상한)
                # Limit::perMinutes(5, $steady * 3)->by($key),
            ];
        });
    }
}
