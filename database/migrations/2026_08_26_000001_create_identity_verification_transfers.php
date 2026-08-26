<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_verification_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('professional_id');
            $table->foreignId('identity_verification_id');
            $table->char('token_hash', 64);
            $table->string('provider_session_id', 191);
            $table->text('hosted_url');
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique('token_hash', 'identity_transfer_token_unique');
            $table->index(['professional_id', 'expires_at'], 'identity_transfer_owner_expiry_idx');
            $table->index('provider_session_id', 'identity_transfer_session_idx');
            $table->foreign('professional_id', 'identity_transfer_professional_fk')
                ->references('id')->on('professional_profiles')->cascadeOnDelete();
            $table->foreign('identity_verification_id', 'identity_transfer_verification_fk')
                ->references('id')->on('professional_identity_verifications')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_verification_transfers');
    }
};
