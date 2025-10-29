<?php

namespace App\Http\Controllers;

use App\Models\Cubiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CubiculosController extends Controller
{
    public function index()
    {
        Log::info('CubiculosController@index: Iniciando listado de cubículos');
        try {
            $cubiculos = Cubiculo::all();
            Log::info('CubiculosController@index: Cubículos obtenidos', ['count' => $cubiculos->count()]);
            return response()->json($cubiculos);
        } catch (\Exception $e) {
            Log::error('CubiculosController@index: Error obteniendo cubículos', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    public function store(Request $request)
    {
        Log::info('Store method called', ['data' => $request->all()]);
        $request->validate([
            'numero' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'equipamiento' => 'nullable|string',
            'estado' => 'required|string|max:255',
            'capacidad' => 'required|integer|min:1',
        ]);
        $cubiculo = Cubiculo::create($request->all());
        return response()->json($cubiculo, 201);
    }

    public function show($id)
    {
        $cubiculo = Cubiculo::find($id);
        if (!$cubiculo) {
            return response()->json(['message' => 'Cubiculo not found'], 404);
        }
        return response()->json($cubiculo);
    }

    public function update(Request $request, $id)
    {
        $cubiculo = Cubiculo::find($id);
        if (!$cubiculo) {
            return response()->json(['message' => 'Cubiculo not found'], 404);
        }
        $cubiculo->update($request->all());
        return response()->json($cubiculo);
    }

    public function destroy($id)
    {
        $cubiculo = Cubiculo::find($id);
        if (!$cubiculo) {
            return response()->json(['message' => 'Cubiculo not found'], 404);
        }
        $cubiculo->delete();
        return response()->json(['message' => 'Cubiculo deleted']);
    }

    public function disponibles()
    {
        return response()->json(Cubiculo::disponible()->get());
    }

    public function porTipo($tipo)
    {
        return response()->json(Cubiculo::porTipo($tipo)->get());
    }
}