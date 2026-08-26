<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_acceptances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->string('document_type', 50);
            $table->string('document_version', 100);
            $table->timestamp('accepted_at');
            $table->string('platform', 30);
            $table->char('ip_hash', 64)->nullable();
            $table->char('user_agent_hash', 64)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'document_type', 'document_version'], 'legal_acceptance_user_document_unique');
            $table->index(['document_type', 'document_version'], 'legal_acceptance_document_version_idx');
            $table->foreign('user_id', 'legal_acceptance_user_fk')
                ->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_acceptances');
    }
};
