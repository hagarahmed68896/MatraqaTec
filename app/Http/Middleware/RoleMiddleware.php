<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            \Log::info('RoleMiddleware: User not found', [
                'has_token' => $request->bearerToken() ? 'yes' : 'no',
                'url' => $request->fullUrl(),
            ]);
            return response()->json(['status' => false, 'message' => 'Unauthorized [DEBUG-ROLE]'], 401);
        }

        if (!in_array($request->user()->type, $roles)) {
            return response()->json([
                'status' => false, 
                'message' => 'Forbidden: This action is restricted to ' . implode(' or ', $roles) . ' accounts.'
            ], 403);
        }

        return $next($request);
    }
}
