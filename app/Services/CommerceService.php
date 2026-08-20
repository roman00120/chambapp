<?php

namespace App\Services;

use App\Models\CommerceOrder;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use DomainException;
use Illuminate\Support\Facades\DB;

class CommerceService
{
    public function __construct(private readonly MercadoPagoService $mercadoPago) {}

    public function createFeaturedOrder(ProfessionalProfile $professional, Service $service, int $days): CommerceOrder
    {
        abort_unless($service->professional_id === $professional->getKey(), 403);
        $prices = config('chambapp.commerce.featured_prices', []);
        if (! isset($prices[$days])) {
            throw new DomainException('La duración de la promoción no es válida.');
        }

        return $this->createOrder($professional, 'featured', $service, 'featured-'.$days, (string) $prices[$days], ['days' => $days]);
    }

    public function createCustomizationOrder(ProfessionalProfile $professional, string $itemKey): CommerceOrder
    {
        $item = config('chambapp.commerce.store_items.'.$itemKey);
        if (! is_array($item)) {
            throw new DomainException('El artículo de la tienda no existe.');
        }

        return $this->createOrder($professional, 'customization', null, $itemKey, (string) $item['price'], $item);
    }

    public function checkout(CommerceOrder $order): CommerceOrder
    {
        $item = $order->metadata ?? [];
        $preference = $this->mercadoPago->createPlatformPreference((string) ($item['name'] ?? $order->item_key), (string) $order->amount, (string) $order->external_reference);
        $order->forceFill(['external_preference_id' => $preference['id'], 'checkout_url' => $preference['url']])->save();

        return $order->fresh();
    }

    public function applyPaidOrder(CommerceOrder $order): CommerceOrder
    {
        return DB::transaction(function () use ($order): CommerceOrder {
            $order = CommerceOrder::query()->lockForUpdate()->findOrFail($order->getKey());
            if ($order->status === 'approved') {
                return $order;
            }
            $order->forceFill(['status' => 'approved', 'paid_at' => now()])->save();
            if ($order->kind === 'featured' && $order->service) {
                $days = (int) data_get($order->metadata, 'days', 1);
                $start = $order->service->featured_until?->isFuture() ? $order->service->featured_until : now();
                $order->service->forceFill(['is_featured' => true, 'featured_until' => $start->copy()->addDays($days)])->save();
            }
            if ($order->kind === 'customization') {
                $this->applyCustomization($order->professional, $order->metadata ?? []);
            }

            return $order->fresh();
        });
    }

    private function createOrder(ProfessionalProfile $professional, string $kind, ?Service $service, string $itemKey, string $amount, array $metadata): CommerceOrder
    {
        return DB::transaction(function () use ($professional, $kind, $service, $itemKey, $amount, $metadata): CommerceOrder {
            $order = CommerceOrder::create([
                'professional_id' => $professional->getKey(),
                'kind' => $kind,
                'service_id' => $service?->getKey(),
                'item_key' => $itemKey,
                'amount' => $amount,
                'currency' => config('chambapp.payments.currency', 'MXN'),
                'status' => 'pending',
                'external_reference' => 'CHAMBAPP-COM-'.$professional->getKey().'-'.str()->random(12),
                'metadata' => $metadata,
            ]);

            return $order;
        });
    }

    private function applyCustomization(ProfessionalProfile $professional, array $item): void
    {
        $field = match ($item['kind'] ?? null) {
            'theme' => 'profile_theme',
            'banner' => 'profile_banner',
            'frame' => 'profile_frame',
            'animation' => 'profile_animation',
            default => null,
        };
        if ($field) {
            $professional->forceFill([$field => $item['value']])->save();
        }
    }
}
