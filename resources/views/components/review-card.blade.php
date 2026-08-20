@props(['review', 'reportable' => false])

<article class="review-card">
    <div class="review-card__header">
        <div class="d-flex align-items-center gap-2">
            <x-ui.avatar :name="$review->publicClientName()" size="sm" />
            <div><strong>{{ $review->publicClientName() }}</strong><small class="d-block text-muted">{{ $review->created_at?->locale('es')->translatedFormat('d M Y') }}</small></div>
        </div>
        <span class="review-card__rating" aria-label="{{ $review->rating }} de 5 estrellas">{{ str_repeat('★', $review->rating) }}<span class="visually-hidden"> {{ $review->rating }} de 5</span></span>
    </div>
    @if ($review->comment)<p class="review-card__comment">{{ $review->comment }}</p>@endif
    <div class="review-card__footer"><span><i class="bi bi-patch-check-fill" aria-hidden="true"></i> Trabajo realizado en Chambapp</span>@if ($review->jobRequest?->service)<span>Servicio: {{ $review->jobRequest->service->title }}</span>@endif</div>
    @if ($reportable && auth()->check() && auth()->user()->isProfessional())
        <details class="review-card__report mt-3"><summary>Reportar reseña</summary><form method="POST" action="{{ route('reviews.report', $review) }}" class="mt-2">@csrf<div class="mb-2"><label class="visually-hidden" for="report-reason-{{ $review->id }}">Motivo</label><select class="form-select form-select-sm" id="report-reason-{{ $review->id }}" name="reason" required><option value="">Motivo</option><option value="offensive">Contenido ofensivo</option><option value="personal_data">Datos personales</option><option value="spam">Spam</option><option value="unrelated">No relacionado</option></select></div><textarea class="form-control form-control-sm mb-2" name="description" maxlength="500" placeholder="Detalle opcional"></textarea><button class="ui-button ui-button--outline ui-button--sm" type="submit">Enviar reporte</button></form></details>
    @endif
</article>
