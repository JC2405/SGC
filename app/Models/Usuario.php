<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'apellido',
        'documento_identidad',
        'email', 
        'password',
        'telefono',
        'fecha_nacimiento',
        'eps_id',
        'rol_id',

    ];

    protected $casts = [
        'fecha_nacimiento' => 'date'
    ];

      protected $hidden = [
        'password',
    ];

    // Métodos requeridos por JWT
    public function getJWTIdentifier()
    {
        return $this->getKey(); // devuelve el id del usuario
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'paciente_id');
    }

    public function eps()
    {
        return $this->belongsTo(Eps::class);
    }

    public function rol()
    {
        return $this->belongsTo(Roles::class, 'rol_id');
    }
}
