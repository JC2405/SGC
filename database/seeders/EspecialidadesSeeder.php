<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Especialidad;

class EspecialidadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $especialidades = [
            [
                'nombre' => 'Medicina General',
                'descripcion' => 'Atención primaria de salud y diagnóstico básico de enfermedades comunes.',
            ],
            [
                'nombre' => 'Cardiología',
                'descripcion' => 'Especialidad médica que se ocupa del estudio, diagnóstico y tratamiento de las enfermedades del corazón y del aparato circulatorio.',
            ],
            [
                'nombre' => 'Dermatología',
                'descripcion' => 'Especialidad médica que se ocupa del estudio de la piel, sus estructuras, funciones y enfermedades.',
            ],
            [
                'nombre' => 'Neurología',
                'descripcion' => 'Especialidad médica que trata los trastornos del sistema nervioso, especialmente del cerebro, médula espinal y nervios periféricos.',
            ],
            [
                'nombre' => 'Pediatría',
                'descripcion' => 'Especialidad médica que se ocupa del estudio del niño y sus enfermedades, desde el nacimiento hasta la adolescencia.',
            ],
            [
                'nombre' => 'Ginecología',
                'descripcion' => 'Especialidad médica que trata las enfermedades propias del sistema reproductor femenino.',
            ],
            [
                'nombre' => 'Oftalmología',
                'descripcion' => 'Especialidad médica que se ocupa del estudio y tratamiento de las enfermedades del ojo.',
            ],
            [
                'nombre' => 'Traumatología',
                'descripcion' => 'Especialidad médica que se ocupa del estudio y tratamiento de las lesiones del aparato locomotor.',
            ],
            [
                'nombre' => 'Psiquiatría',
                'descripcion' => 'Especialidad médica que se ocupa del estudio y tratamiento de las enfermedades mentales.',
            ],
            [
                'nombre' => 'Radiología',
                'descripcion' => 'Especialidad médica que utiliza técnicas de imagen para diagnosticar y tratar enfermedades.',
            ],
        ];

        foreach ($especialidades as $especialidad) {
            Especialidad::create($especialidad);
        }

        $this->command->info('Especialidades médicas creadas exitosamente!');
    }
}
