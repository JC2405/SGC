<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('doctor_id');
        $table->enum('dia', [
            'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'
        ]);
        $table->time('hora_inicio');
        $table->time('hora_fin');
        $table->enum('estado', ['activo', 'inactivo'])->default('activo');
        $table->timestamps();

        // Relación con doctores
        $table->foreign('doctor_id')
              ->references('id')
              ->on('doctores')
              ->onDelete('cascade'); // si se borra un doctor, se borran sus horarios
               });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
