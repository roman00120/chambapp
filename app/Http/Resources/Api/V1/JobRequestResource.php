<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $participant = $user && ($this->client_id === $user->id || $this->professional?->user_id === $user->id);
        $paid = $this->relationLoaded('payments')
            ? $this->payments->contains(fn ($payment) => $payment->status === PaymentStatus::APPROVED)
            : $this->payments()->where('status', PaymentStatus::APPROVED->value)->exists();
        $private = $participant && $paid;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'service_mode' => $this->service_mode?->value,
            'status' => $this->status?->value,
            'status_label' => $this->statusLabel(),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'service' => new ServiceResource($this->whenLoaded('service')),
            'professional' => new ProfessionalResource($this->whenLoaded('professional')),
            'city' => $this->city,
            'state' => $this->state,
            'address' => $this->when($private, $this->address),
            'postal_code' => $this->when($private, $this->postal_code),
            'latitude' => $this->when($private, $this->latitude),
            'longitude' => $this->when($private, $this->longitude),
            'scheduled_for' => $this->scheduled_for?->toIso8601String(),
            'scheduled_slot' => $this->scheduled_slot,
            'agreed_price' => $this->agreed_price !== null ? (string) $this->agreed_price : null,
            'currency' => config('chambapp.payments.currency'),
            'search_round' => $this->search_round,
            'search_radius_km' => $this->search_radius_km !== null ? (string) $this->search_radius_km : null,
            'search_expires_at' => $this->search_expires_at?->toIso8601String(),
            'quotes' => JobQuoteResource::collection($this->whenLoaded('quotes')),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function statusLabel(): ?string
    {
        return match ($this->status?->value) {
            'searching' => 'Buscando profesional',
            'matched' => 'Profesional encontrado',
            'awaiting_quote' => 'Esperando cotización',
            'awaiting_payment' => 'Esperando pago',
            'paid' => 'Pagado',
            'on_the_way' => 'En camino',
            'arrived' => 'Profesional en el lugar',
            'in_progress' => 'Trabajo en proceso',
            'awaiting_confirmation' => 'Esperando confirmación',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado',
            'expired' => 'Expirado',
            default => $this->status?->value,
        };
    }
}
