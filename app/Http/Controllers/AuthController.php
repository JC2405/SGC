<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password' => Hash::make($request->password)
        ]);

          $token = Auth::guard('api')->login($user);


        return response()->json(['user' => $user, 'token' => $token]);
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
