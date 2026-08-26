<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('professional_identity_verifications', function (Blueprint $table): void {
            $table->string('provider_session_id', 191)->nullable()->after('provider_verification_id');
            $table->unique('provider_session_id', 'identity_provider_session_unique');
            $table->string('provider_status', 50)->nullable()->after('status');
            $table->timestamp('started_at')->nullable()->after('provider_status');
            $table->timestamp('last_provider_sync_at')->nullable()->after('expires_at');
        });

        Schema::create('professional_identity_verification_consents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('professional_id')->constrained('professional_profiles')->restrictOnDelete();
            $table->foreignId('identity_verification_id')->constrained('professional_identity_verifications')->cascadeOnDelete();
            $table->string('consent_version', 50);
            $table->string('privacy_notice_version', 50);
            $table->string('purpose', 100);
            $table->timestamp('accepted_at');
            $table->char('ip_hash', 64)->nullable();
            $table->char('user_agent_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['professional_id', 'accepted_at'], 'identity_consent_professional_date');
        });

        Schema::create('professional_identity_verification_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('identity_verification_id')->constrained('professional_identity_verifications')->cascadeOnDelete();
            $table->string('provider_session_id', 191)->nullable()->index();
            $table->string('source', 30);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->string('reason_code', 100)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });

        Schema::create('didit_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_id', 191)->unique();
            $table->string('webhook_type', 100);
            $table->string('provider_session_id', 191)->index();
            $table->char('payload_hash', 64);
            $table->string('processing_status', 30)->default('received')->index();
            $table->string('failure_code', 100)->nullable();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('didit_webhook_events');
        Schema::dropIfExists('professional_identity_verification_events');
        Schema::dropIfExists('professional_identity_verification_consents');

        Schema::table('professional_identity_verifications', function (Blueprint $table): void {
            $table->dropUnique('identity_provider_session_unique');
            $table->dropColumn([
                'provider_session_id',
                'provider_status',
                'started_at',
                'last_provider_sync_at',
            ]);
        });
    }
};
