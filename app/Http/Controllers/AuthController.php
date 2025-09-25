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

    public function login(Request $request){
            $validated = Validator::make($request->all(),[
                'email' => 'required|email',
                'password' => 'required|string|min:4'
            ]);

            if($validated->fails()){
                return response()->json(['errors' => $validated->errors()]);
            }

            $credenciales = $request->only('email','password');
            if(!$token = JWTAuth::attempt($credenciales)){
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales Invalidas'
                ]);
            }

            return response()->json(['Success' => true, 'message' => 'Bienvenido', 'Token' => $token],200);


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
