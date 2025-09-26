<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horarios extends Model
{
    use HasFactory;

    protected $table = 'horarios'; 
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

