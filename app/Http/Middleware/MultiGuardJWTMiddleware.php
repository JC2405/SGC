<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

    // Allow refresh, me, and eps/activas/list endpoints without full authentication
    if ($request->is('api/refresh') || $request->is('api/me') || $request->is('api/eps/activas/list')) {
        Log::info('🔍 DEBUG MIDDLEWARE - Allowing refresh/me/eps endpoint');
        return $next($request);
    }

    try {
        $token = \Tymon\JWTAuth\Facades\JWTAuth::getToken();
        if (!$token) {
            Log::warning('🔍 DEBUG MIDDLEWARE - Token no encontrado en headers');
            return response()->json(['Error' => 'Falta el token (Authorization: Bearer)'], 401);
        }
    } catch (JWTException $e) {
        Log::warning('🔍 DEBUG MIDDLEWARE - JWTException al obtener token', ['error' => $e->getMessage()]);
        return response()->json(['Error' => 'Token ausente o mal formado'], 401);
    }

    Log::info('🔍 DEBUG MIDDLEWARE - Token encontrado, probando guards', [
        'guards_to_try' => $guards,
        'token_preview' => substr($token, 0, 20) . '...'
    ]);

    foreach ($guards as $guard) {
          try {
              Log::info("🔍 DEBUG MIDDLEWARE - Probando guard: {$guard}");

              // Verificar si el guard puede autenticar al usuario
              if (Auth::guard($guard)->check()) {
                  $user = Auth::guard($guard)->user();
                  Log::info("🔍 DEBUG MIDDLEWARE - Usuario autenticado en guard: {$guard}", [
                      'user_id' => $user->id,
                      'user_email' => $user->email,
                      'user_rol_id' => $user->rol_id ?? 'no_rol_id'
                  ]);
                  $request->merge(['jwt_user' => $user, 'jwt_guard' => $guard]);
                  auth()->shouldUse($guard);
                  return $next($request);
              } else {
                  Log::info("🔍 DEBUG MIDDLEWARE - Guard {$guard} no pudo autenticar usuario");
              }
          } catch (\Throwable $e) {
              Log::info("🔍 DEBUG MIDDLEWARE - Error en guard {$guard}", ['error' => $e->getMessage()]);
          }
      }

    Log::warning('🔍 DEBUG MIDDLEWARE - Ningún guard pudo autenticar el token');
    return response()->json(['Error' => 'Token inválido'], 401);
}

}