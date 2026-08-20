@extends('layouts.app')

@section('title', 'Mis pagos | Chambapp')

@section('content')
    <section class="payment-page"><div class="container"><div class="page-heading"><div><p class="eyebrow mb-2"><i class="bi bi-receipt" aria-hidden="true"></i> Actividad</p><h1 class="page-title">Mis pagos</h1><p class="section-copy mb-0">Consulta el estado de tus operaciones dentro de Chambapp.</p></div></div><div class="payment-history-list mt-4">@forelse ($payments as $payment)<article class="payment-history-card"><div><p class="job-card__eyebrow">{{ $payment->jobRequest?->service?->title ?? 'Trabajo' }}</p><h2 class="h5 mb-1">{{ $payment->jobRequest?->title }}</h2><p class="small text-muted mb-0">Profesional: {{ $payment->professional?->user?->name }} · {{ $payment->created_at->locale('es')->translatedFormat('d M Y') }}</p></div><div class="text-md-end"><strong>${{ $payment->gross_amount }} MXN</strong><x-job-status-badge :status="$payment->status" /></div></article>@empty<x-ui.empty-state icon="bi-receipt" title="Aún no tienes pagos" description="Tus pagos aparecerán aquí cuando inicies una operación." />@endforelse</div>@if ($payments->hasPages())<div class="mt-4"><x-pagination :paginator="$payments" /></div>@endif</div></section>
@endsection
