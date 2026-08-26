<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('professional_identity_verifications', 'provider_session_id')) {
            Schema::table('professional_identity_verifications', function (Blueprint $table): void {
                $table->string('provider_session_id', 191)->nullable()->after('provider_verification_id');
            });
        }

        if (! Schema::hasColumn('professional_identity_verifications', 'provider_status')) {
            Schema::table('professional_identity_verifications', function (Blueprint $table): void {
                $table->string('provider_status', 50)->nullable()->after('status');
            });
        }

        if (! Schema::hasColumn('professional_identity_verifications', 'started_at')) {
            Schema::table('professional_identity_verifications', function (Blueprint $table): void {
                $table->timestamp('started_at')->nullable()->after('provider_status');
            });
        }

        if (! Schema::hasColumn('professional_identity_verifications', 'last_provider_sync_at')) {
            Schema::table('professional_identity_verifications', function (Blueprint $table): void {
                $table->timestamp('last_provider_sync_at')->nullable()->after('expires_at');
            });
        }

        $providerSessionIndexExists = collect(Schema::getIndexes('professional_identity_verifications'))
            ->contains(fn (array $index): bool => $index['name'] === 'identity_provider_session_unique');

        if (! $providerSessionIndexExists) {
            Schema::table('professional_identity_verifications', function (Blueprint $table): void {
                $table->unique('provider_session_id', 'identity_provider_session_unique');
            });
        }

        if (! Schema::hasTable('professional_identity_verification_consents')) {
            Schema::create('professional_identity_verification_consents', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('professional_id');
                $table->foreignId('identity_verification_id');
                $table->string('consent_version', 50);
                $table->string('privacy_notice_version', 50);
                $table->string('purpose', 100);
                $table->timestamp('accepted_at');
                $table->char('ip_hash', 64)->nullable();
                $table->char('user_agent_hash', 64)->nullable();
                $table->timestamps();

                $table->foreign('professional_id', 'didit_consent_professional_fk')
                    ->references('id')->on('professional_profiles')->restrictOnDelete();
                $table->foreign('identity_verification_id', 'didit_consent_verification_fk')
                    ->references('id')->on('professional_identity_verifications')->cascadeOnDelete();
                $table->index(['professional_id', 'accepted_at'], 'identity_consent_professional_date');
            });
        } else {
            $this->repairConsentForeignKeys();
        }

        if (! Schema::hasTable('professional_identity_verification_events')) {
            Schema::create('professional_identity_verification_events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('identity_verification_id');
                $table->string('provider_session_id', 191)->nullable();
                $table->string('source', 30);
                $table->string('from_status', 30)->nullable();
                $table->string('to_status', 30);
                $table->string('reason_code', 100)->nullable();
                $table->timestamp('occurred_at');
                $table->timestamps();

                $table->foreign('identity_verification_id', 'didit_event_verification_fk')
                    ->references('id')->on('professional_identity_verifications')->cascadeOnDelete();
                $table->index('provider_session_id', 'didit_event_session_idx');
            });
        } else {
            $this->repairVerificationEventTable();
        }

        if (! Schema::hasTable('didit_webhook_events')) {
            Schema::create('didit_webhook_events', function (Blueprint $table): void {
                $table->id();
                $table->string('event_id', 191);
                $table->string('webhook_type', 100);
                $table->string('provider_session_id', 191);
                $table->char('payload_hash', 64);
                $table->string('processing_status', 30)->default('received');
                $table->string('failure_code', 100)->nullable();
                $table->timestamp('received_at');
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->unique('event_id', 'didit_webhook_event_unique');
                $table->index('provider_session_id', 'didit_webhook_session_idx');
                $table->index('processing_status', 'didit_webhook_status_idx');
            });
        }
    }

    private function repairConsentForeignKeys(): void
    {
        $requiredColumns = [
            'professional_id',
            'identity_verification_id',
            'consent_version',
            'privacy_notice_version',
            'purpose',
            'accepted_at',
            'ip_hash',
            'user_agent_hash',
            'created_at',
            'updated_at',
        ];

        foreach ($requiredColumns as $column) {
            if (! Schema::hasColumn('professional_identity_verification_consents', $column)) {
                throw new RuntimeException("The partial Didit consent table is missing the {$column} column.");
            }
        }

        if (DB::getDriverName() !== 'mysql') {
            throw new RuntimeException('Partial Didit migration recovery is only supported on MySQL.');
        }

        $foreignKeyExists = static fn (string $column): bool => DB::selectOne(
            <<<'SQL'
                select 1 as present
                from information_schema.key_column_usage
                where table_schema = database()
                  and table_name = 'professional_identity_verification_consents'
                  and column_name = ?
                  and referenced_table_name is not null
                limit 1
                SQL,
            [$column],
        ) !== null;

        if (! $foreignKeyExists('professional_id')) {
            Schema::table('professional_identity_verification_consents', function (Blueprint $table): void {
                $table->foreign('professional_id', 'didit_consent_professional_fk')
                    ->references('id')->on('professional_profiles')->restrictOnDelete();
            });
        }

        if (! $foreignKeyExists('identity_verification_id')) {
            Schema::table('professional_identity_verification_consents', function (Blueprint $table): void {
                $table->foreign('identity_verification_id', 'didit_consent_verification_fk')
                    ->references('id')->on('professional_identity_verifications')->cascadeOnDelete();
            });
        }

        $consentIndexExists = collect(Schema::getIndexes('professional_identity_verification_consents'))
            ->contains(fn (array $index): bool => $index['name'] === 'identity_consent_professional_date');

        if (! $consentIndexExists) {
            Schema::table('professional_identity_verification_consents', function (Blueprint $table): void {
                $table->index(['professional_id', 'accepted_at'], 'identity_consent_professional_date');
            });
        }
    }

    private function repairVerificationEventTable(): void
    {
        $requiredColumns = [
            'identity_verification_id',
            'provider_session_id',
            'source',
            'from_status',
            'to_status',
            'reason_code',
            'occurred_at',
            'created_at',
            'updated_at',
        ];

        foreach ($requiredColumns as $column) {
            if (! Schema::hasColumn('professional_identity_verification_events', $column)) {
                throw new RuntimeException("The partial Didit event table is missing the {$column} column.");
            }
        }

        if (DB::getDriverName() !== 'mysql') {
            throw new RuntimeException('Partial Didit migration recovery is only supported on MySQL.');
        }

        $foreignKeyExists = DB::selectOne(
            <<<'SQL'
                select 1 as present
                from information_schema.key_column_usage
                where table_schema = database()
                  and table_name = 'professional_identity_verification_events'
                  and column_name = 'identity_verification_id'
                  and referenced_table_name is not null
                limit 1
                SQL,
        ) !== null;

        if (! $foreignKeyExists) {
            Schema::table('professional_identity_verification_events', function (Blueprint $table): void {
                $table->foreign('identity_verification_id', 'didit_event_verification_fk')
                    ->references('id')->on('professional_identity_verifications')->cascadeOnDelete();
            });
        }

        $sessionIndexExists = collect(Schema::getIndexes('professional_identity_verification_events'))
            ->contains(fn (array $index): bool => $index['name'] === 'didit_event_session_idx');

        if (! $sessionIndexExists) {
            Schema::table('professional_identity_verification_events', function (Blueprint $table): void {
                $table->index('provider_session_id', 'didit_event_session_idx');
            });
        }
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
