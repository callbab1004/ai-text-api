<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-KEY');

        if (!$key) {
            return response()->json([
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Missing X-API-KEY']
            ], 401);
        }

        $row = DB::table('api_keys')->where('key', $key)->where('is_active', true)->first();
        if (!$row) {
            return response()->json([
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Invalid or inactive API key']
            ], 401);
        }

        // downstream에서 접근할 수 있게 저장
        $request->attributes->set('api_key_id', $row->id);

        // best-effort로 마지막 사용시각 업데이트
        DB::table('api_keys')->where('id', $row->id)->update(['last_used_at' => now()]);

        return $next($request);
    }
}
