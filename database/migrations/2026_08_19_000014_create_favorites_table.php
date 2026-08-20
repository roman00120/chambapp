<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('professional_id')->constrained('professional_profiles')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'professional_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
