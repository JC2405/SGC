<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Especialidad; // Corregido nombre del modelo

class EspecialidadesController extends Controller
{
    public function index()
    {
        try {
            $especialidades = Especialidad::all();
            return response()->json([
                'success' => true,
                'data' => $especialidades
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener especialidades: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255|unique:especialidades,nombre',
                'descripcion' => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $especialidad = Especialidad::create($request->all());
            return response()->json([
                'success' => true,
                'data' => $especialidad,
                'message' => 'Especialidad creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear especialidad: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $especialidad = Especialidad::find($id);
            if (!$especialidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Especialidad no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $especialidad
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener especialidad: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $especialidad = Especialidad::find($id);
            if (!$especialidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Especialidad no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255|unique:especialidades,nombre,' . $id,
                'descripcion' => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $especialidad->update($request->all());
            return response()->json([
                'success' => true,
                'data' => $especialidad,
                'message' => 'Especialidad actualizada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar especialidad: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $especialidad = Especialidad::find($id);
            if (!$especialidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Especialidad no encontrada'
                ], 404);
            }

            if ($especialidad->doctores()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la especialidad porque tiene doctores asociados'
                ], 409);
            }

            $especialidad->delete();
            return response()->json([
                'success' => true,
                'message' => 'Especialidad eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar especialidad: ' . $e->getMessage()
            ], 500);
        }
    }
}
