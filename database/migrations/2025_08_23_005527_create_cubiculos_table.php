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
        Schema::create('cubiculos', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->string('nombre')->nullable();
            $table->enum('tipo', ['consulta', 'procedimientos', 'emergencia'])->default('consulta'); // Corregido campo tipo
            $table->text('equipamiento')->nullable(); // Agregado campo equipamiento
            $table->enum('estado', ['disponible', 'ocupado', 'mantenimiento'])->default('disponible'); // Corregido campo estado
            $table->integer('capacidad')->default(1); // Agregado campo capacidad
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cubiculos');
    }
};
