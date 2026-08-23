@extends('layouts.app')

@section('title', 'Pagos | Administración')

@section('content')
<section class="admin-page">
    <div class="container">
        <div class="page-heading">
            <div><p class="eyebrow">Finanzas</p><h1 class="page-title">Pagos</h1></div>
        </div>
        <x-ui.card class="mb-4" padding="md">
            <form class="admin-filter-bar" method="GET">
                <input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Referencia externa">
                <select class="form-select form-select-sm" name="status">
                    <option value="">Todos los estados</option>
                    @foreach (['pending', 'processing', 'approved', 'rejected', 'cancelled', 'in_mediation', 'refunded', 'partially_refunded', 'charged_back'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <button class="ui-button ui-button--primary ui-button--sm">Filtrar</button>
            </form>
        </x-ui.card>
        <div class="admin-table-wrap">
            <table class="table admin-table">
                <thead><tr><th>Payment</th><th>Participantes</th><th>Bruto</th><th>Comisión</th><th>Profesional</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>#{{ $payment->id }}<small class="d-block text-muted">{{ $payment->external_reference }}</small></td>
                            <td>{{ $payment->client->name }}<small class="d-block text-muted">{{ $payment->professional->user->name }}</small></td>
                            <td>{{ $payment->gross_amount }} {{ $payment->currency }}</td>
                            <td>{{ $payment->platform_fee_percent }}% · {{ $payment->platform_fee }}</td>
                            <td>{{ $payment->professional_amount }}</td>
                            <td>{{ $payment->status->value }}</td>
                            <td><a class="ui-button ui-button--outline ui-button--sm" href="{{ route('admin.payments.show', $payment) }}">Detalle</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Sin pagos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($payments->hasPages())
            <div class="mt-4"><x-pagination :paginator="$payments" /></div>
        @endif
    </div>
</section>
@endsection
