<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function crearUsuario(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:4',
            'rol' => 'required|string'
        ]);

        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors()]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol
        ]);

        return response()->json(['success' => true, 'message' => 'Usuario agregado correctamente', 'user' => $user]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
    
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }
    
        return $this->respondWithToken($token);
    }



    public function me()
    {
        return response()->json(auth()->user());
    }


    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Logout exitoso']);
    }


  // Refrescar token
    public function refresh()
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

   protected function respondWithToken($token)
    {
    return response()->json([
        'access_token' => $token,
        'token_type'   => 'bearer',
        'expires_in'   => Auth::guard('api')->factory()->getTTL() * 60
    ]);
    }

}
