<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('professional_profiles', 'is_available')) {
            Schema::table('professional_profiles', fn (Blueprint $table) => $table->boolean('is_available')->default(false)->after('verification_status')->index());
        }
        if (! Schema::hasColumn('professional_profiles', 'availability_status')) {
            Schema::table('professional_profiles', fn (Blueprint $table) => $table->string('availability_status', 20)->default('available')->after('is_available')->index());
        }
        if (! Schema::hasColumn('professional_profiles', 'last_latitude')) {
            Schema::table('professional_profiles', fn (Blueprint $table) => $table->decimal('last_latitude', 10, 7)->nullable()->after('availability_status'));
        }
        if (! Schema::hasColumn('professional_profiles', 'last_longitude')) {
            Schema::table('professional_profiles', fn (Blueprint $table) => $table->decimal('last_longitude', 10, 7)->nullable()->after('last_latitude'));
        }
        if (! Schema::hasColumn('professional_profiles', 'location_updated_at')) {
            Schema::table('professional_profiles', fn (Blueprint $table) => $table->timestamp('location_updated_at')->nullable()->after('last_longitude')->index());
        }
        if (! Schema::hasColumn('professional_profiles', 'service_radius_km')) {
            Schema::table('professional_profiles', fn (Blueprint $table) => $table->unsignedSmallInteger('service_radius_km')->default(10)->after('location_updated_at'));
        }
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            Schema::table('professional_profiles', fn (Blueprint $table) => $table->index(['is_available', 'availability_status', 'location_updated_at'], 'profiles_availability_freshness_idx'));

            return;
        }
        $hasAvailabilityIndex = DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', 'professional_profiles')
            ->where('index_name', 'profiles_availability_freshness_idx')
            ->exists();
        if (! $hasAvailabilityIndex) {
            Schema::table('professional_profiles', fn (Blueprint $table) => $table->index(['is_available', 'availability_status', 'location_updated_at'], 'profiles_availability_freshness_idx'));
        }
    }

    public function down(): void
    {
        Schema::table('professional_profiles', function (Blueprint $table): void {
            $table->dropIndex('profiles_availability_freshness_idx');
            $table->dropColumn([
                'is_available', 'availability_status', 'last_latitude', 'last_longitude',
                'location_updated_at', 'service_radius_km',
            ]);
        });
    }
};
