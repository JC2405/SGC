<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
         Schema::create('eps', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->string('codigo', 255)->unique();
            $table->string('nit', 255)->unique();
            $table->string('telefono', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('direccion')->nullable();
            $table->enum('estado', ['activa', 'inactiva'])->default('activa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eps');
    }
};
