<?php

namespace App\Http\Controllers;

use App\Models\Horarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HorarioController extends Controller
{
    //
    public function index()
    {
        $horarios = Horarios::all();

        return response()->json(['horarios' => $horarios]);
    }

    public function store(Request $request)
    {
        // Para admin: usar el doctor_id que viene en la solicitud
        // Para doctor: usar el usuario autenticado
        $user = $request->jwt_user;

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $validated = Validator::make($request->all(), [
            'doctor_id'   => 'required|exists:doctores,id',
            'dia'         => 'required|string|max:20',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin'    => 'required|date_format:H:i|after:hora_inicio',
            'estado'      => 'required|string|in:activo,inactivo',
        ]);

        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors()], 422);
        }

        // Verificar que el horario no se solape con horarios existentes del mismo doctor
        $horarioExistente = Horarios::where('doctor_id', $request->doctor_id)
            ->where('dia', $request->dia)
            ->where(function($query) use ($request) {
                $query->where(function($q) use ($request) {
                    $q->where('hora_inicio', '<', $request->hora_fin)
                      ->where('hora_fin', '>', $request->hora_inicio);
                });
            })
            ->first();

        if ($horarioExistente) {
            return response()->json([
                'error' => 'Ya existe un horario en ese rango de tiempo para este doctor'
            ], 409);
        }

        $crearHorario = Horarios::create([
            'doctor_id'   => $request->doctor_id,
            'dia'         => $request->dia,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin'    => $request->hora_fin,
            'estado'      => $request->estado,
        ]);

        return response()->json([
            'message' => 'Horario creado correctamente',
            'success' => true,
            'horario' => $crearHorario->load('doctor')
        ]);
    }

    public function update(Request $request, $id)
    {
        $doctor = auth('apiDoctor')->user() ?? $request->jwt_user;

        // Ensure we have a valid doctor user
        if (!$doctor || !isset($doctor->id)) {
            return response()->json(['error' => 'Usuario no válido'], 401);
        }

        $horario = Horarios::where('id', $id)->where('doctor_id', $doctor->id)->first();

        if (!$horario) {
            return response()->json(['message' => "No se ha encontrado el horario o no tienes permisos para editarlo"]);
        }

        $validated = Validator::make($request->all(), [
            'horaInicio' => 'required|date_format:H:i',
            'horaFin'    => 'required|date_format:H:i',
            'estado' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors()], 422);
        }

        $horario->update($validated->validated());

        return response()->json(['message' => 'Actualizado correctamente', 'success' => true, 'horario' => $horario]);
    }

    public function delete($id)
    {
        $doctor = auth('apiDoctor')->user() ?? request()->jwt_user;

        // Ensure we have a valid doctor user
        if (!$doctor || !isset($doctor->id)) {
            return response()->json(['error' => 'Usuario no válido'], 401);
        }

        $horario = Horarios::where('id', $id)->where('doctor_id', $doctor->id)->first();

        if (!$horario) {
            return response()->json(['message' => 'no se encontro el horario o no tienes permisos para eliminarlo']);
        }

        $horario->delete();

        return response()->json(['message' => 'horario eliminado correctamente']);
    }

    public function horarioById($id)
    {
        $horario = Horarios::find($id);

        if (!$horario) {
            return response()->json(['message' => 'no se encontro el horario']);
        }

        return response()->json(['horario' => $horario]);
    }

    // ========== MÉTODOS ESPECÍFICOS PARA DOCTORES ==========

    /**
     * Obtener horarios del doctor autenticado
     */
    public function misHorarios(Request $request)
    {
        // Use auth() helper as primary method, fallback to middleware
        $doctor = auth('apiDoctor')->user() ?? $request->jwt_user;

        // Ensure we have a valid doctor user
        if (!$doctor || !isset($doctor->id)) {
            return response()->json(['error' => 'Usuario no válido'], 401);
        }

        $horarios = Horarios::where('doctor_id', $doctor->id)
            ->orderBy('horaInicio', 'asc')
            ->get();

        return response()->json(['horarios' => $horarios]);
    }
}