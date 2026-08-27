export function submitConfirmedForm(form, submitter, documentRef = document) {
    if (!form) {
        return;
    }

    if (submitter?.name) {
        const submitterValue = documentRef.createElement('input');
        submitterValue.type = 'hidden';
        submitterValue.name = submitter.name;
        submitterValue.value = submitter.value;
        form.appendChild(submitterValue);
    }

    if (typeof form.hasAttribute === 'function' && form.hasAttribute('data-disable-on-submit')) {
        form.querySelectorAll?.('button[type="submit"]')?.forEach?.((btn) => {
            btn.disabled = true;
        });
    }

    if (typeof form.submit === 'function') {
        form.submit();
    } else {
        HTMLFormElement.prototype.submit.call(form);
    }
}
