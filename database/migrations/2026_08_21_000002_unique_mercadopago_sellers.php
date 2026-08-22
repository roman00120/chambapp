<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('professional_profiles')
            ->select('mercadopago_user_id')
            ->whereNotNull('mercadopago_user_id')
            ->groupBy('mercadopago_user_id')
            ->havingRaw('COUNT(*) > 1')
            ->limit(5)
            ->get();
        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException('No se puede asegurar mercadopago_user_id: existen cuentas de vendedor duplicadas.');
        }

        Schema::table('professional_profiles', function (Blueprint $table): void {
            $table->dropIndex(['mercadopago_user_id']);
            $table->unique('mercadopago_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('professional_profiles', function (Blueprint $table): void {
            $table->dropUnique(['mercadopago_user_id']);
            $table->index('mercadopago_user_id');
        });
    }
};
