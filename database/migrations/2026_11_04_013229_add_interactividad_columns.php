<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retos', function (Blueprint $table) {
            $table->string('tipo_actividad')->default('escenario');
            $table->json('actividad')->nullable();
        });

        Schema::table('eventos', function (Blueprint $table) {
            $table->unsignedSmallInteger('errores')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('retos', function (Blueprint $table) {
            $table->dropColumn(['tipo_actividad', 'actividad']);
        });

        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('errores');
        });
    }
};