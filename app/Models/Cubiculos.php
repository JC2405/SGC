<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cubiculo extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'nombre',
        'tipo',
        'equipamiento',
        'estado',
        'capacidad'
    ];

    /**
     * Relación con citas
     */
    public function citas()
    {
        return $this->hasMany(Citas::class);
    }

    /**
     * Scope para cubículos disponibles
     */
    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'disponible');
    }

    /**
     * Scope por tipo de cubículo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}
