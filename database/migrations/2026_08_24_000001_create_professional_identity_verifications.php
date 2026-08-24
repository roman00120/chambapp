<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['professional_credentials', 'professional_identity_verifications'] as $partialTable) {
            if (! Schema::hasTable($partialTable)) {
                continue;
            }

            if (DB::table($partialTable)->exists()) {
                throw new \RuntimeException("Cannot repair non-empty partial migration table: {$partialTable}");
            }

            Schema::drop($partialTable);
        }

        Schema::create('professional_identity_verifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('professional_id')->unique()->constrained('professional_profiles')->restrictOnDelete();
            $table->string('verification_provider', 50)->nullable();
            $table->string('provider_verification_id', 191)->nullable();
            $table->unique('provider_verification_id', 'identity_provider_reference_unique');
            $table->string('status', 30)->default('not_started')->index();
            $table->string('verification_level', 50)->nullable();
            $table->string('document_type', 50)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->string('reason_code', 100)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('professional_credentials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('professional_id')->constrained('professional_profiles')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->restrictOnDelete();
            $table->string('credential_type', 50);
            $table->string('provider', 50)->nullable();
            $table->string('provider_reference', 191)->nullable();
            $table->string('status', 30)->default('not_started')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->string('reason_code', 100)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_reason')->nullable();
            $table->timestamps();

            $table->unique(['professional_id', 'category_id', 'credential_type'], 'professional_credential_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_credentials');
        Schema::dropIfExists('professional_identity_verifications');
    }
};
