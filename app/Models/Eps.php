<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eps extends Model
{
    protected $table = 'eps';

    protected $fillable = [
        'nombre',
        'codigo',
        'nit',
        'telefono',
        'email',
        'direccion',
        'estado',
    ];

    // scope: eps activas
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    // relación: una EPS tiene muchos usuarios
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'eps_id');
    }
}