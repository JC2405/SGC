<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


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
            'telefono' => 'required|string|max:20',
            'fecha_nacimiento' => 'required|date',
            'eps_id' => 'nullable|exists:eps,id',
            'numero_afiliacion' => 'nullable|string|max:50'
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
            'numero_afiliacion' => 'nullable|string|max:50'
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
}
