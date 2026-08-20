@extends('layouts.app')
@section('title', 'Tienda de perfil | Chambapp')
@section('content')
<section class="professional-page"><div class="container container--narrow"><div class="page-heading"><div><p class="eyebrow">Tienda Chambapp</p><h1 class="page-title">Haz más vistoso tu perfil.</h1><p class="section-copy">Compra personalizaciones que se aplican automáticamente después del pago.</p></div></div>
@if ($errors->any())<x-ui.alert variant="danger">{{ $errors->first() }}</x-ui.alert>@endif
<div class="row g-3">@foreach ($items as $key => $item)<div class="col-12 col-md-6"><x-ui.card class="h-100" padding="lg"><span class="eyebrow">{{ ucfirst($item['kind']) }}</span><h2 class="h5">{{ $item['name'] }}</h2><p class="text-muted">Personalización para tu perfil profesional.</p><strong class="d-block mb-3">${{ $item['price'] }} MXN</strong><form method="POST" action="{{ route('professional.commerce.store.buy', $key) }}">@csrf<x-ui.button class="w-100" type="submit">Comprar</x-ui.button></form></x-ui.card></div>@endforeach</div></div></section>
@endsection
