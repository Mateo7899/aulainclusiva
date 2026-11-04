<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reto_id')->constrained('retos')->cascadeOnDelete();
            $table->date('fecha');
            $table->timestamps();

            $table->unique(['user_id', 'fecha']);   // un giro por día
        });

        Schema::table('retos', function (Blueprint $table) {
            $table->text('consejo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giros');

        Schema::table('retos', function (Blueprint $table) {
            $table->dropColumn('consejo');
        });
    }
};
