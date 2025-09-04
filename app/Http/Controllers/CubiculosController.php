<?php

namespace App\Http\Controllers;

use App\Models\Cubiculo;
use Illuminate\Http\Request;
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cubiculo;

class CubiculosController extends Controller
{
    public function index()
    {
        return response()->json(Cubiculo::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero'       => 'required|string|max:10|unique:cubiculos,numero',
            'nombre'       => 'nullable|string|max:255',
            'tipo'         => 'required|in:consulta,procedimientos,emergencia',
            'equipamiento' => 'nullable|string',
            'estado'       => 'nullable|in:disponible,ocupado,mantenimiento',
            'capacidad'    => 'nullable|integer|min:1'
        ]);

        $cubiculo = Cubiculo::create($validated);
        return response()->json($cubiculo, 201);
    }

    public function show($id)
    {
        // Si no necesitas las citas, puedes quitar el with('citas')
        $cubiculo = Cubiculo::with('citas')->findOrFail($id);
        return response()->json($cubiculo);
    }

    public function update(Request $request, $id)
    {
        $cubiculo  = Cubiculo::findOrFail($id);

        $validated = $request->validate([
            'numero'       => 'sometimes|string|max:10|unique:cubiculos,numero,' . $id,
            'nombre'       => 'sometimes|nullable|string|max:255',
            'tipo'         => 'sometimes|in:consulta,procedimientos,emergencia',
            'equipamiento' => 'sometimes|nullable|string',
            'estado'       => 'sometimes|in:disponible,ocupado,mantenimiento',
            'capacidad'    => 'sometimes|integer|min:1'
        ]);

        $cubiculo->update($validated);
        return response()->json($cubiculo);
    }

    public function destroy($id)
    {
        $cubiculo = Cubiculo::findOrFail($id);
        $cubiculo->delete();

        return response()->json(['message' => 'Cubículo eliminado correctamente']);
    }

    public function disponibles()
    {
        return response()->json(Cubiculo::disponibles()->get());
    }

    public function porTipo($tipo)
    {
        return response()->json(Cubiculo::porTipo($tipo)->get());
    }
}