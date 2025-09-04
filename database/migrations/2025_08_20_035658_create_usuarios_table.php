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
      Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->string('apellido', 255);
            $table->string('documento_identidad', 255)->unique();
            $table->string('email', 255)->unique();
            $table->string('telefono', 255)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->timestamps();

            // Clave foránea hacia eps
            $table->foreignId('eps_id')
                  ->constrained('eps')
                  ->onUpdate('cascade')
                  ->onDelete('restrict'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
