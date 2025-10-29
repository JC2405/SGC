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

        
        Route::apiResource('eps', EpsController::class)->only(['index','store','show','update','destroy']);
        Route::get('eps/inactivas/list', [EpsController::class, 'inactivas']);
        Route::post("crearrol", [RoleController::class, 'store']);
// Usuarios
        Route::post('register', [AuthController::class, 'crearUsuario']);
        Route::get('listarUsuariosAuth', [AuthController::class, 'listarUsuarios']);
        Route::put('actualizarUsuarioAuth/{id}', [AuthController::class, 'actualizarUsuario']);
        Route::delete('eliminarUsuarioAuth/{id}', [AuthController::class, 'eliminarUsuario']);
       
        //Pacientes
        Route::get("listarUsuarios", [UsuariosController::class, 'index']);
        Route::put("actualizarUsuario/{id}", [UsuariosController::class, 'update']);
        Route::delete("eliminarUsuario/{id}", [UsuariosController::class, 'destroy']);          





/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/
Route::post('login', [AuthController::class, 'login']);
Route::post("crearUsuarioPaciente", [UsuariosController::class, 'crearUsuarioPaciente']);
Route::post("creacionDeAdmin", [AuthController::class, 'creacionDeAdmins']);
Route::post("logout", [AuthController::class, 'logout']);
Route::get("me", [AuthController::class, 'me']);
Route::get('eps/activas/list', [EpsController::class, 'activas']);
       
        Route::post('CrearUsuarioDoctor', [DoctorController::class, 'crearUsuarioDoctor']);
        Route::post("crearCubiculo", [CubiculosController::class, 'store']);
        Route::post("creacionDeAdmin", [AuthController::class, 'creacionDeAdmins']);

/*
|--------------------------------------------------------------------------
| Rutas protegidas con JWT
|--------------------------------------------------------------------------
*/
Route::middleware(['jwt.multiguard'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | TODOS LOS ROLES (admin, doctor, paciente)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:admin,doctor,paciente'])->group(function () {
        Route::get("usuario/{id}", [UsuariosController::class, 'show']);
        Route::post("refresh", [AuthController::class, 'refresh']);
        Route::get("listarDoctores", [DoctorController::class, 'index']);
        Route::get("listarEspecialidades", [EspecialidadesController::class, 'index']);
        Route::get("horariosDisponibles/{doctor_id}", [HorarioController::class, 'horasDisponibles']);
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:admin'])->group(function () {
            
        // Roles ckeck 
        Route::get('indexRol',[RoleController::class,'index']);
        
        //Admin
        Route::put("editarAdmin/{id}",[AuthController::class,'editarAdmin']);
        Route::delete("eliminarAdmin/{id}",[AuthController::class,'eliminarAdmin']);

        // Especialidades check 
        Route::post("crearespecialidad", [EspecialidadesController::class, 'store']);
        Route::get("especialidad/{id}", [EspecialidadesController::class, 'show']);
        Route::put("actualizarEspecialidad/{id}", [EspecialidadesController::class, 'update']);
        Route::delete("eliminarEspecialidad/{id}", [EspecialidadesController::class, 'destroy']);

        // Citas
        Route::get("listarCitas", [CitasController::class, 'index']);
        Route::put("actualizarCita/{id}", [CitasController::class, 'update']);
        Route::delete("eliminarCita/{id}", [CitasController::class, 'destroy']);

      
        // EPS check
        Route::patch('eps/cambiar-estado/{id}', [EpsController::class, 'cambiarEstado']);

        // Cubículos check
        Route::get("listarCubiculos", [CubiculosController::class, 'index']);
        Route::get("cubiculo/{id}", [CubiculosController::class, 'show']);
        Route::put("actualizarCubiculo/{id}", [CubiculosController::class, 'update']);
        Route::delete("eliminarCubiculo/{id}", [CubiculosController::class, 'destroy']);
        Route::get('cubiculos/disponibles/list', [CubiculosController::class, 'disponibles']);
        Route::get('cubiculos/tipo/{tipo}', [CubiculosController::class, 'porTipo']);

        
        // Horarios check
        Route::post('crearhorario', [HorarioController::class, 'store']);
        Route::get('listarHorarios', [HorarioController::class, 'index']);
        Route::put('actualizarHorario/{id}', [HorarioController::class, 'update']);
        Route::delete('eliminarHorario/{id}', [HorarioController::class, 'destroy']);
       
        // Doctores check
         //  Route::post("crearDoctor", [DoctorController::class, 'store']); store sin parametros necesarios
        Route::put("actualizarDoctor/{id}", [DoctorController::class, 'update']);
        Route::delete("eliminarDoctor/{id}", [DoctorController::class, 'destroy']);
        Route::get("doctoresPorEspecialidad/{especialidad_id}", [DoctorController::class, 'porEspecialidad']);

    });

    /*
    |--------------------------------------------------------------------------
    | MÉDICO
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:doctor'])->group(function () {
         //Route::get("especialidad/{id}", [EspecialidadesController::class, 'show']);
        Route::get("miPerfil", [DoctorController::class, 'miPerfil']);
        Route::get("cita/{id}", [CitasController::class, 'show']);
        Route::get("citasPorDoctor/{doctor_id}", [CitasController::class, 'porDoctor']);
        Route::patch("cambiarEstadoCita/{id}", [CitasController::class, 'cambiarEstado']);
        Route::get("cubiculos/{id}", [CubiculosController::class, 'show']);

        // Horarios del doctor
        Route::get("misHorarios", [HorarioController::class, 'misHorarios']);
        Route::delete("eliminarHorario/{id}", [HorarioController::class, 'delete']);
    });

    /*
    |--------------------------------------------------------------------------
    | PACIENTE
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:paciente'])->group(function () {
        Route::get("doctor/{id}", [DoctorController::class, 'show']);
        Route::get("cita/{id}", [CitasController::class, 'show']);
        Route::get("citasPorPaciente/{paciente_id}", [CitasController::class, 'porPaciente']);
        Route::post("crearCita", [CitasController::class, 'store']);
        Route::patch("cambiarEstadoCita/{id}", [CitasController::class, 'cambiarEstado']);
    });
});
