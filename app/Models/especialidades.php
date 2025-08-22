<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class especialidades extends Model
{
    protected $table = 'especialidades';

    protected $fillable = [
        'nombre', 
        'descripcion'
    ];

    public $timestamps = true;

    public function doctores()
    {
        return $this->hasMany(\App\Models\doctores::class, 'especialidad_id');
    }
}
