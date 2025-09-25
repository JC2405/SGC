<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Doctor; // Corregido nombre del modelo
use PhpParser\Comment\Doc;

class DoctorController extends Controller
{
    public function index()
    {
        $doctores = Doctor::with('especialidad')->get();
        return response()->json($doctores);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|unique:doctores,email',
            'telefono' => 'nullable|string|max:20',
            'especialidad_id' => 'required|exists:especialidades,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $doctor = Doctor::create($request->all());
        $doctor->load('especialidad');
        return response()->json($doctor, 201);
    }

    public function show($id)
    {
        $doctor = Doctor::with('especialidad')->find($id);
        if (!$doctor) {
            return response()->json(['message' => 'Doctor no encontrado'], 404);
        }
        return response()->json($doctor);
    }

    public function update(Request $request, $id)
    {
        $doctor = Doctor::find($id);
        if (!$doctor) {
            return response()->json(['message' => 'Doctor no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:255',
            'apellido' => 'string|max:255',
            'email' => 'email|unique:doctores,email,' . $doctor->id,
            'telefono' => 'nullable|string|max:20',
            'especialidad_id' => 'exists:especialidades,id',
            'cubiculo_id' => 'nullable|exists:cubiculos,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $doctor->update($request->all());
        $doctor->load('especialidad');
        return response()->json($doctor);
    }

    public function destroy($id)
    {
        $doctor = Doctor::find($id);
        if (!$doctor) {
            return response()->json(['message' => 'Doctor no encontrado'], 404);
        }

        $doctor->delete();
        return response()->json(['message' => 'Doctor eliminado']);
    }

    // Método adicional para obtener doctores por especialidad
    public function porEspecialidad($especialidad_id)
    {
        $doctores = Doctor::where('especialidad_id', $especialidad_id)
                           ->with('especialidad')
                           ->get();
        return response()->json($doctores);
    }

      public function crearUsuarioPaciente(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|unique:doctores,email',
            'password' => 'required|string|min:4',
            'telefono' => 'nullable|string|max:20',
            'especialidad_id' => 'required|exists:especialidades,id',
            'cubiculo_id' => 'nullable|exists:cubiculos,id'
        ]);

        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors()]);
        }

        $doctor = Doctor::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'password' => Doctor::make($request->password),
            'telefono' => $request->telefono,
            'especialidad_id' => $request->especialidad_id,
            'cubiculo_id' => $request->cubiculo_id
        ]);

        return response()->json(['success' => true, 'message' => 'Usuario agregado correctamente', 'Doctor' => $doctor]);
    }
}
