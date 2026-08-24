import assert from 'node:assert/strict';
import test from 'node:test';

import { submitConfirmedForm } from '../../resources/js/confirmed-form-submit.js';

test('preserves the clicked submit button name and value', () => {
    const appended = [];
    let submitted = false;
    const form = {
        appendChild: (element) => appended.push(element),
        submit: () => {
            submitted = true;
        },
    };
    const documentRef = {
        createElement: () => ({}),
    };

    submitConfirmedForm(form, { name: 'status', value: 'blocked' }, documentRef);

    assert.equal(submitted, true);
    assert.deepEqual(appended, [{ type: 'hidden', name: 'status', value: 'blocked' }]);
});

test('submits forms whose button does not carry a value', () => {
    let submitted = false;
    const form = {
        appendChild: () => assert.fail('no hidden input should be added'),
        submit: () => {
            submitted = true;
        },
    };

    submitConfirmedForm(form, null, { createElement: () => ({}) });

    assert.equal(submitted, true);
});
