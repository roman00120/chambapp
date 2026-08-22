@props(['status', 'audience' => request()->user()?->isProfessional() ? 'professional' : 'client'])

@php
    $value = $status?->value ?? (string) $status;
    [$label, $variant] = match ($value) {
        'pending' => ['Pendiente', 'warning'],
        'searching' => ['Buscando profesional', 'info'],
        'matched' => ['Profesional encontrado', 'success'],
        'awaiting_quote' => ['Esperando cotización', 'warning'],
        'accepted' => ['Aceptado', 'success'],
        'awaiting_payment' => ['Pendiente de pago', 'warning'],
        'paid' => ['Pagado', 'success'],
        'on_the_way' => ['En camino', 'info'],
        'arrived' => ['Llegó', 'info'],
        'processing' => ['Procesando', 'info'],
        'approved' => ['Pago aprobado', 'success'],
        'refunded' => ['Reembolsado', 'neutral'],
        'partially_refunded' => ['Reembolso parcial', 'warning'],
        'charged_back' => ['Contracargo', 'danger'],
        'in_mediation' => ['En mediación', 'danger'],
        'cancelled' => ['Cancelado', 'danger'],
        'rejected' => ['Rechazado', 'danger'],
        'in_progress' => ['En proceso', 'info'],
        'awaiting_confirmation' => [$audience === 'professional' ? 'Esperando confirmación del cliente' : 'Esperando tu confirmación', 'warning'],
        'completed' => ['Completado', 'success'],
        'disputed' => ['En disputa', 'danger'],
        'expired' => ['Expirada', 'neutral'],
        default => ['En revisión', 'neutral'],
    };
@endphp

<x-ui.badge :variant="$variant" :label="$label" />
