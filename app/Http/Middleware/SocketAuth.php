<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SocketAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: No token provided',
                'data' => []
            ], 401);
        }

        if ($token !== env('SOCKET_TOKEN')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
                'data' => []
            ], 401);
        }

        return $next($request);
    }
}
