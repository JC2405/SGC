<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cita; // Corregido nombre del modelo
use Carbon\Carbon;

class CitasController extends Controller
{
    public function index()
    {
        $citas = Cita::with(['paciente', 'doctor.especialidad', 'cubiculo'])->get();
        return response()->json($citas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:usuarios,id',
            'doctor_id' => 'required|exists:doctores,id',
            'fecha_hora' => 'required|date|after:now',
            'estado' => 'in:pendiente,confirmada,cancelada,atendida',
            'cubiculo_id' => 'nullable|exists:cubiculos,id',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Verificar disponibilidad del doctor
        $fechaHora = Carbon::parse($request->fecha_hora);
        $citaExistente = Cita::where('doctor_id', $request->doctor_id)
                             ->where('fecha_hora', $fechaHora)
                             ->whereIn('estado', ['pendiente', 'confirmada'])
                             ->first();

        if ($citaExistente) {
            return response()->json(['message' => 'El doctor no está disponible en esa fecha y hora'], 409);
        }

        $cita = Cita::create($request->all());
        $cita->load(['paciente', 'doctor.especialidad', 'cubiculo']);
        return response()->json($cita, 201);
    }

    public function show($id)
    {
        $cita = Cita::with(['paciente', 'doctor.especialidad', 'cubiculo'])->find($id);
        if (!$cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }
        return response()->json($cita);
    }

    public function update(Request $request, $id)
    {
        $cita = Cita::find($id);
        if (!$cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'paciente_id' => 'exists:usuarios,id',
            'doctor_id' => 'exists:doctores,id',
            'fecha_hora' => 'date|after:now',
            'estado' => 'in:pendiente,confirmada,cancelada,atendida',
            'cubiculo_id' => 'nullable|exists:cubiculos,id',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Si se cambia fecha/hora o doctor, verificar disponibilidad
        if ($request->has('fecha_hora') || $request->has('doctor_id')) {
            $fechaHora = Carbon::parse($request->fecha_hora ?? $cita->fecha_hora);
            $doctorId = $request->doctor_id ?? $cita->doctor_id;
            
            $citaExistente = Cita::where('doctor_id', $doctorId)
                                 ->where('fecha_hora', $fechaHora)
                                 ->where('id', '!=', $id)
                                 ->whereIn('estado', ['pendiente', 'confirmada'])
                                 ->first();

            if ($citaExistente) {
                return response()->json(['message' => 'El doctor no está disponible en esa fecha y hora'], 409);
            }
        }

        $cita->update($request->all());
        $cita->load(['paciente', 'doctor.especialidad', 'cubiculo']);
        return response()->json($cita);
    }

    public function destroy($id)
    {
        $cita = Cita::find($id);
        if (!$cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $cita->delete();
        return response()->json(['message' => 'Cita eliminada']);
    }

    // Métodos adicionales útiles
    public function porPaciente($paciente_id)
    {
        $citas = Cita::where('paciente_id', $paciente_id)
                     ->with(['doctor.especialidad', 'cubiculo'])
                     ->orderBy('fecha_hora', 'desc')
                     ->get();
        return response()->json($citas);
    }

    public function porDoctor($doctor_id)
    {
        $citas = Cita::where('doctor_id', $doctor_id)
                     ->with(['paciente', 'cubiculo'])
                     ->orderBy('fecha_hora', 'asc')
                     ->get();
        return response()->json($citas);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $cita = Cita::find($id);
        if (!$cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:pendiente,confirmada,cancelada,atendida'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $cita->update(['estado' => $request->estado]);
        $cita->load(['paciente', 'doctor.especialidad', 'cubiculo']);
        return response()->json($cita);
    }
}
