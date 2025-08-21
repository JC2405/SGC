<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class usuarios extends Model
{
    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido',
        'email', 
        'telefono',
        'fecha_nacimiento',
    ];

    public function citas()
    {
        // referencia totalmente calificada para evitar errores de importación
        return $this->hasMany(\App\Models\Cita::class, 'paciente_id');
    }
}