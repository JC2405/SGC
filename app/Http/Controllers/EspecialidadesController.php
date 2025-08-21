<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // 👈 IMPORTANTE
use App\Models\Especialidad; // 👈 Modelo con convención

class EspecialidadesController extends Controller
{
    public function index()
    {
        $especialidades = Especialidad::all();
        return response()->json($especialidades);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $especialidad = Especialidad::create($request->all());
        return response()->json($especialidad, 201);
    }

    public function update(Request $request, $id)
    {
        $especialidad = Especialidad::find($id);
        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $especialidad->update($request->all());
        return response()->json($especialidad);
    }

    public function show($id)
    {
        $especialidad = Especialidad::find($id);
        if ($especialidad) {
            return response()->json($especialidad);
        } else {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }
    }

    public function destroy($id) // 👈 cambié string a normal
    {
        $especialidad = Especialidad::find($id);
        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }

        $especialidad->delete();
        return response()->json(['message' => 'Especialidad eliminada']);
    }
}
