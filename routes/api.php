<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//importar controladores
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\EspecialidadesController;
use App\Http\Controllers\DoctoresController;
use App\Http\Controllers\CitasController;
use App\Http\Controllers\EpsController;
use App\Http\Controllers\CubiculosController; // Agregada importación faltante

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//USUARIOS
Route::get("listarUsuarios", [UsuariosController::class, 'index']);
Route::post("crearUsuario", [UsuariosController::class, 'store']);
Route::get("usuario/{id}", [UsuariosController::class, 'show']); 
Route::put("actualizarUsuario/{id}", [UsuariosController::class, 'update']);
Route::delete("eliminarUsuario/{id}", [UsuariosController::class, 'destroy']);  

//ESPECIALIDADES
Route::get("listarEspecialidades", [EspecialidadesController::class, 'index']);
Route::post("crearEspecialidad", [EspecialidadesController::class, 'store']);
Route::get("especialidad/{id}", [EspecialidadesController::class, 'show']);
Route::put("actualizarEspecialidad/{id}", [EspecialidadesController::class, 'update']);
Route::delete("eliminarEspecialidad/{id}", [EspecialidadesController::class, 'destroy']);

//DOCTORES
Route::get("listarDoctores", [DoctoresController::class, 'index']);
Route::post("crearDoctor", [DoctoresController::class, 'store']);
Route::get("doctor/{id}", [DoctoresController::class, 'show']);
Route::put("actualizarDoctor/{id}", [DoctoresController::class, 'update']);
Route::delete("eliminarDoctor/{id}", [DoctoresController::class, 'destroy']);
Route::get("doctoresPorEspecialidad/{especialidad_id}", [DoctoresController::class, 'porEspecialidad']);

//CITAS
Route::get("listarCitas", [CitasController::class, 'index']);
Route::post("crearCita", [CitasController::class, 'store']);
Route::get("cita/{id}", [CitasController::class, 'show']);
Route::put("actualizarCita/{id}", [CitasController::class, 'update']);
Route::delete("eliminarCita/{id}", [CitasController::class, 'destroy']);
Route::get("citasPorPaciente/{paciente_id}", [CitasController::class, 'porPaciente']);
Route::get("citasPorDoctor/{doctor_id}", [CitasController::class, 'porDoctor']);
Route::patch("cambiarEstadoCita/{id}", [CitasController::class, 'cambiarEstado']);

// Rutas para EPS

Route::get('eps/buscar', [EpsController::class, 'index']);
Route::post('eps/crear', [EpsController::class, 'store']);
Route::get('eps/{id}', [EpsController::class, 'get']);
Route::put('eps/{id}', [EpsController::class, 'update']);
Route::delete('eps/{id}', [EpsController::class, 'destroy']);
Route::apiResource('eps', EpsController::class);
Route::get('eps/activas/list', [EpsController::class, 'activas']);
Route::get('eps/inactivas/list', [EpsController::class, 'inactivas']);
Route::patch('eps/{id}/cambiar-estado', [EpsController::class, 'cambiarEstado']);


// Rutas para Cubículos
Route::apiResource('cubiculos', CubiculosController::class);
Route::get('cubiculos/disponibles/list', [CubiculosController::class, 'disponibles']);
Route::get('cubiculos/tipo/{tipo}', [CubiculosController::class, 'porTipo']);
