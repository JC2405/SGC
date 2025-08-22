<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\doctores;

class DoctoresController extends Controller
{
    public function index()
    {
        $doctores = doctores::with('especialidad')->get();
        return response()->json($doctores);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|unique:doctores,email',
            'telefono' => 'nullable|string|max:20',
            'especialidad_id' => 'required|exists:especialidades,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $doctor = doctores::create($request->all());
        $doctor->load('especialidad');
        return response()->json($doctor, 201);
    }

    public function show($id)
    {
        $doctor = doctores::with('especialidad')->find($id);
        if (!$doctor) {
            return response()->json(['message' => 'Doctor no encontrado'], 404);
        }
        return response()->json($doctor);
    }

    public function update(Request $request, $id)
    {
        $doctor = doctores::find($id);
        if (!$doctor) {
            return response()->json(['message' => 'Doctor no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:255',
            'apellido' => 'string|max:255',
            'email' => 'email|unique:doctores,email,' . $doctor->id,
            'telefono' => 'nullable|string|max:20',
            'especialidad_id' => 'exists:especialidades,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $doctor->update($request->all());
        $doctor->load('especialidad');
        return response()->json($doctor);
    }

    public function destroy($id)
    {
        $doctor = doctores::find($id);
        if (!$doctor) {
            return response()->json(['message' => 'Doctor no encontrado'], 404);
        }

        $doctor->delete();
        return response()->json(['message' => 'Doctor eliminado']);
    }

    // Método adicional para obtener doctores por especialidad
    public function porEspecialidad($especialidad_id)
    {
        $doctores = doctores::where('especialidad_id', $especialidad_id)
                           ->with('especialidad')
                           ->get();
        return response()->json($doctores);
    }
}
