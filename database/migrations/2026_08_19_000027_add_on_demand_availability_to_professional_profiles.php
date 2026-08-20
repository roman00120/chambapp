<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('professional_profiles', function (Blueprint $table): void {
            $table->boolean('is_available')->default(false)->after('verification_status')->index();
            $table->string('availability_status', 20)->default('available')->after('is_available')->index();
            $table->decimal('last_latitude', 10, 7)->nullable()->after('availability_status');
            $table->decimal('last_longitude', 10, 7)->nullable()->after('last_latitude');
            $table->timestamp('location_updated_at')->nullable()->after('last_longitude')->index();
            $table->unsignedSmallInteger('service_radius_km')->default(10)->after('location_updated_at');
            $table->index(['is_available', 'availability_status', 'location_updated_at']);
        });
    }

    public function down(): void
    {
        Schema::table('professional_profiles', function (Blueprint $table): void {
            $table->dropIndex(['is_available', 'availability_status', 'location_updated_at']);
            $table->dropColumn([
                'is_available', 'availability_status', 'last_latitude', 'last_longitude',
                'location_updated_at', 'service_radius_km',
            ]);
        });
    }
};
