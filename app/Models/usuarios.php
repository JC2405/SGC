<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido',
        'email', 
        'telefono',
        'fecha_nacimiento',
        'eps_id',
        'numero_afiliacion'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date'
    ];

    public function citas()
    {
        return $this->hasMany(Cita::class, 'paciente_id');
    }

    public function eps()
    {
        return $this->belongsTo(Eps::class);
    }
}
