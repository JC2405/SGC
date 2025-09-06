<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cubiculo extends Model
{
    use HasFactory;

    // Opcional (coincide con tu migración)
    protected $table = 'cubiculos';

    protected $fillable = [
        'numero',
        'nombre',
        'tipo',
        'equipamiento',
        'estado',
        'capacidad',
    ];

    // Si luego usas relación con Cita:
     public function doctores()
    {
        return $this->hasMany(Doctor::class, 'cubiculo_id');
    }

    // Un cubículo puede tener muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class, 'cubiculo_id');
    }
}