<?php

namespace App\Http\Controllers;

use App\Models\Cubiculo;
use Illuminate\Http\Request;

class CubiculosController extends Controller
{
    /**
     * Mostrar todos los cubículos
     */
    public function index()
    {
        $cubiculos = Cubiculo::all();
        return response()->json($cubiculos);
    }

    /**
     * Crear nuevo cubículo
     */
    public function store(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|max:10|unique:cubiculos',
            'nombre' => 'nullable|string|max:255',
            'tipo' => 'required|in:consulta,procedimientos,emergencia',
            'equipamiento' => 'nullable|string',
            'estado' => 'in:disponible,ocupado,mantenimiento',
            'capacidad' => 'integer|min:1'
        ]);

        $cubiculo = Cubiculo::create($request->all());
        return response()->json($cubiculo, 201);
    }

    /**
     * Mostrar cubículo específico
     */
    public function show($id)
    {
        $cubiculo = Cubiculo::with('citas')->findOrFail($id);
        return response()->json($cubiculo);
    }

    /**
     * Actualizar cubículo
     */
    public function update(Request $request, $id)
    {
        $cubiculo = Cubiculo::findOrFail($id);
        
        $request->validate([
            'numero' => 'string|max:10|unique:cubiculos,numero,' . $id,
            'nombre' => 'nullable|string|max:255',
            'tipo' => 'in:consulta,procedimientos,emergencia',
            'equipamiento' => 'nullable|string',
            'estado' => 'in:disponible,ocupado,mantenimiento',
            'capacidad' => 'integer|min:1'
        ]);

        $cubiculo->update($request->all());
        return response()->json($cubiculo);
    }

    /**
     * Eliminar cubículo
     */
    public function destroy($id)
    {
        $cubiculo = Cubiculo::findOrFail($id);
        $cubiculo->delete();
        return response()->json(['message' => 'Cubículo eliminado correctamente']);
    }

    /**
     * Obtener cubículos disponibles
     */
    public function disponibles()
    {
        $cubiculos = Cubiculo::disponibles()->get();
        return response()->json($cubiculos);
    }

    /**
     * Obtener cubículos por tipo
     */
    public function porTipo($tipo)
    {
        $cubiculos = Cubiculo::porTipo($tipo)->get();
        return response()->json($cubiculos);
    }
}
