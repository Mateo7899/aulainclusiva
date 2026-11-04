<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insignia_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insignia_id')->constrained('insignias')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'insignia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insignia_user');
    }
};
