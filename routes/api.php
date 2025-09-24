<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//importar controladores

use App\Http\Controllers\EspecialidadesController;  
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\CitasController;
use App\Http\Controllers\EpsController;
use App\Http\Controllers\CubiculosController; 
use App\Models\Cubiculo;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuariosController;


Route::post('register', [AuthController::class, 'crearUsuario']);
Route::post('login', [AuthController::class, 'login']);
Route::get("/listarUsuarios", [UsuariosController::class, 'index']);

Route::group(['middleware' => 'auth:api'], function () {

    // USUARIOS
    Route::post("crearUsuario", [UsuariosController::class, 'store'])->middleware('rol:admin,paciente,medico');
    Route::get("usuario/{id}", [UsuariosController::class, 'show']); 
    Route::put("actualizarUsuario/{id}", [UsuariosController::class, 'update']);
    Route::delete("eliminarUsuario/{id}", [UsuariosController::class, 'destroy']);  
    
    // ESPECIALIDADES
    Route::get("listarEspecialidades", [EspecialidadesController::class, 'index'])->middleware('rol:paciente,medico,admin');
    Route::post("crearEspecialidad", [EspecialidadesController::class, 'store'])->middleware('rol:medico,admin');
    Route::get("especialidad/{id}", [EspecialidadesController::class, 'show'])->middleware('rol:medico,admin');
    Route::put("actualizarEspecialidad/{id}", [EspecialidadesController::class, 'update'])->middleware('rol:admin');
    Route::delete("eliminarEspecialidad/{id}", [EspecialidadesController::class, 'destroy'])->middleware('rol:admin');
    
    // DOCTORES
    Route::get("listarDoctores", [DoctorController::class, 'index'])->middleware('rol:admin'); 
    Route::post("crearDoctor", [DoctorController::class, 'store'])->middleware('rol:admin');
    Route::get("doctor/{id}", [DoctorController::class, 'show'])->middleware('rol:paciente');
    Route::put("actualizarDoctor/{id}", [DoctorController::class, 'update'])->middleware('rol:admin,medico');
    Route::delete("eliminarDoctor/{id}", [DoctorController::class, 'destroy'])->middleware('rol:admin');
    Route::get("doctoresPorEspecialidad/{especialidad_id}", [DoctorController::class, 'porEspecialidad'])->middleware('rol:admin');
    
    // CITAS
    Route::get("listarCitas", [CitasController::class, 'index'])->middleware('rol:admin');
    Route::post("crearCita", [CitasController::class, 'store'])->middleware('rol:paciente,admin');
    Route::get("cita/{id}", [CitasController::class, 'show'])->middleware('rol:paciente');
    Route::put("actualizarCita/{id}", [CitasController::class, 'update'])->middleware('rol:admin');
    Route::delete("eliminarCita/{id}", [CitasController::class, 'destroy'])->middleware('rol:admin');
    Route::get("citasPorPaciente/{paciente_id}", [CitasController::class, 'porPaciente'])->middleware('rol:admin,paciente');
    Route::get("citasPorDoctor/{doctor_id}", [CitasController::class, 'porDoctor'])->middleware('rol:admin,medico');
    Route::patch("cambiarEstadoCita/{id}", [CitasController::class, 'cambiarEstado'])->middleware('rol:admin,medico');
    
    // EPS
    Route::apiResource('eps', EpsController::class)->only(['index','store','show','update','destroy'])->middleware('rol:admin');
    Route::get('eps/activas/list', [EpsController::class, 'activas'])->middleware('rol:admin');
    Route::get('eps/inactivas/list', [EpsController::class, 'inactivas'])->middleware('rol:admin');
    Route::patch('eps/{id}/cambiar-estado', [EpsController::class, 'cambiarEstado'])->middleware('rol:admin');

    // Rutas para Cubículos (separadas)
    Route::get   ('cubiculos',            [CubiculosController::class, 'index'])->middleware('rol:admin');
    Route::post  ('cubiculos',            [CubiculosController::class, 'store'])->middleware('rol:admin,medico');
    Route::get   ('cubiculos/{id}',       [CubiculosController::class, 'show'])->middleware('rol:admin,medico');
    Route::put   ('cubiculos/{id}',       [CubiculosController::class, 'update'])->middleware('rol:admin');
    Route::patch ('cubiculos/{id}',       [CubiculosController::class, 'update'])->middleware('rol:admin');
    Route::delete('cubiculos/{id}',       [CubiculosController::class, 'destroy'])->middleware('rol:admin');
    
    // Rutas extra
    Route::get('cubiculos/disponibles/list', [CubiculosController::class, 'disponibles'])->middleware('rol:admin');
    Route::get('cubiculos/tipo/{tipo}',      [CubiculosController::class, 'porTipo'])->middleware('rol:admin');    

    // AUTH extra
    Route::get('me', [AuthController::class, 'me'])->middleware('rol:admin,paciente,medico');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('rol:admin,paciente,medico');
    Route::post('refresh', [AuthController::class, 'refresh']);

    // CUBICULOS
    Route::get("listarCubiculos", [CubiculosController::class, 'index'])->middleware('rol:admin');
    Route::post("crearCubiculo", [CubiculosController::class, 'store'])->middleware('rol:admin');
    Route::get("cubiculo/{id}", [CubiculosController::class, 'show'])->middleware('rol:admin');
    Route::put("actualizarCubiculo/{id}", [CubiculosController::class, 'update'])->middleware('rol:admin');
    Route::delete("eliminarCubiculo/{id}", [CubiculosController::class, 'destroy'])->middleware('rol:admin');
});
