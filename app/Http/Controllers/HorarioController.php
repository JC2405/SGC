<?php

namespace App\Http\Controllers;

use App\Models\Horarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class HorarioController extends Controller
{
    //
    public function index()
    {
        $horarios = Horarios::with('doctor.especialidad')->get();

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
            'horario' => $crearHorario->load('doctor.especialidad')
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

    /**
     * Obtener horas disponibles para un doctor en una fecha específica
     */
    public function horasDisponibles(Request $request, $doctor_id)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fecha = $request->fecha;
        $diaSemana = Carbon::parse($fecha)->format('l'); // Obtener el día de la semana en inglés

        // Mapear días de la semana al formato español usado en la BD
        $diasMap = [
            'Monday' => 'lunes',
            'Tuesday' => 'martes',
            'Wednesday' => 'miercoles',
            'Thursday' => 'jueves',
            'Friday' => 'viernes',
            'Saturday' => 'sabado',
            'Sunday' => 'domingo'
        ];

        $diaEspanol = $diasMap[$diaSemana] ?? $diaSemana;

        // Obtener horarios del doctor para ese día
        $horarios = Horarios::where('doctor_id', $doctor_id)
            ->where('dia', $diaEspanol)
            ->where('estado', 'activo')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        if ($horarios->isEmpty()) {
            return response()->json(['horas_disponibles' => []]);
        }

        $horasDisponibles = [];

        foreach ($horarios as $horario) {
            // Generar horas cada 30 minutos entre hora_inicio y hora_fin
            $horaInicio = Carbon::createFromFormat('H:i:s', $horario->hora_inicio);
            $horaFin = Carbon::createFromFormat('H:i:s', $horario->hora_fin);

            $horaActual = $horaInicio->copy();

            while ($horaActual->lessThan($horaFin)) {
                $horaCompleta = $fecha . ' ' . $horaActual->format('H:i:s');

                // Verificar si ya hay una cita en esa hora
                $citaExistente = \App\Models\Cita::where('doctor_id', $doctor_id)
                    ->where('fecha_hora', $horaCompleta)
                    ->whereIn('estado', ['pendiente', 'confirmada'])
                    ->exists();

                if (!$citaExistente) {
                    $horasDisponibles[] = [
                        'id' => $horaActual->format('H:i:s'),
                        'hora_completa' => $horaCompleta,
                        'hora_display' => $horaActual->format('H:i')
                    ];
                }

                $horaActual->addMinutes(30); // Intervalos de 30 minutos
            }
        }

        return response()->json(['horas_disponibles' => $horasDisponibles]);
    }
}