<?php

namespace App\Console\Commands;

use App\Models\ProfessionalProfile;
use App\Services\MercadoPagoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class RefreshMercadoPagoTokens extends Command
{
    protected $signature = 'mercadopago:refresh-tokens {--limit=100}';

    protected $description = 'Refresh Mercado Pago seller credentials before they expire';

    public function handle(MercadoPagoService $mercadoPago): int
    {
        $profiles = ProfessionalProfile::query()
            ->whereNotNull('mercadopago_user_id')
            ->whereNotNull('mercadopago_access_token')
            ->whereNotNull('mercadopago_refresh_token')
            ->whereNotNull('mercadopago_token_expires_at')
            ->where('mercadopago_token_expires_at', '<=', now()->addDays(7))
            ->orderBy('mercadopago_token_expires_at')
            ->limit(max(1, min(500, (int) $this->option('limit'))))
            ->get();

        $failures = 0;
        foreach ($profiles as $profile) {
            try {
                $mercadoPago->refreshAccessToken($profile);
            } catch (Throwable $exception) {
                $failures++;
                Log::warning('Mercado Pago token refresh failed', [
                    'professional_profile_id' => $profile->getKey(),
                    'exception' => $exception::class,
                ]);
            }
        }

        $this->info(sprintf('Refreshed %d credential(s); %d failed.', $profiles->count() - $failures, $failures));

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }
}
