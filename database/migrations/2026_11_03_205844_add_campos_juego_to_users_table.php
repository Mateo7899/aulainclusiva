<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rol')->default('jugador');        // jugador | administrador
            $table->string('perfil')->nullable();             // cumplidor | competitivo
            $table->boolean('visible_ranking')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rol', 'perfil', 'visible_ranking']);
        });
    }
};
