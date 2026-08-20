@props(['variant' => 'info', 'title' => null, 'dismissible' => false])

<div {{ $attributes->merge(['class' => 'ui-alert ui-alert--'.$variant]) }} role="alert">
    <i class="bi bi-{{ $variant === 'success' ? 'check-circle' : ($variant === 'danger' ? 'exclamation-triangle' : ($variant === 'warning' ? 'exclamation-circle' : 'info-circle')) }}" aria-hidden="true"></i>
    <div class="ui-alert__content">
        @if ($title)
            <strong>{{ $title }}</strong>
        @endif
        <div>{{ $slot }}</div>
    </div>
    @if ($dismissible)
        <button type="button" class="ui-alert__close" data-bs-dismiss="alert" aria-label="Cerrar aviso">&times;</button>
    @endif
</div>
