<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professional_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->text('bio')->nullable();
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->string('city')->nullable()->index();
            $table->string('state')->nullable()->index();
            $table->string('postal_code', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('verification_status', 30)->default('unverified')->index();
            $table->string('profile_photo')->nullable();
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->unsignedInteger('total_completed_jobs')->default(0);
            $table->timestamps();

            $table->index(['city', 'state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_profiles');
    }
};
