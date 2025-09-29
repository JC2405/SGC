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
      // Agregar headers CORS
      $response = $next($request);

      $response->headers->set('Access-Control-Allow-Origin', '*');
      $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
      $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-User-Guard');

      // Manejar preflight requests
      if ($request->getMethod() === 'OPTIONS') {
          return response('', 200)->withHeaders([
              'Access-Control-Allow-Origin' => '*',
              'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
              'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-User-Guard',
          ]);
      }

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
            // Configurar el guard para usar el token
            JWTAuth::setToken($token);
            $user = JWTAuth::authenticate();

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