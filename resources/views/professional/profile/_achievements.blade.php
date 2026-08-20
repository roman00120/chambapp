@if ($profile->achievements())
<x-ui.card class="mt-4" padding="lg"><p class="eyebrow mb-2">Logros</p><h2 class="h5">Tu reputacion se construye con cada trabajo</h2><div class="d-flex flex-wrap gap-2 mt-3">@foreach ($profile->achievements() as $achievement)<span class="profile-achievement"><i class="bi {{ $achievement['icon'] }}" aria-hidden="true"></i><span><strong>{{ $achievement['title'] }}</strong><small>{{ $achievement['text'] }}</small></span></span>@endforeach</div></x-ui.card>
@endif
