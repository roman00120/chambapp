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

    if (form.hasAttribute('data-disable-on-submit')) {
        form.querySelectorAll('button[type="submit"]').forEach((btn) => {
            btn.disabled = true;
        });
    }

    HTMLFormElement.prototype.submit.call(form);
}
