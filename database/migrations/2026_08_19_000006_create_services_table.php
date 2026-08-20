<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_id')->constrained('professional_profiles')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('price_type', 30)->default('quote');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['professional_id', 'slug']);
            $table->index(['professional_id', 'is_active']);
            $table->index(['category_id', 'is_active']);
            $table->index(['is_featured', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
