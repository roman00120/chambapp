@props(['message' => null, 'variant' => 'success'])

<div class="toast-container position-fixed bottom-0 end-0 p-3" aria-live="polite" aria-atomic="true">
    <div class="toast ui-toast ui-toast--{{ $variant }}" role="status" data-ui-toast>
        <div class="toast-body d-flex align-items-center gap-2">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            <span data-ui-toast-message>{{ $message ?? 'Listo.' }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>
</div>
