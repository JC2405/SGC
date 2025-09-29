<?php

 namespace App\Http\Controllers;

 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Auth;
 use Illuminate\Support\Facades\Log;
 use App\Models\User;
 use Illuminate\Support\Facades\Hash;
 use Illuminate\Support\Facades\Validator;
 use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function crearUsuario(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name'     => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:4',
            'rol'      => 'required|string'
        ]);

        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors()]);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'rol'      => $request->rol
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario agregado correctamente',
            'user'    => $user
        ]);
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

        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado']);
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
        return response()->json(['message'=>'Errores de validación','errors'=>$v->errors()], 422);
    }

    $credentials = $v->validated();
    $guards = ['api_admin','api_doctores','api_usuarios'];
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
                    $log[] = [$guard, 'role_mismatch' => ['have'=>$userRole, 'need'=>$expectedRole]];
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

            // Add guard information to response
            $userArray = [
                'id' => $user->id,
                'name' => $user->name ?? ($user->nombre ?? 'Sin nombre'),
                'email' => $user->email,
                'guard' => $guardName,
                'user_type' => match ($guardName) {
                    'api_admin' => 'admin',
                    'api_doctores' => 'doctor',
                    'api_usuarios' => 'paciente',
                    default => 'unknown'
                }
            ];

            // ✅ AGREGAR INFORMACIÓN CRÍTICA DEL GUARD Y TIPO
            $userArray['user_type'] = match ($guardName) {
                'api_admin' => 'admin',
                'api_doctores' => 'doctor',
                'api_usuarios' => 'paciente',
                default => 'unknown'
            };

            $userArray['guard'] = $guardName;

            // Add role information if available
            if (isset($user->rol)) {
                $userArray['rol'] = $user->rol->rol ?? $user->rol->role ?? null;
                $userArray['rol_id'] = $user->rol_id;
                Log::info("🔍 DEBUG ME - Información del rol agregada", [
                    'user_type' => $userArray['user_type'],
                    'guard' => $userArray['guard'],
                    'rol' => $user->rol->rol ?? $user->rol->role ?? null,
                    'rol_id' => $user->rol_id
                ]);
            } elseif (isset($user->rol_id)) {
                $userArray['rol_id'] = $user->rol_id;
                Log::info("🔍 DEBUG ME - rol_id agregado", [
                    'user_type' => $userArray['user_type'],
                    'guard' => $userArray['guard'],
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

    public function logout(Request $request)
    {
        try {
            $guards = ['api_admin', 'api_doctores', 'api_usuarios'];

            foreach ($guards as $guard) {
                if (Auth::guard($guard)->check()) {
                    Auth::guard($guard)->logout();
                    break;
                }
            }

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
