import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'bootstrap';

if ('serviceWorker' in navigator && window.isSecureContext) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}

document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        const input = document.querySelector(toggle.dataset.target);

        if (!input) {
            return;
        }

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        toggle.textContent = isPassword ? 'Ocultar' : 'Mostrar';
        toggle.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
    });
});

const uiToast = document.querySelector('[data-ui-toast]');
const uiToastMessage = document.querySelector('[data-ui-toast-message]');

document.querySelectorAll('[data-coming-soon]').forEach((element) => {
    element.addEventListener('click', (event) => {
        event.preventDefault();

        if (!uiToast || !uiToastMessage) {
            return;
        }

        uiToastMessage.textContent = 'Esta sección estará disponible en una próxima fase.';
        bootstrap.Toast.getOrCreateInstance(uiToast).show();
    });
});

const updatePriceField = (select) => {
    const wrapper = document.querySelector('[data-price-wrapper]');
    const input = document.querySelector('[data-price-input]');

    if (!wrapper || !input) {
        return;
    }

    const isQuote = select.value === 'quote';
    wrapper.hidden = isQuote;
    input.disabled = isQuote;
    input.setAttribute('aria-hidden', isQuote ? 'true' : 'false');
};

document.querySelectorAll('[data-price-type]').forEach((select) => {
    updatePriceField(select);
    select.addEventListener('change', () => updatePriceField(select));
});

document.querySelectorAll('[data-avatar-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const file = input.files?.[0];
        const preview = document.querySelector('#profile-avatar-preview');

        if (!file || !preview) {
            return;
        }

        let image = preview.querySelector('img');
        if (!image) {
            preview.textContent = '';
            image = document.createElement('img');
            preview.appendChild(image);
        }

        image.src = URL.createObjectURL(file);
        image.alt = 'Vista previa de la foto de perfil';
    });
});

document.querySelectorAll('[data-service-images]').forEach((input) => {
    input.addEventListener('change', () => {
        const preview = document.querySelector('[data-service-preview]');
        if (!preview) {
            return;
        }

        preview.innerHTML = '';
        const existingCount = Number(input.dataset.existingCount || 0);
        Array.from(input.files || []).forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'service-upload-preview__item';
            const image = document.createElement('img');
            image.src = URL.createObjectURL(file);
            image.alt = `Vista previa ${index + 1}`;
            image.loading = 'lazy';

            const label = document.createElement('label');
            label.className = 'form-check small';
            const radio = document.createElement('input');
            radio.className = 'form-check-input';
            radio.type = 'radio';
            radio.name = 'cover_index';
            radio.value = String(index);
            radio.checked = existingCount === 0 && index === 0;
            label.append(radio, ' Portada');
            item.append(image, label);
            preview.appendChild(item);
        });
    });
});

const confirmModal = document.querySelector('[data-ui-confirm-modal]');
const confirmMessage = document.querySelector('[data-ui-confirm-message]');
const confirmSubmit = document.querySelector('[data-ui-confirm-submit]');
let pendingConfirmationForm = null;

document.querySelectorAll('[data-confirm-delete-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!confirmModal || !confirmSubmit) {
            return;
        }

        event.preventDefault();
        pendingConfirmationForm = form;
        if (confirmMessage) {
            confirmMessage.textContent = form.action.includes('/imagenes/')
                ? 'La imagen se eliminará de este servicio.'
                : 'El servicio se marcará como eliminado y dejará de estar disponible.';
        }
        bootstrap.Modal.getOrCreateInstance(confirmModal).show();
    });
});

document.querySelectorAll('[data-confirm-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!confirmModal || !confirmSubmit) {
            return;
        }

        event.preventDefault();
        pendingConfirmationForm = form;
        const title = document.querySelector('#ui-confirm-title');
        if (title) {
            title.textContent = '¿Confirmar acción?';
        }
        if (confirmMessage) {
            confirmMessage.textContent = form.dataset.confirmMessage || 'Confirma esta acción para continuar.';
        }
        confirmSubmit.textContent = form.dataset.confirmSubmit || 'Confirmar';
        bootstrap.Modal.getOrCreateInstance(confirmModal).show();
    });
});

confirmSubmit?.addEventListener('click', () => {
    if (!pendingConfirmationForm) {
        return;
    }

    const form = pendingConfirmationForm;
    pendingConfirmationForm = null;
    bootstrap.Modal.getOrCreateInstance(confirmModal).hide();
    form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.disabled = true;
    });
    form.submit();
});

document.querySelectorAll('[data-disable-on-submit]').forEach((form) => {
    form.addEventListener('submit', () => {
        form.querySelectorAll('button[type="submit"]').forEach((button) => {
            button.disabled = true;
        });
    });
});

document.querySelectorAll('[data-payment-form]').forEach((form) => {
    const button = form.querySelector('button[type="submit"]');

    const updatePaymentAvailability = () => {
        if (!button) {
            return;
        }

        button.disabled = !navigator.onLine;
        button.title = navigator.onLine ? '' : 'Necesitas conexión a Internet para pagar.';
    };

    updatePaymentAvailability();
    window.addEventListener('online', updatePaymentAvailability);
    window.addEventListener('offline', updatePaymentAvailability);

    form.addEventListener('submit', () => {
        if (!button) {
            return;
        }

        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Preparando pago...';
    });
});

document.querySelectorAll('[data-geolocation-form]').forEach((form) => {
    const button = form.querySelector('[data-geolocate]');
    const status = form.querySelector('[data-geolocation-status]');
    const latitude = form.querySelector('[data-latitude]');
    const longitude = form.querySelector('[data-longitude]');

    button?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            if (status) status.textContent = 'Tu navegador no permite ubicación. Escribe la dirección manualmente.';
            return;
        }
        if (status) status.textContent = 'Solicitando ubicación…';
        navigator.geolocation.getCurrentPosition((position) => {
            latitude.value = position.coords.latitude.toFixed(7);
            longitude.value = position.coords.longitude.toFixed(7);
            if (status) status.textContent = 'Ubicación obtenida. Puedes agregar una referencia manual.';
        }, () => {
            if (status) status.textContent = 'No pudimos obtener tu ubicación. Puedes escribirla manualmente.';
        }, { enableHighAccuracy: false, timeout: 8000, maximumAge: 120000 });
    });
});

const onDemandStatus = document.querySelector('[data-on-demand-status]');
if (onDemandStatus) {
    const pollUrl = onDemandStatus.dataset.pollUrl;
    const interval = Number(onDemandStatus.dataset.pollInterval || 4000);
    const message = onDemandStatus.querySelector('[data-search-message]');
    const radius = onDemandStatus.querySelector('[data-search-radius]');
    const radar = onDemandStatus.querySelector('[data-search-radar]');
    const poll = async () => {
        try {
            const response = await fetch(pollUrl, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) return;
            const data = await response.json();
            if (radius && data.search_radius_km) radius.textContent = `Radio actual: ${data.search_radius_km} km`;
            if (data.status !== 'searching') {
                if (message) message.textContent = data.status === 'matched' || data.status === 'awaiting_quote'
                    ? 'Encontramos un profesional. Revisa el detalle para cotizar.'
                    : 'La búsqueda terminó. Revisa el siguiente paso.';
                radar?.classList.remove('is-active');
                window.clearInterval(timer);
                window.setTimeout(() => window.location.reload(), 600);
            }
        } catch (_) {
            // Polling is best effort; the page remains usable if the network drops.
        }
    };
    const timer = window.setInterval(poll, interval);
}

const ratingLabels = { 1: 'Muy malo', 2: 'Malo', 3: 'Regular', 4: 'Muy bueno', 5: 'Excelente' };
document.querySelectorAll('.rating-input').forEach((group) => {
    const label = group.parentElement.querySelector('[data-rating-label]');
    group.querySelectorAll('input[name="rating"]').forEach((input) => {
        input.addEventListener('change', () => {
            if (label) {
                label.textContent = `${ratingLabels[input.value]} · ${input.value} de 5 estrellas`;
            }
        });
    });
});
