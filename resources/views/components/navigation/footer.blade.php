<footer class="site-footer">
    <div class="container py-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3">
            <div>
                <x-ui.brand-mark />
                <p class="small text-body-secondary mt-2 mb-0">Servicios que se sienten cerca.</p>
            </div>
            <div class="d-flex flex-column align-items-start align-items-sm-end gap-1">
                <p class="small text-body-secondary mb-0">&copy; {{ now()->year }} Chambapp</p>
                <div class="d-flex gap-3 small">
                    <a class="text-link" href="{{ route('legal.terms') }}">Términos</a>
                    <a class="text-link" href="{{ route('legal.privacy') }}">Privacidad</a>
                </div>
            </div>
        </div>
    </div>
</footer>
