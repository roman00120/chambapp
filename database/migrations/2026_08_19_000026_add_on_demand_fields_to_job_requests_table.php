<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_requests', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable()->after('professional_id')->constrained('categories')->nullOnDelete();
            $table->string('service_mode', 20)->default('scheduled')->after('category_id')->index();
            $table->timestamp('scheduled_for')->nullable()->after('requested_date')->index();
            $table->string('scheduled_slot', 30)->nullable()->after('scheduled_for');
            $table->timestamp('search_started_at')->nullable()->after('scheduled_slot');
            $table->timestamp('search_expires_at')->nullable()->after('search_started_at')->index();
            $table->timestamp('matched_at')->nullable()->after('search_expires_at');
            $table->timestamp('on_the_way_at')->nullable()->after('matched_at');
            $table->timestamp('arrived_at')->nullable()->after('on_the_way_at');
            $table->unsignedTinyInteger('search_round')->default(1)->after('arrived_at');
            $table->decimal('search_radius_km', 5, 2)->nullable()->after('search_round');
            $table->json('photo_paths')->nullable()->after('search_radius_km');
        });

        Schema::table('job_requests', function (Blueprint $table): void {
            $table->foreignId('professional_id')->nullable()->change();
            $table->index(['category_id', 'service_mode', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('job_requests', function (Blueprint $table): void {
            $table->dropIndex(['category_id', 'service_mode', 'status']);
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn([
                'service_mode', 'scheduled_for', 'scheduled_slot', 'search_started_at',
                'search_expires_at', 'matched_at', 'on_the_way_at', 'arrived_at',
                'search_round', 'search_radius_km', 'photo_paths',
            ]);
            $table->foreignId('professional_id')->nullable(false)->change();
        });
    }
};
