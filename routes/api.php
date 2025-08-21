<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//importar UsuariosController
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\EspecialidadesController;

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



