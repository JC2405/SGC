<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $table = 'especialidades';

    protected $fillable = ['nombre', 'descripcion'];

    public $timestamps = true;

    public function doctores()
    {
        return $this->hasMany(Doctor::class, 'especialidad_id');
    }
}