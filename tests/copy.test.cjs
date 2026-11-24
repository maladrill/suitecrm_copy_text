const assert = require('node:assert/strict');
const test = require('node:test');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function setup(clipboard) {
    let listener;
    let status;
    const source = { innerText: '  Line one\nLine two  ' };
    const parent = {
        querySelector(selector) {
            if (selector === '.sqs-copy-source') return source;
            if (selector === '.sqs-copy-status') return status;
            return null;
        },
        appendChild(node) { status = node; }
    };
    const button = { parentNode: parent, disabled: false, setAttribute() {} };
    const document = {
        documentElement: { lang: 'pl-PL' },
        readyState: 'complete',
        addEventListener(name, fn) { if (name === 'click') listener = fn; },
        querySelectorAll() { return [button]; },
        createElement() {
            return { setAttribute() {}, textContent: '' };
        }
    };
    const script = fs.readFileSync(path.join(__dirname, '..', 'src/custom/include/SQS_CopyText/copy.js'), 'utf8');
    vm.runInNewContext(script, { document, navigator: { clipboard } });
    return { button, source, get status() { return status; }, click() {
        listener({ target: { closest() { return button; } } });
    } };
}

test('copies visible multiline value and reports success', async () => {
    let copied;
    const view = setup({ writeText(value) { copied = value; return Promise.resolve(); } });
    view.click();
    await new Promise(setImmediate);
    assert.equal(copied, 'Line one\nLine two');
    assert.equal(view.status.textContent, 'Skopiowano');
    assert.equal(view.button.disabled, false);
});

test('reports denied clipboard without copying hidden markup', async () => {
    const view = setup({ writeText() { return Promise.reject(new Error('denied')); } });
    view.click();
    await new Promise(setImmediate);
    assert.equal(view.status.textContent, 'Nie udało się skopiować');
    assert.equal(view.button.disabled, false);
});

test('does not call the clipboard for an empty visible field', () => {
    let called = false;
    const view = setup({ writeText() { called = true; return Promise.resolve(); } });
    view.source.innerText = '  ';
    view.click();
    assert.equal(called, false);
});
