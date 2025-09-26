<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function crearUsuario(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name'     => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:4',
            'rol'      => 'required|string'
        ]);

        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors()]);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'rol'      => $request->rol
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario agregado correctamente',
            'user'    => $user
        ]);
    }



    public function login(Request $request)
{
    $v = Validator::make($request->all(), [
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);
    if ($v->fails()) {
        return response()->json(['message'=>'Errores de validación','errors'=>$v->errors()], 422);
    }

    $credentials = $v->validated();
    $guards = ['api_admin','api_doctores','api_usuarios'];
    $log = [];

    foreach ($guards as $guard) {
        try {
            $ok = Auth::guard($guard)->validate($credentials); // no genera token, solo valida
            $log[] = [$guard, 'validate' => $ok];

            if (! $ok) { continue; }

            if ($token = Auth::guard($guard)->attempt($credentials)) {
                $user = Auth::guard($guard)->user();

                $expectedRole = match ($guard) {
                    'api_admin' => 'admin',
                    'api_doctores' => 'doctor',
                    'api_usuarios' => 'paciente',
                };

                if (method_exists($user, 'rol')) { $user->load('rol'); }
                $userRole = $user->rol->role ?? null;

                if ($userRole !== $expectedRole) {
                    $log[] = [$guard, 'role_mismatch' => ['have'=>$userRole, 'need'=>$expectedRole]];
                    continue;
                }

                return response()->json([
                    'access_token' => $token,
                    'guard'        => $guard,
                    'user'         => $user,
                ]);
            } else {
                $log[] = [$guard, 'attempt' => false];
            }
        } catch (\Throwable $e) {
            $log[] = [$guard, 'error' => $e->getMessage()];
        }
    }

    // Dev only: inspeccionar por qué
    return response()->json(['message' => 'Credenciales inválidas', 'debug' => $log], 401);
}
    // public function login(Request $request)
    // {
    //     $v = Validator::make($request->all(), [
    //         'email'    => 'required|email',
    //         'password' => 'required|string',
    //     ]);

    //     if ($v->fails()) {
    //         return response()->json([
    //             'message' => 'Errores de validación',
    //             'errors'  => $v->errors(),
    //         ], 422);
    //     }

    //     $credentials = $v->validated();

    //     // AuthController@login
    //     $guards = ['api_admin', 'api_doctores', 'api_usuarios'];

    //     foreach ($guards as $guard) {
    //         if ($token = Auth::guard($guard)->attempt($credentials)) {
    //             $user = Auth::guard($guard)->user();

    //             $expectedRole = match ($guard) {
    //                 'api_admin'     => 'admin',
    //                 'api_doctores'  => 'doctor',
    //                 'api_usuarios'  => 'paciente',
    //                 default         => null,
    //             };

    //             if (method_exists($user, 'rol')) {
    //                 $user->load('rol');
    //             }

    //             $userRole = $user->rol->rol ?? null;
    //             if ($userRole !== $expectedRole) {
    //                 continue;
    //             }

    //             return response()->json([
    //                 'access_token' => $token,
    //                 'guard'        => $guard,
    //                 'user'         => $user,
    //             ]);
    //         }
    //     }
    //     return response()->json(['message' => 'Credenciales inválidas'], 401);
    // }



    public function logout(Request $request)
    {
        try {
            $guards = ['api_admin', 'api_doctores', 'api_usuarios'];

            foreach ($guards as $guard) {
                if (Auth::guard($guard)->check()) {
                    Auth::guard($guard)->logout();
                    break;
                }
            }

            return response()->json([
                'message' => 'Sesión cerrada correctamente',
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
