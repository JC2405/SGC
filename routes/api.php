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
    Route::get("listarEspecialidades", [EspecialidadesController::class, 'index'])->middleware('rol:paciente,admin');
    Route::post("crearEspecialidad", [EspecialidadesController::class, 'store']);
    Route::get("especialidad/{id}", [EspecialidadesController::class, 'show']);
    Route::put("actualizarEspecialidad/{id}", [EspecialidadesController::class, 'update']);
    Route::delete("eliminarEspecialidad/{id}", [EspecialidadesController::class, 'destroy']);
    
    // DOCTORES
    Route::get("listarDoctores", [DoctorController::class, 'index']); 
    Route::post("crearDoctor", [DoctorController::class, 'store']);
    Route::get("doctor/{id}", [DoctorController::class, 'show']);
    Route::put("actualizarDoctor/{id}", [DoctorController::class, 'update']);
    Route::delete("eliminarDoctor/{id}", [DoctorController::class, 'destroy']);
    Route::get("doctoresPorEspecialidad/{especialidad_id}", [DoctorController::class, 'porEspecialidad']);
    
    // CITAS
    Route::get("listarCitas", [CitasController::class, 'index']);
    Route::post("crearCita", [CitasController::class, 'store']);
    Route::get("cita/{id}", [CitasController::class, 'show']);
    Route::put("actualizarCita/{id}", [CitasController::class, 'update']);
    Route::delete("eliminarCita/{id}", [CitasController::class, 'destroy']);
    Route::get("citasPorPaciente/{paciente_id}", [CitasController::class, 'porPaciente']);
    Route::get("citasPorDoctor/{doctor_id}", [CitasController::class, 'porDoctor']);
    Route::patch("cambiarEstadoCita/{id}", [CitasController::class, 'cambiarEstado']);
    
    // EPS
    Route::apiResource('eps', EpsController::class)->only(['index','store','show','update','destroy']);
    Route::get('eps/activas/list', [EpsController::class, 'activas']);
    Route::get('eps/inactivas/list', [EpsController::class, 'inactivas']);
    Route::patch('eps/{id}/cambiar-estado', [EpsController::class, 'cambiarEstado']);
    
    // Rutas para Cubículos (separadas)
    Route::get   ('cubiculos',            [CubiculosController::class, 'index']);
    Route::post  ('cubiculos',            [CubiculosController::class, 'store']);
    Route::get   ('cubiculos/{id}',       [CubiculosController::class, 'show']);
    Route::put   ('cubiculos/{id}',       [CubiculosController::class, 'update']);
    Route::patch ('cubiculos/{id}',       [CubiculosController::class, 'update']); // opcional
    Route::delete('cubiculos/{id}',       [CubiculosController::class, 'destroy']);
    
    // Rutas extra
    Route::get('cubiculos/disponibles/list', [CubiculosController::class, 'disponibles']);
    Route::get('cubiculos/tipo/{tipo}',      [CubiculosController::class, 'porTipo']);    

    // AUTH extra
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    // CUBICULOS
    Route::get("listarCubiculos", [CubiculosController::class, 'index']);
    Route::post("crearCubiculo", [CubiculosController::class, 'store']);
    Route::get("cubiculo/{id}", [CubiculosController::class, 'show']);
    Route::put("actualizarCubiculo/{id}", [CubiculosController::class, 'update']);
    Route::delete("eliminarCubiculo/{id}", [CubiculosController::class, 'destroy']);
});
