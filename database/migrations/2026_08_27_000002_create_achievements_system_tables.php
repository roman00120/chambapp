<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('achievements')) {
            Schema::create('achievements', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 100);
                $table->text('description');
                $table->string('category', 50)->default('merit')->index();
                $table->string('audience', 30)->default('all')->index();
                $table->string('icon', 60)->default('award');
                $table->boolean('is_public')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('user_achievements')) {
            Schema::create('user_achievements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('achievement_id')->constrained('achievements')->cascadeOnDelete();
                $table->string('level', 30)->default('bronze')->index();
                $table->dateTime('earned_at');
                $table->dateTime('revoked_at')->nullable();
                $table->string('revocation_reason')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'achievement_id']);
                $table->index(['user_id', 'revoked_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
    }
};
