<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'dia',
        'hora_inicio',
        'hora_fin',
        'estado',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}

