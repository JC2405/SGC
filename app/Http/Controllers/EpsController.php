<?php

namespace App\Http\Controllers;

use App\Models\Eps;
use Illuminate\Http\Request;

class EpsController extends Controller
{
    /**
     * Mostrar todas las EPS
     */
    public function index()
    {
        $eps = Eps::activas()->get();
        return response()->json($eps);
    }

    /**
     * Crear nueva EPS
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:eps',
            'codigo' => 'required|string|max:10|unique:eps',
            'nit' => 'required|string|max:20|unique:eps',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
            'estado' => 'in:activa,inactiva'
        ]);

        $eps = Eps::create($request->all());
        return response()->json($eps, 201);
    }

    /**
     * Mostrar EPS específica
     */
    public function show($id)
    {
        $eps = Eps::with('usuarios')->findOrFail($id);
        return response()->json($eps);
    }

    /**
     * Actualizar EPS
     */
    public function update(Request $request, $id)
    {
        $eps = Eps::findOrFail($id);
        
        $request->validate([
            'nombre' => 'string|max:255|unique:eps,nombre,' . $id,
            'codigo' => 'string|max:10|unique:eps,codigo,' . $id,
            'nit' => 'string|max:20|unique:eps,nit,' . $id,
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
            'estado' => 'in:activa,inactiva'
        ]);

        $eps->update($request->all());
        return response()->json($eps);
    }

    /**
     * Eliminar EPS
     */
    public function destroy($id)
    {
        $eps = Eps::findOrFail($id);
        $eps->delete();
        return response()->json(['message' => 'EPS eliminada correctamente']);
    }
}
