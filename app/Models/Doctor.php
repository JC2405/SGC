<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;


class Doctor extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'apellido', 
        'email',
        'telefono',
        'especialidad_id'
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

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'doctor_id');
    }
    
      public function cubiculo()
    {
        return $this->belongsTo(Cubiculo::class, 'cubiculo_id');
    }
    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }
    
}
