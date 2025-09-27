<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Importar controladores
use App\Http\Controllers\EspecialidadesController;  
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\CitasController;
use App\Http\Controllers\EpsController;
use App\Http\Controllers\CubiculosController; 
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UsuariosController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/
Route::post('login', [AuthController::class, 'login']);
   Route::post("crearUsuarioPaciente", [UsuariosController::class, 'crearUsuarioPaciente']);

/*
|--------------------------------------------------------------------------
| Rutas protegidas con JWT
|--------------------------------------------------------------------------
*/
Route::middleware(['jwt.multiguard'])->group(function () {
   
    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:admin'])->group(function () {
        // Roles
     
        Route::post("crearRol", [RoleController::class, 'store']);
        Route::get('indexRol',[RoleController::class,'index']);

        // Usuarios
        Route::post('register', [AuthController::class, 'crearUsuario']);
       Route::post("crearUsuario", [UsuariosController::class, 'store']);

        Route::get("listarUsuarios", [UsuariosController::class, 'index']);
        Route::put("actualizarUsuario/{id}", [UsuariosController::class, 'update']);
        Route::delete("eliminarUsuario/{id}", [UsuariosController::class, 'destroy']);  

        // Especialidades
        Route::get("listarEspecialidades", [EspecialidadesController::class, 'index']);
        Route::post("crearEspecialidad", [EspecialidadesController::class, 'store']);
        Route::get("especialidad/{id}", [EspecialidadesController::class, 'show']);
        Route::put("actualizarEspecialidad/{id}", [EspecialidadesController::class, 'update']);
        Route::delete("eliminarEspecialidad/{id}", [EspecialidadesController::class, 'destroy']);

        // Doctores
        Route::post('CrearUsuarioDoctor', [DoctorController::class, 'crearUsuarioDoctor']);
        
        Route::post("crearDoctor", [DoctorController::class, 'store']);
        Route::put("actualizarDoctor/{id}", [DoctorController::class, 'update']); 
        Route::delete("eliminarDoctor/{id}", [DoctorController::class, 'destroy']);
        Route::get("doctoresPorEspecialidad/{especialidad_id}", [DoctorController::class, 'porEspecialidad']);
        
        // Citas
        Route::get("listarCitas", [CitasController::class, 'index']);
        Route::put("actualizarCita/{id}", [CitasController::class, 'update']);
        Route::delete("eliminarCita/{id}", [CitasController::class, 'destroy']);
        Route::get("citasPorPaciente/{paciente_id}", [CitasController::class, 'porPaciente']);
        Route::get("citasPorDoctor/{doctor_id}", [CitasController::class, 'porDoctor']);
        Route::patch("cambiarEstadoCita/{id}", [CitasController::class, 'cambiarEstado']);

        // EPS
      
        Route::get('eps/activas/list', [EpsController::class, 'activas']);
        Route::get('eps/inps/{id}activas/list', [EpsController::class, 'inactivas']);
        Route::patch('e/cambiar-estado', [EpsController::class, 'cambiarEstado']);

        // Cubículos
        Route::get("listarCubiculos", [CubiculosController::class, 'index']);
        Route::post("crearCubiculo", [CubiculosController::class, 'store']);
        Route::get("cubiculo/{id}", [CubiculosController::class, 'show']);
        Route::put("actualizarCubiculo/{id}", [CubiculosController::class, 'update']);
        Route::delete("eliminarCubiculo/{id}", [CubiculosController::class, 'destroy']);
        Route::get('cubiculos/disponibles/list', [CubiculosController::class, 'disponibles']);
        Route::get('cubiculos/tipo/{tipo}', [CubiculosController::class, 'porTipo']);

        // Horarios
        Route::post('crearHorario', [HorarioController::class, 'store']);
        Route::get('listarHorarios', [HorarioController::class, 'index']);
        Route::put('actualizarHorario/{id}', [HorarioController::class, 'update']);
        Route::delete('eliminarHorario/{id}', [HorarioController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | MÉDICO
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:doctor'])->group(function () {
        Route::get("especialidad/{id}", [EspecialidadesController::class, 'show']);
        Route::put("actualizarDoctor/{id}", [DoctorController::class, 'update']);
        Route::get("citasPorDoctor/{doctor_id}", [CitasController::class, 'porDoctor']);
        Route::patch("cambiarEstadoCita/{id}", [CitasController::class, 'cambiarEstado']);
        Route::get("cubiculos/{id}", [CubiculosController::class, 'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | PACIENTE
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:paciente'])->group(function () {
        Route::get("listarEspecialidades", [EspecialidadesController::class, 'index']);
        Route::get("doctor/{id}", [DoctorController::class, 'show']);
        Route::get("cita/{id}", [CitasController::class, 'show']);
        Route::get("citasPorPaciente/{paciente_id}", [CitasController::class, 'porPaciente']);
    });

    /*
    |--------------------------------------------------------------------------
    | TODOS LOS ROLES (admin, doctor, paciente)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:admin,doctor,paciente'])->group(function () {
          Route::apiResource('eps', EpsController::class)->only(['index','store','show','update','destroy']);
         Route::get("listarDoctores", [DoctorController::class, 'index']);
        Route::get("usuario/{id}", [UsuariosController::class, 'show']); 
        Route::post("crearCita", [CitasController::class, 'store']);
        Route::get("me", [AuthController::class, 'me']);
        Route::post("logout", [AuthController::class, 'logout']);
        Route::post("refresh", [AuthController::class, 'refresh']);
    });
});
