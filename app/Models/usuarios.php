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

    protected $casts = [
        'fecha_nacimiento' => 'date'
    ];

    public function citas()
    {
        return $this->hasMany(\App\Models\citas::class, 'paciente_id');
    }
}
