<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('professional_profiles', function (Blueprint $table): void {
            $table->string('mercadopago_user_id')->nullable()->index();
            $table->text('mercadopago_access_token')->nullable();
            $table->text('mercadopago_refresh_token')->nullable();
            $table->string('mercadopago_public_key')->nullable();
            $table->dateTime('mercadopago_token_expires_at')->nullable();
            $table->dateTime('mercadopago_connected_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('professional_profiles', function (Blueprint $table): void {
            $table->dropIndex(['mercadopago_user_id']);
            $table->dropColumn([
                'mercadopago_user_id', 'mercadopago_access_token', 'mercadopago_refresh_token',
                'mercadopago_public_key', 'mercadopago_token_expires_at', 'mercadopago_connected_at',
            ]);
        });
    }
};
