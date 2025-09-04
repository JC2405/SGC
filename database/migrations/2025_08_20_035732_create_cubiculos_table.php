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
            $table->string('numero', 255);
            $table->string('nombre', 255);
            $table->enum('tipo', ['consulta', 'procedimientos', 'emergencia']);
            $table->text('equipamiento')->nullable();
            $table->enum('estado', ['disponible', 'ocupado', 'mantenimiento'])->default('disponible');
            $table->integer('capacidad')->default(1);
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
