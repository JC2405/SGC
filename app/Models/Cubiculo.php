<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cubiculo extends Model
{
    use HasFactory;

    protected $table = 'cubiculos';

    protected $fillable = [
        'numero',
        'nombre',
        'tipo',
        'equipamiento',
        'estado',
        'capacidad',
    ];

    // Relación con doctores
    public function doctores()
    {
        return $this->hasMany(Doctor::class, 'cubiculo_id');
    }

    // Relación con citas
    public function citas()
    {
        return $this->hasMany(Cita::class, 'cubiculo_id');
    }

    // Scope para cubículos disponibles
    public function scopeDisponible($query)
    {
        return $query->where('estado', 'disponible');
    }

    // Scope para filtrar por tipo
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}
