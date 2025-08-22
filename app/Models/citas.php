<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class citas extends Model
{
    protected $table = 'citas';

    protected $fillable = [
        'paciente_id',
        'doctor_id',
        'fecha_hora',
        'estado'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime'
    ];

    public function paciente()
    {
        return $this->belongsTo(\App\Models\usuarios::class, 'paciente_id');
    }

    public function doctor()
    {
        return $this->belongsTo(\App\Models\doctores::class, 'doctor_id');
    }
}
