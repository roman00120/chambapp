<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('professional_profiles', function (Blueprint $table): void {
            $table->foreignId('verified_by')->nullable()->after('verification_status')->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable()->after('verified_by');
            $table->text('verification_rejection_reason')->nullable()->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('professional_profiles', function (Blueprint $table): void {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verified_by', 'verified_at', 'verification_rejection_reason']);
        });
    }
};
