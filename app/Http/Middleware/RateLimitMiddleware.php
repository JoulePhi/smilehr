<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $limit
     * @param  string  $window
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $limit = '60', string $window = '1'): mixed
    {
        $key = sprintf('rate_limit:%s:%s',
            $request->ip(),
            $request->route()->getName()
        );

        $current = Redis::connection('cache')->get($key) ?? 0;

        if ($current >= (int) $limit) {
            return response()->json([
                'status' => false,
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $window * 60,
            ], 429);
        }

        Redis::connection('cache')->setex(
            $key,
            (int) $window * 60,
            (int) $current + 1
        );

        return $next($request);
    }
}