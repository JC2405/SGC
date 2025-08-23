<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eps extends Model
{
    use HasFactory;

    protected $table = 'eps';

    protected $fillable = [
        'nombre',
        'codigo',
        'nit',
        'telefono',
        'email',
        'direccion',
        'estado'
    ];

    /**
     * Relación con usuarios
     */
    public function usuarios()
    {
        return $this->hasMany(Usuarios::class);
    }

    /**
     * Scope para EPS activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }
}
