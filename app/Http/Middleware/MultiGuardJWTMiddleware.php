<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class MultiGuardJWTMiddleware
{
    public function handle(Request $request, Closure $next)
    {
      $guards = ['api_doctores','api_usuarios','api_admin'];

    if ($g = $request->header('X-User-Guard')) {
        if (in_array($g, $guards, true)) {
            array_unshift($guards, $g);
            $guards = array_values(array_unique($guards));
        }
    }

    try {
        $token = \Tymon\JWTAuth\Facades\JWTAuth::getToken();
        if (!$token) return response()->json(['Error' => 'Falta el token (Authorization: Bearer)'], 401);
    } catch (JWTException $e) {
        return response()->json(['Error' => 'Token ausente o mal formado'], 401);
    }

    foreach ($guards as $guard) {
        try {
            $user = auth($guard)->setToken($token)->user();
            if ($user) {
                $request->merge(['jwt_user' => $user, 'jwt_guard' => $guard]);
                auth()->shouldUse($guard);
                return $next($request);
            }
        } catch (\Throwable $e) { /* probar siguiente guard */ }
    }
    return response()->json(['Error' => 'Token inválido'], 401);
}
    
}