<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_reports')) {
            Schema::create('user_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('reported_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('job_request_id')->nullable()->constrained('job_requests')->nullOnDelete();
                $table->string('category', 50)->index();
                $table->string('severity_reported', 20)->default('low');
                $table->text('description');
                $table->string('status', 30)->default('submitted')->index();
                $table->string('resolution', 40)->nullable();
                $table->text('admin_notes_private')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['reporter_id', 'status']);
                $table->index(['reported_id', 'status']);
                $table->index(['created_at']);
            });
        }

        if (! Schema::hasTable('report_evidence')) {
            Schema::create('report_evidence', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_report_id')->constrained('user_reports')->cascadeOnDelete();
                $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('storage_path');
                $table->string('mime_type', 100);
                $table->string('original_name');
                $table->unsignedBigInteger('file_size');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('disciplinary_actions')) {
            Schema::create('disciplinary_actions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('source_report_id')->nullable()->constrained('user_reports')->nullOnDelete();
                $table->string('action_type', 40)->default('yellow_card')->index();
                $table->string('severity', 20)->default('low');
                $table->string('reason_code', 60)->index();
                $table->text('reason_text');
                $table->string('status', 30)->default('active')->index();
                $table->foreignId('issued_by_admin_id')->constrained('users')->cascadeOnDelete();
                $table->dateTime('issued_at');
                $table->dateTime('expires_at')->nullable();
                $table->dateTime('revoked_at')->nullable();
                $table->foreignId('revoked_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('revocation_reason')->nullable();
                $table->text('internal_notes')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['user_id', 'action_type', 'status']);
            });
        }

        if (! Schema::hasTable('disciplinary_appeals')) {
            Schema::create('disciplinary_appeals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('disciplinary_action_id')->constrained('disciplinary_actions')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('appeal_text');
                $table->string('status', 30)->default('submitted')->index();
                $table->foreignId('reviewed_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('reviewed_at')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->timestamps();

                $table->index(['disciplinary_action_id', 'status']);
                $table->index(['user_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_appeals');
        Schema::dropIfExists('disciplinary_actions');
        Schema::dropIfExists('report_evidence');
        Schema::dropIfExists('user_reports');
    }
};
