const assert = require('node:assert/strict');
const test = require('node:test');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function setup(clipboard, options = {}) {
    let listener;
    let status;
    let button;
    const source = {
        innerText: options.value === undefined ? '  Line one\nLine two  ' : options.value,
        classList: { add(name) { source.className = name; } }
    };
    const parent = {
        querySelector(selector) {
            if (selector === '.sqs-copy-source') return source.className ? source : null;
            if (selector === '.sqs-copy-status') return status;
            return null;
        },
        insertBefore(node) { button = node; node.parentNode = parent; },
        appendChild(node) { status = node; }
    };
    source.parentNode = parent;
    const cells = (options.fields || ['description']).map(field => ({
        getAttribute() { return field; },
        querySelector(selector) { return selector === '.sugar_field' ? source : null; }
    }));
    const document = {
        documentElement: { lang: 'pl-PL' },
        readyState: 'complete',
        addEventListener(name, fn) { if (name === 'click') listener = fn; },
        getElementById(id) {
            return id === 'sqs-copy-config' && options.config !== null
                ? { textContent: options.config === undefined ? '["description"]' : options.config }
                : null;
        },
        querySelectorAll(selector) {
            const fieldCell = options.layout === 'div' ? '.detail-view-field[field]' : 'td[field]';
            return selector.split(',').map(value => value.trim()).includes(fieldCell) ? cells : [];
        },
        createElement() { return { setAttribute() {}, textContent: '' }; }
    };
    const script = fs.readFileSync(path.join(__dirname, '..', 'src/custom/include/SQS_CopyText/copy.js'), 'utf8');
    vm.runInNewContext(script, { document, navigator: { clipboard } });
    return { get button() { return button; }, source, get status() { return status; }, click() {
        if (button) listener({ target: { closest() { return button; } } });
    } };
}

test('copies visible multiline value and reports success', async () => {
    let copied;
    const view = setup({ writeText(value) { copied = value; return Promise.resolve(); } });
    assert.ok(view.button);
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

test('does not add a button for an empty visible field', () => {
    const view = setup({ writeText() { throw new Error('unexpected'); } }, { value: '  ' });
    assert.equal(view.button, undefined);
});

test('does not add a button for a field without the Studio option', () => {
    const view = setup({ writeText() { throw new Error('unexpected'); } }, { fields: ['name'] });
    assert.equal(view.button, undefined);
});

test('adds a button in the SuiteP div-based Detail View layout', async () => {
    let copied;
    const view = setup(
        { writeText(value) { copied = value; return Promise.resolve(); } },
        { layout: 'div', fields: ['part_number'], config: '["part_number"]', value: 'ID78859' }
    );
    assert.ok(view.button);
    view.click();
    await new Promise(setImmediate);
    assert.equal(copied, 'ID78859');
});

test('ignores missing and malformed configuration', () => {
    assert.equal(setup({}, { config: null }).button, undefined);
    assert.equal(setup({}, { config: '{invalid' }).button, undefined);
});
