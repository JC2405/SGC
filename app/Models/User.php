<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 
        'email',
        'rol',
         'password',
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
}
