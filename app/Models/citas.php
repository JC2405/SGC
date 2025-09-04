<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'citas';

    protected $fillable = [
        'paciente_id',
        'doctor_id',
        'fecha_hora',
        'estado',
        'cubiculo_id',
        'observaciones'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime'
    ];

    public function paciente()
    {
        return $this->belongsTo(Usuario::class, 'paciente_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

       public function cubiculo()
    {
        return $this->belongsTo(Cubiculo::class, 'cubiculo_id');
    }
}
