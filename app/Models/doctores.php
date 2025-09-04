<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $table = 'doctores';

    protected $fillable = [
        'nombre',
        'apellido', 
        'email',
        'telefono',
        'especialidad_id'
    ];

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
}
