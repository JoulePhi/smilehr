<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Check if API key is provided for optional auth endpoints
        if ($request->bearerToken()) {
            try {
                Auth::guard('sanctum')->authenticate();
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid authentication token',
                ], 401);
            }
        }

        return $next($request);
    }
}