<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->boolean('is_hidden')->default(false)->after('comment')->index();
            $table->foreignId('hidden_by')->nullable()->after('is_hidden')->constrained('users')->nullOnDelete();
            $table->dateTime('hidden_at')->nullable()->after('hidden_by');
            $table->text('moderation_reason')->nullable()->after('hidden_at');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropForeign(['hidden_by']);
            $table->dropColumn(['is_hidden', 'hidden_by', 'hidden_at', 'moderation_reason']);
        });
    }
};
