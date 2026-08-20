@extends('layouts.app')

@section('title', 'Estado del pago | Chambapp')

@section('content')
    <section class="payment-page"><div class="container"><x-ui.card class="payment-return-card" padding="lg"><span class="payment-placeholder__icon"><i class="bi bi-{{ $state === 'success' ? 'hourglass-split' : ($state === 'pending' ? 'clock-history' : 'exclamation-triangle') }}" aria-hidden="true"></i></span>@if ($state === 'success')<h1 class="page-title">Estamos verificando tu pago</h1><p class="section-copy">La confirmación definitiva llegará mediante Mercado Pago y se reflejará aquí cuando el servidor la valide.</p>@elseif ($state === 'pending')<h1 class="page-title">Tu pago está siendo procesado</h1><p class="section-copy">El trabajo continúa pendiente de pago hasta recibir una confirmación válida.</p>@else<h1 class="page-title">No pudimos completar el pago</h1><p class="section-copy">Puedes intentar nuevamente desde el resumen del pago. No mostramos detalles internos del procesador.</p>@endif<a class="ui-button ui-button--outline mt-3" href="{{ route('client.jobs.index') }}">Volver a mis trabajos</a></x-ui.card></div></section>
@endsection
