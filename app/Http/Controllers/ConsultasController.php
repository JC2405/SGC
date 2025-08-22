<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\doctores;
use App\Models\especialidades;
use App\Models\usuarios;
use App\Models\citas;
use Illuminate\Support\Facades\DB;

class ConsultasController extends Controller
{
    // 1. TODOS LOS DOCTORES DE UNA ESPECIALIDAD ESPECÍFICA
    public function doctoresPorEspecialidad($especialidadNombre)
    {
        $doctores = doctores::with(['especialidad', 'citas'])
            ->whereHas('especialidad', function($query) use ($especialidadNombre) {
                $query->where('nombre', $especialidadNombre);
            })
            ->withCount([
                'citas',
                'citas as citas_completadas' => function($query) {
                    $query->where('estado', 'completada');
                },
                'citas as citas_pendientes' => function($query) {
                    $query->where('estado', 'pendiente');
                },
                'citas as citas_canceladas' => function($query) {
                    $query->where('estado', 'cancelada');
                }
            ])
            ->orderBy('citas_count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $doctores,
            'especialidad' => $especialidadNombre
        ]);
    }

    // 2. REPORTE MENSUAL DE CITAS
    public function reporteMensual()
    {
        $reporte = citas::select([
                DB::raw('DATE_FORMAT(fecha, "%Y-%m") as mes'),
                DB::raw('COUNT(citas.id) as total_citas'),
                DB::raw('COUNT(DISTINCT citas.usuario_id) as pacientes_unicos'),
                DB::raw('COUNT(DISTINCT citas.doctor_id) as doctores_activos'),
                DB::raw('COUNT(DISTINCT especialidades.id) as especialidades_solicitadas'),
                DB::raw('COUNT(CASE WHEN citas.estado = "completada" THEN 1 END) as citas_completadas'),
                DB::raw('COUNT(CASE WHEN citas.estado = "cancelada" THEN 1 END) as citas_canceladas'),
                DB::raw('COUNT(CASE WHEN citas.estado = "pendiente" THEN 1 END) as citas_pendientes'),
                DB::raw('ROUND(AVG(CASE WHEN citas.estado = "completada" THEN 1 ELSE 0 END) * 100, 2) as porcentaje_completadas')
            ])
            ->join('doctores', 'citas.doctor_id', '=', 'doctores.id')
            ->join('especialidades', 'doctores.especialidad_id', '=', 'especialidades.id')
            ->join('usuarios', 'citas.usuario_id', '=', 'usuarios.id')
            ->where('citas.fecha', '>=', now()->subMonths(12))
            ->groupBy(DB::raw('DATE_FORMAT(fecha, "%Y-%m")'))
            ->orderBy('mes', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reporte
        ]);
    }

    // 3. RANKING DE DOCTORES MÁS SOLICITADOS
    public function rankingDoctores()
    {
        $ranking = doctores::select([
                'especialidades.nombre as especialidad',
                'doctores.nombre as doctor_nombre',
                'doctores.apellido as doctor_apellido',
                DB::raw('COUNT(citas.id) as total_citas'),
                DB::raw('COUNT(CASE WHEN citas.estado = "completada" THEN 1 END) as citas_completadas'),
                DB::raw('ROUND(COUNT(CASE WHEN citas.estado = "completada" THEN 1 END) / COUNT(citas.id) * 100, 2) as tasa_completadas'),
                DB::raw('RANK() OVER (PARTITION BY especialidades.id ORDER BY COUNT(citas.id) DESC) as ranking_en_especialidad')
            ])
            ->join('especialidades', 'doctores.especialidad_id', '=', 'especialidades.id')
            ->leftJoin('citas', 'doctores.id', '=', 'citas.doctor_id')
            ->where('citas.fecha', '>=', now()->subMonths(6))
            ->groupBy('doctores.id', 'especialidades.id', 'especialidades.nombre', 'doctores.nombre', 'doctores.apellido')
            ->havingRaw('COUNT(citas.id) > 0')
            ->orderBy('especialidades.nombre')
            ->orderBy('ranking_en_especialidad')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $ranking
        ]);
    }

    // 4. PACIENTES MÁS FRECUENTES
    public function pacientesFrecuentes()
    {
        $pacientes = usuarios::select([
                'usuarios.id as paciente_id',
                'usuarios.nombre as paciente_nombre',
                'usuarios.apellido as paciente_apellido',
                'usuarios.email as paciente_email',
                DB::raw('COUNT(citas.id) as total_citas'),
                DB::raw('COUNT(DISTINCT citas.doctor_id) as doctores_diferentes'),
                DB::raw('COUNT(DISTINCT especialidades.id) as especialidades_visitadas'),
                DB::raw('MIN(citas.fecha) as primera_cita'),
                DB::raw('MAX(citas.fecha) as ultima_cita'),
                DB::raw('DATEDIFF(MAX(citas.fecha), MIN(citas.fecha)) as dias_como_paciente'),
                DB::raw('GROUP_CONCAT(DISTINCT especialidades.nombre ORDER BY especialidades.nombre SEPARATOR ", ") as especialidades_lista'),
                DB::raw('COUNT(CASE WHEN citas.estado = "cancelada" THEN 1 END) as citas_canceladas'),
                DB::raw('ROUND(COUNT(CASE WHEN citas.estado = "cancelada" THEN 1 END) / COUNT(citas.id) * 100, 2) as tasa_cancelacion')
            ])
            ->join('citas', 'usuarios.id', '=', 'citas.usuario_id')
            ->join('doctores', 'citas.doctor_id', '=', 'doctores.id')
            ->join('especialidades', 'doctores.especialidad_id', '=', 'especialidades.id')
            ->where('citas.fecha', '>=', now()->subMonths(12))
            ->groupBy('usuarios.id', 'usuarios.nombre', 'usuarios.apellido', 'usuarios.email')
            ->havingRaw('COUNT(citas.id) >= 3')
            ->orderBy('total_citas', 'desc')
            ->orderBy('tasa_cancelacion', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pacientes
        ]);
    }

    // 5. ANÁLISIS DE DISPONIBILIDAD POR ESPECIALIDAD
    public function analisisDisponibilidad()
    {
        $analisis = especialidades::select([
                'especialidades.id as especialidad_id',
                'especialidades.nombre as especialidad_nombre',
                DB::raw('COUNT(DISTINCT doctores.id) as total_doctores'),
                DB::raw('COUNT(citas.id) as total_citas'),
                DB::raw('ROUND(COUNT(citas.id) / COUNT(DISTINCT doctores.id), 2) as promedio_citas_por_doctor'),
                DB::raw('COUNT(CASE WHEN citas.fecha >= CURDATE() THEN 1 END) as citas_futuras'),
                DB::raw('COUNT(CASE WHEN citas.fecha >= CURDATE() AND citas.fecha <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as citas_proximo_mes'),
                DB::raw('CASE 
                    WHEN COUNT(DISTINCT doctores.id) = 0 THEN "SIN DOCTORES"
                    WHEN COUNT(citas.id) / COUNT(DISTINCT doctores.id) > 50 THEN "SOBRECARGADA"
                    WHEN COUNT(citas.id) / COUNT(DISTINCT doctores.id) > 25 THEN "ALTA DEMANDA"
                    WHEN COUNT(citas.id) / COUNT(DISTINCT doctores.id) > 10 THEN "DEMANDA NORMAL"
                    ELSE "BAJA DEMANDA"
                END as nivel_demanda')
            ])
            ->leftJoin('doctores', 'especialidades.id', '=', 'doctores.especialidad_id')
            ->leftJoin('citas', function($join) {
                $join->on('doctores.id', '=', 'citas.doctor_id')
                     ->where('citas.fecha', '>=', now()->subMonths(6));
            })
            ->groupBy('especialidades.id', 'especialidades.nombre')
            ->orderBy('promedio_citas_por_doctor', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $analisis
        ]);
    }

    // BONUS 1: Horarios más solicitados
    public function horariosMasSolicitados()
    {
        $horarios = citas::select([
                DB::raw('DAYNAME(citas.fecha) as dia_semana'),
                DB::raw('HOUR(citas.hora) as hora'),
                DB::raw('COUNT(*) as total_citas'),
                DB::raw('COUNT(DISTINCT citas.usuario_id) as pacientes_unicos'),
                DB::raw('GROUP_CONCAT(DISTINCT especialidades.nombre ORDER BY especialidades.nombre SEPARATOR ", ") as especialidades')
            ])
            ->join('doctores', 'citas.doctor_id', '=', 'doctores.id')
            ->join('especialidades', 'doctores.especialidad_id', '=', 'especialidades.id')
            ->where('citas.fecha', '>=', now()->subMonths(3))
            ->groupBy(DB::raw('DAYOFWEEK(citas.fecha)'), DB::raw('HOUR(citas.hora)'))
            ->orderBy('total_citas', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $horarios
        ]);
    }

    // BONUS 2: Patrones de cancelación
    public function patronesCancelacion()
    {
        $patrones = especialidades::select([
                'especialidades.nombre as especialidad',
                DB::raw('COUNT(citas.id) as total_citas'),
                DB::raw('COUNT(CASE WHEN citas.estado = "cancelada" THEN 1 END) as citas_canceladas'),
                DB::raw('ROUND(COUNT(CASE WHEN citas.estado = "cancelada" THEN 1 END) / COUNT(citas.id) * 100, 2) as tasa_cancelacion'),
                DB::raw('AVG(DATEDIFF(citas.fecha, citas.created_at)) as dias_promedio_anticipacion'),
                DB::raw('COUNT(CASE WHEN citas.estado = "cancelada" AND DATEDIFF(citas.fecha, citas.updated_at) <= 1 THEN 1 END) as cancelaciones_ultimo_momento')
            ])
            ->join('doctores', 'especialidades.id', '=', 'doctores.especialidad_id')
            ->join('citas', 'doctores.id', '=', 'citas.doctor_id')
            ->where('citas.fecha', '>=', now()->subMonths(6))
            ->groupBy('especialidades.id', 'especialidades.nombre')
            ->havingRaw('COUNT(citas.id) >= 10')
            ->orderBy('tasa_cancelacion', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $patrones
        ]);
    }
}
