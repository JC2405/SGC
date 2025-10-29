<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Mail\WelcomeEmail;

class UsuariosController extends Controller
{   
    public function index()
    {
        $usuario = Usuario::all();
        return response()->json(["data" => $usuario]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:4',
            'telefono' => 'required|string|max:20',
            'fecha_nacimiento' => 'required|date',
            'eps_id' => 'nullable|exists:eps,id',
         
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $usuario = Usuario::create($request->all());
        $usuario->load('eps');
        return response()->json($usuario, 201); 
    }

    public function show(string $id)
    {
        $usuario = Usuario::with('eps')->find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        return response()->json($usuario);
    }

    public function update(Request $request, string $id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:255',
            'apellido' => 'string|max:255',
            'email' => 'email|unique:usuarios,email,' . $usuario->id,
            'telefono' => 'string|max:20',
            'fecha_nacimiento' => 'date',
            'eps_id' => 'nullable|exists:eps,id',
             
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $usuario->update($request->all());
        $usuario->load('eps');
        return response()->json($usuario);
    }

    public function destroy(string $id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }
    
public function crearUsuarioPaciente(Request $request)
{
    try {
        $validated = Validator::make($request->all(), [
        'nombre' => 'required|string|max:255',
        'apellido' => 'required|string|max:255',
        'documento_identidad' => 'required|string|max:255|unique:usuarios,documento_identidad',
        'email' => 'required|string|email|max:255|unique:usuarios,email',
        'password' => 'required|string|min:8',
        'telefono' => 'nullable|string|max:20',
        'fecha_nacimiento' => 'required|date',
        'eps_id' => 'nullable|exists:eps,id',
        'rol_id' => 'required|exists:roles,id',
        ]);

        if ($validated->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Errores de validación",
                "errors" => $validated->errors()
            ], 422);
        }

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'documento_identidad' => $request->documento_identidad,
            'eps_id' => $request->eps_id,
            'rol_id' => $request->rol_id,
        ]);

        // Enviar correo de bienvenida
        $userType = $usuario->rol->role ?? 'paciente'; // Usando el campo 'role' del modelo Roles
        Mail::to($usuario->email)->send(new WelcomeEmail($usuario, $userType));

        return response()->json([
            "success" => true,
            "message" => "Usuario agregado correctamente",
            "usuario" => $usuario
        ], 201);

    } catch (\Illuminate\Database\QueryException $e) {
        return response()->json([
            "success" => false,
            "message" => "Error en la base de datos",
            "error" => $e->getMessage()
        ], 500);
    } catch (\Exception $e) {
        return response()->json([
            "success" => false,
            "message" => "Error inesperado",
            "error" => $e->getMessage()
        ], 500);
        }
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


}

