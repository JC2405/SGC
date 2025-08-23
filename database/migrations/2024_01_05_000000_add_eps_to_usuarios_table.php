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
        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'eps_id')) {
                $table->unsignedBigInteger('eps_id')->nullable();
                $table->foreign('eps_id')->references('id')->on('eps')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'eps_id')) {
                $table->dropForeign(['eps_id']);
                $table->dropColumn('eps_id');
            }
        });
    }
};
