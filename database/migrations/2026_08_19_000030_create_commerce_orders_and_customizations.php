<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->timestamp('featured_until')->nullable()->after('is_featured')->index();
        });

        Schema::table('professional_profiles', function (Blueprint $table): void {
            $table->string('profile_theme', 40)->default('default')->after('profile_photo');
            $table->string('profile_banner', 40)->default('paper')->after('profile_theme');
            $table->string('profile_frame', 40)->default('none')->after('profile_banner');
            $table->string('profile_animation', 40)->default('none')->after('profile_frame');
            $table->string('profile_accent', 7)->default('#e87818')->after('profile_animation');
        });

        Schema::create('commerce_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('professional_id')->constrained('professional_profiles')->cascadeOnDelete();
            $table->string('kind', 30)->index();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('item_key', 80);
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('MXN');
            $table->string('status', 30)->default('pending')->index();
            $table->string('external_reference')->unique();
            $table->string('external_preference_id')->nullable()->index();
            $table->text('checkout_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_orders');

        Schema::table('professional_profiles', function (Blueprint $table): void {
            $table->dropColumn(['profile_theme', 'profile_banner', 'profile_frame', 'profile_animation', 'profile_accent']);
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->dropIndex(['featured_until']);
            $table->dropColumn('featured_until');
        });
    }
};
