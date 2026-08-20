<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->text('avatar_url')->nullable()->after('google_id');
        });

        Schema::table('job_requests', function (Blueprint $table): void {
            $table->text('completion_code')->nullable()->after('finished_at');
            $table->timestamp('completion_code_expires_at')->nullable()->after('completion_code');
            $table->timestamp('completion_confirmed_at')->nullable()->after('completion_code_expires_at');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->decimal('tip_amount', 12, 2)->default(0)->after('professional_amount');
            $table->decimal('tip_platform_fee', 12, 2)->default(0)->after('tip_amount');
            $table->decimal('tip_professional_amount', 12, 2)->default(0)->after('tip_platform_fee');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn(['tip_amount', 'tip_platform_fee', 'tip_professional_amount']);
        });

        Schema::table('job_requests', function (Blueprint $table): void {
            $table->dropColumn(['completion_code', 'completion_code_expires_at', 'completion_confirmed_at']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['google_id']);
            $table->dropColumn(['google_id', 'avatar_url']);
        });
    }
};
