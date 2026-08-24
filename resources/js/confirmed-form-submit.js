export function submitConfirmedForm(form, submitter, documentRef = document) {
    if (submitter?.name) {
        const submitterValue = documentRef.createElement('input');
        submitterValue.type = 'hidden';
        submitterValue.name = submitter.name;
        submitterValue.value = submitter.value;
        form.appendChild(submitterValue);
    }

    form.submit();
}
