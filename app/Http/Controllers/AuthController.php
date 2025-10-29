<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Usuario;
use App\Models\Doctor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function crearUsuario(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'documento_identidad' => 'required|string|max:255|unique:usuarios',
            'email' => 'required|string|email|max:255|unique:usuarios',
            'password' => 'required|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'required|date',
            'eps_id' => 'nullable|exists:eps,id',
            'rol_id' => 'required|exists:roles,id',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validated->errors()
            ], 422);
        }

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'documento_identidad' => $request->documento_identidad,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'eps_id' => $request->eps_id,
            'rol_id' => $request->rol_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario agregado correctamente',
            'usuario' => $usuario
        ], 201);
    }


    public function creacionDeAdmins(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            // 🔸 Ya no pedimos rol_id en el request
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validated->errors()
            ], 422);
        }

        try {
            $admin = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'rol_id' => 1, // 👈 rol por defecto (administrador)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Administrador creado correctamente',
                'admin' => $admin
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el administrador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function editarAdmin(Request $request, $id)
    {
        // 🔹 Buscar el administrador por ID
        $admin = User::find($id);

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Administrador no encontrado'
            ], 404);
        }

        // 🔹 Validar los campos que pueden ser actualizados
        $validated = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $admin->id,
            'password' => 'nullable|string|min:8',
            // No se permite cambiar el rol_id aquí (se mantiene el rol de administrador)
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validated->errors()
            ], 422);
        }

        try {
            // 🔹 Actualizar los campos proporcionados
            if ($request->has('name')) {
                $admin->name = $request->name;
            }

            if ($request->has('email')) {
                $admin->email = $request->email;
            }

            if ($request->filled('password')) {
                $admin->password = Hash::make($request->password);
            }

            // 🔸 Asegurar que el rol se mantenga como 1 (admin)
            $admin->rol_id = 1;

            $admin->save();

            return response()->json([
                'success' => true,
                'message' => 'Administrador actualizado correctamente',
                'admin' => $admin
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el administrador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminarAdmin($id)
    {
        // 🔹 Buscar el administrador por ID
        $admin = User::find($id);

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Administrador no encontrado'
            ], 404);
        }

        // 🔹 Verificar que sea administrador (rol_id = 1)
        if ($admin->rol_id !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no es un administrador'
            ], 400);
        }

        // 🔹 Verificar que no esté intentando eliminarse a sí mismo
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->id == $id) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar tu propia cuenta de administrador'
            ], 400);
        }

        try {
            // 🔹 Eliminar el administrador
            $admin->delete();

            return response()->json([
                'success' => true,
                'message' => 'Administrador eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el administrador',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function listarUsuarios()
    {
        $usuarios = User::with('rol')->get();
        return response()->json(["data" => $usuarios]);
    }

    public function eliminarUsuario($id)
    {
        $usuario = User::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // 🔹 Verificar que no esté intentando eliminarse a sí mismo
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->id == $id) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar tu propia cuenta de usuario'
            ], 400);
        }

        // Si el usuario tiene rol de doctor, también eliminar el registro de doctor
        if ($usuario->rol_id == 2) { // ID 2 corresponde al rol de doctor según el frontend
            $doctor = Doctor::where('email', $usuario->email)->first();
            if ($doctor) {
                $doctor->delete();
            }
        }

        $usuario->delete();
        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado correctamente'
        ]);
    }

    public function actualizarUsuario(Request $request, $id)
    {
        $usuario = User::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $validated = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $usuario->id,
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $usuario->update($request->all());
        return response()->json($usuario);
    }



    public function login(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => 'Errores de validación', 'errors' => $v->errors()], 422);
        }

        $credentials = $v->validated();
        $guards = ['api_admin', 'api_doctores', 'api_usuarios'];
        $log = [];

        Log::info('🔍 DEBUG LOGIN - Iniciando proceso de login', [
            'email' => $request->email,
            'guards_to_try' => $guards
        ]);

        foreach ($guards as $guard) {
            try {
                Log::info("🔍 DEBUG LOGIN - Probando guard: {$guard}");

                $ok = Auth::guard($guard)->validate($credentials); // no genera token, solo valida
                $log[] = [$guard, 'validate' => $ok];
                Log::info("🔍 DEBUG LOGIN - {$guard} validate result:", ['ok' => $ok]);

                if (! $ok) {
                    Log::info("🔍 DEBUG LOGIN - {$guard} validación fallida, continuando");
                    continue;
                }

                if ($token = Auth::guard($guard)->attempt($credentials)) {
                    $user = Auth::guard($guard)->user();
                    Log::info("🔍 DEBUG LOGIN - {$guard} token generado exitosamente", [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'user_rol_id' => $user->rol_id
                    ]);

                    $expectedRole = match ($guard) {
                        'api_admin' => 'admin',
                        'api_doctores' => 'doctor',
                        'api_usuarios' => 'paciente',
                    };

                    Log::info("🔍 DEBUG LOGIN - {$guard} expected role:", ['expected' => $expectedRole]);

                    if (method_exists($user, 'rol')) {
                        $rol = $user->rol;
                        Log::info("🔍 DEBUG LOGIN - {$guard} relación 'rol' cargada", [
                            'rol_data' => $rol ? [
                                'id' => $rol->id,
                                'role' => $rol->role,
                                'rol' => $rol->rol
                            ] : null
                        ]);
                    }

                    $userRole = $user->rol->rol ?? $user->rol->role ?? null;
                    Log::info("🔍 DEBUG LOGIN - {$guard} user role obtenido", [
                        'userRole' => $userRole,
                        'expectedRole' => $expectedRole,
                        'match' => $userRole === $expectedRole
                    ]);

                    if ($userRole !== $expectedRole) {
                        $log[] = [$guard, 'role_mismatch' => ['have' => $userRole, 'need' => $expectedRole]];
                        Log::info("🔍 DEBUG LOGIN - {$guard} role mismatch", [
                            'have' => $userRole,
                            'need' => $expectedRole
                        ]);
                        continue;
                    }

                    Log::info("🔍 DEBUG LOGIN - {$guard} login exitoso, retornando respuesta");

                    // Crear array de usuario con información adicional para el frontend
                    $userArray = [
                        'id' => $user->id,
                        'name' => $user->name ?? ($user->nombre ?? 'Sin nombre'),
                        'email' => $user->email,
                    ];

                    // ✅ AGREGAR INFORMACIÓN CRÍTICA DEL GUARD Y TIPO
                    $userArray['user_type'] = match ($guard) {
                        'api_admin' => 'admin',
                        'api_doctores' => 'doctor',
                        'api_usuarios' => 'paciente',
                        default => 'unknown'
                    };

                    $userArray['guard'] = $guard;

                    // Agregar información del rol de manera más robusta
                    if (isset($user->rol)) {
                        $userArray['rol'] = $user->rol->rol ?? $user->rol->role ?? null;
                        $userArray['rol_id'] = $user->rol_id;
                    }

                    // Agregar información adicional del modelo específico
                    if ($guard === 'api_doctores' && isset($user->apellido)) {
                        $userArray['apellido'] = $user->apellido;
                        $userArray['telefono'] = $user->telefono ?? null;
                        $userArray['especialidad_id'] = $user->especialidad_id ?? null;
                    } elseif ($guard === 'api_usuarios' && isset($user->apellido)) {
                        $userArray['apellido'] = $user->apellido;
                        $userArray['telefono'] = $user->telefono ?? null;
                        $userArray['fecha_nacimiento'] = $user->fecha_nacimiento ?? null;
                    }

                    Log::info("🔍 DEBUG LOGIN - {$guard} respuesta completa", [
                        'user_id' => $user->id,
                        'user_type' => $userArray['user_type'],
                        'guard' => $userArray['guard'],
                        'rol' => $userArray['rol'] ?? 'no_rol'
                    ]);

                    return response()->json([
                        'access_token' => $token,
                        'guard'        => $guard,
                        'user'         => $userArray,
                    ]);
                } else {
                    $log[] = [$guard, 'attempt' => false];
                    Log::info("🔍 DEBUG LOGIN - {$guard} attempt fallido");
                }
            } catch (\Throwable $e) {
                $log[] = [$guard, 'error' => $e->getMessage()];
                Log::info("🔍 DEBUG LOGIN - {$guard} error", ['error' => $e->getMessage()]);
            }
        }

        Log::error('🔍 DEBUG LOGIN - Login fallido para todos los guards', ['debug_log' => $log]);

        // Dev only: inspeccionar por qué
        return response()->json(['message' => 'Credenciales inválidas', 'debug' => $log], 401);
    }
    // public function login(Request $request)
    // {
    //     $v = Validator::make($request->all(), [
    //         'email'    => 'required|email',
    //         'password' => 'required|string',
    //     ]);

    //     if ($v->fails()) {
    //         return response()->json([
    //             'message' => 'Errores de validación',
    //             'errors'  => $v->errors(),
    //         ], 422);
    //     }

    //     $credentials = $v->validated();

    //     // AuthController@login
    //     $guards = ['api_admin', 'api_doctores', 'api_usuarios'];

    //     foreach ($guards as $guard) {
    //         if ($token = Auth::guard($guard)->attempt($credentials)) {
    //             $user = Auth::guard($guard)->user();

    //             $expectedRole = match ($guard) {
    //                 'api_admin'     => 'admin',
    //                 'api_doctores'  => 'doctor',
    //                 'api_usuarios'  => 'paciente',
    //                 default         => null,
    //             };

    //             if (method_exists($user, 'rol')) {
    //                 $user->load('rol');
    //             }

    //             $userRole = $user->rol->rol ?? null;
    //             if ($userRole !== $expectedRole) {
    //                 continue;
    //             }

    //             return response()->json([
    //                 'access_token' => $token,
    //                 'guard'        => $guard,
    //                 'user'         => $user,
    //             ]);
    //         }
    //     }
    //     return response()->json(['message' => 'Credenciales inválidas'], 401);
    // }



    public function me(Request $request)
    {
        try {
            $guards = ['api_admin', 'api_doctores', 'api_usuarios'];
            $user = null;
            $guardName = null;

            Log::info('🔍 DEBUG ME - Iniciando consulta de información del usuario');

            // Find which guard has the authenticated user
            foreach ($guards as $guard) {
                if (Auth::guard($guard)->check()) {
                    $user = Auth::guard($guard)->user();
                    $guardName = $guard;
                    Log::info("🔍 DEBUG ME - Usuario encontrado en guard: {$guard}", [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'user_rol_id' => $user->rol_id
                    ]);
                    break;
                }
            }

            if (!$user) {
                Log::warning('🔍 DEBUG ME - Usuario no autenticado');
                return response()->json([
                    'message' => 'Usuario no autenticado',
                    'success' => false
                ], 401);
            }

            // Add guard information to response (excluding sensitive data as requested)
            $userArray = [
                'id' => $user->id,
                'name' => $user->name ?? ($user->nombre ?? 'Sin nombre'),
                'email' => $user->email,
            ];

            // Add role information if available (for internal use only, not exposed to frontend)
            if (isset($user->rol)) {
                Log::info("🔍 DEBUG ME - Información del rol (no expuesta)", [
                    'user_type' => match ($guardName) {
                        'api_admin' => 'admin',
                        'api_doctores' => 'doctor',
                        'api_usuarios' => 'paciente',
                        default => 'unknown'
                    },
                    'guard' => $guardName,
                    'rol' => $user->rol->rol ?? $user->rol->role ?? null,
                    'rol_id' => $user->rol_id
                ]);
            } elseif (isset($user->rol_id)) {
                Log::info("🔍 DEBUG ME - rol_id (no expuesto)", [
                    'user_type' => match ($guardName) {
                        'api_admin' => 'admin',
                        'api_doctores' => 'doctor',
                        'api_usuarios' => 'paciente',
                        default => 'unknown'
                    },
                    'guard' => $guardName,
                    'rol_id' => $user->rol_id
                ]);
            }

            // Add doctor-specific fields if it's a doctor
            if ($guardName === 'api_doctores') {
                if (isset($user->apellido)) $userArray['apellido'] = $user->apellido;
                if (isset($user->telefono)) $userArray['telefono'] = $user->telefono;
                if (isset($user->especialidad_id)) $userArray['especialidad_id'] = $user->especialidad_id;
                if (isset($user->cubiculo_id)) $userArray['cubiculo_id'] = $user->cubiculo_id;
            }

            // Add patient-specific fields if it's a patient
            if ($guardName === 'api_usuarios') {
                if (isset($user->apellido)) $userArray['apellido'] = $user->apellido;
                if (isset($user->telefono)) $userArray['telefono'] = $user->telefono;
                if (isset($user->fecha_nacimiento)) $userArray['fecha_nacimiento'] = $user->fecha_nacimiento;
                if (isset($user->direccion)) $userArray['direccion'] = $user->direccion;
                if (isset($user->eps_id)) $userArray['eps_id'] = $user->eps_id;
            }

            Log::info("🔍 DEBUG ME - Respuesta completa antes de enviar", [
                'userArray' => $userArray,
                'guardName' => $guardName
            ]);

            return response()->json([
                'success' => true,
                'user' => $userArray,
                'guard' => $guardName
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener información del usuario',
                'error' => $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    public function refresh(Request $request)
    {
        try {
            Log::info('🔍 DEBUG REFRESH - Iniciando refresh de token');
            Log::info('🔍 DEBUG REFRESH - Headers recibidos:', $request->headers->all());
            Log::info('🔍 DEBUG REFRESH - Authorization header:', $request->header('Authorization'));

            // Parse the token from the request
            $token = JWTAuth::getToken();
            if (!$token) {
                Log::warning('🔍 DEBUG REFRESH - No token provided');
                return response()->json([
                    'message' => 'No token provided',
                    'success' => false
                ], 401);
            }

            // Try to refresh the token
            $newToken = JWTAuth::refresh($token);
            Log::info("🔍 DEBUG REFRESH - Nuevo token generado exitosamente", [
                'token_length' => strlen($newToken),
                'token_preview' => substr($newToken, 0, 20) . '...'
            ]);

            // Get the user from the new token
            $user = JWTAuth::setToken($newToken)->toUser();
            $guardName = null;

            // Determine the guard based on user type
            if ($user->rol_id == 1) {
                $guardName = 'api_admin';
            } elseif ($user->rol_id == 2) {
                $guardName = 'api_doctores';
            } else {
                $guardName = 'api_usuarios';
            }

            Log::info("🔍 DEBUG REFRESH - Guard determined: {$guardName}", [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_rol_id' => $user->rol_id
            ]);

            return response()->json([
                'success' => true,
                'token' => $newToken,
                'guard' => $guardName,
                'message' => 'Token refrescado correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('🔍 DEBUG REFRESH - Error al refrescar token', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al refrescar token',
                'error' => $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
    public function logout(Request $request)
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json([
                    'message' => 'No hay token para invalidar',
                    'success' => false
                ], 400);
            }

            // Invalidar el token actual
            JWTAuth::invalidate($token);

            return response()->json([
                'message' => 'Sesión cerrada correctamente',
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
