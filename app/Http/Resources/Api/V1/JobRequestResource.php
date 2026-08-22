<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\JobStatus;
use App\Enums\PaymentKind;
use App\Enums\PaymentStatus;
use App\Models\JobRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $participant = $user && ($this->client_id === $user->id || $this->professional?->user_id === $user->id);
        $paid = $this->relationLoaded('payments')
            ? $this->payments->contains(fn ($payment) => $payment->kind === PaymentKind::JOB && $payment->status === PaymentStatus::APPROVED)
            : $this->payments()
                ->where('kind', PaymentKind::JOB->value)
                ->where('status', PaymentStatus::APPROVED->value)
                ->exists();
        $private = $participant && $paid;
        $routeJob = $request->route('job');
        $isDetailRequest = $routeJob instanceof JobRequest
            && $routeJob->getKey() === $this->getKey();
        $canSeeCompletionCode = $isDetailRequest
            && $user?->isClient() === true
            && $this->client_id === $user->getKey()
            && $this->status === JobStatus::AWAITING_CONFIRMATION
            && filled($this->completion_code)
            && ($this->completion_code_expires_at === null || ! $this->completion_code_expires_at->isPast());

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
            'review' => $this->when(
                $this->relationLoaded('review'),
                fn () => $this->review ? new ReviewResource($this->review) : null,
            ),
            'completion_code' => $this->when($canSeeCompletionCode, (string) $this->completion_code),
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
