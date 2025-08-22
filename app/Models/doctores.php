<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class doctores extends Model
{
    protected $table = 'doctores';

    protected $fillable = [
        'nombre',
        'apellido', 
        'email',
        'telefono',
        'especialidad_id'
    ];

    public function especialidad()
    {
        return $this->belongsTo(\App\Models\Especialidad::class, 'especialidad_id');
    }

    public function citas()
    {
        return $this->hasMany(\App\Models\citas::class, 'doctor_id');
    }
}
