<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_requests', function (Blueprint $table): void {
            $table->dateTime('finished_at')->nullable()->after('started_at');
            $table->string('cancellation_reason')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('job_requests', function (Blueprint $table): void {
            $table->dropColumn(['finished_at', 'cancellation_reason']);
        });
    }
};
