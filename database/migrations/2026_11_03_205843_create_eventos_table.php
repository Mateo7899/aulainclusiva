<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reto_id')->nullable()->constrained('retos')->nullOnDelete();
            $table->string('tipo');                         // lectura | aplicacion | bonus_rapido | bonus_racha
            $table->unsignedInteger('puntos')->default(0);
            $table->timestamps();                           // created_at = fecha del evento

            $table->index(['user_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
