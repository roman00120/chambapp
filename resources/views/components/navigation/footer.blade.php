<footer class="site-footer">
    <div class="container py-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-4">
            <div>
                <x-ui.brand-mark />
                <p class="small text-body-secondary mt-2 mb-1">Conectando clientes con profesionales de confianza.</p>
                <p class="small text-body-secondary mb-0">&copy; {{ now()->year }} Chambapp. Todos los derechos reservados.</p>
            </div>
            <div class="d-flex flex-column align-items-start align-items-lg-end gap-2">
                <div class="d-flex flex-wrap gap-3 small">
                    <a class="text-link" href="{{ route('legal.terms') }}">Términos y condiciones</a>
                    <a class="text-link" href="{{ route('legal.privacy') }}">Aviso de privacidad</a>
                    <a class="text-link" href="{{ route('legal.cookies') }}">Política de cookies</a>
                    <a class="text-link" href="{{ route('legal.cancellations') }}">Cancelaciones y reembolsos</a>
                    <a class="text-link" href="{{ route('legal.professionals') }}">Para profesionales</a>
                    <a class="text-link" href="{{ route('legal.contact') }}">Contacto y soporte</a>
                </div>
            </div>
        </div>
    </div>
</footer>
