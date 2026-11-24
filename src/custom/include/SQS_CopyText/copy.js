(function () {
    'use strict';

    var labels = { copy: 'Copy field value', done: 'Copied', failed: 'Copy failed' };
    var status;
    var statusTimer;

    function announce(message, failed) {
        if (!status) {
            status = document.createElement('div');
            status.setAttribute('role', 'status');
            status.setAttribute('aria-live', 'polite');
            document.body.appendChild(status);
        }
        status.className = failed ? 'sqs-copy-notice is-error' : 'sqs-copy-notice';
        status.textContent = message;
        status.hidden = false;
        if (statusTimer) {
            clearTimeout(statusTimer);
        }
        statusTimer = setTimeout(function () {
            status.hidden = true;
        }, 3000);
    }

    function initialise() {
        var config = document.getElementById('sqs-copy-config');
        if (!config) {
            return;
        }
        var configured;
        try {
            configured = JSON.parse(config.textContent);
        } catch (error) {
            return;
        }
        if (!configured || !Array.isArray(configured.fields)) {
            return;
        }
        if (configured.labels) {
            ['copy', 'done', 'failed'].forEach(function (key) {
                if (typeof configured.labels[key] === 'string' && configured.labels[key].length <= 200) {
                    labels[key] = configured.labels[key];
                }
            });
        }
        var enabled = new Set(configured.fields);
        document.querySelectorAll('td[field], .detail-view-field[field]').forEach(function (cell) {
            if (!enabled.has(cell.getAttribute('field'))) {
                return;
            }
            var source = cell.querySelector('.sugar_field');
            if (!source || !(source.innerText || '').trim()) {
                return;
            }
            source.classList.add('sqs-copy-source');
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'sqs-copy-button';
            button.setAttribute('aria-label', labels.copy);
            button.setAttribute('title', labels.copy);
            button.innerHTML = '<svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="3" width="13" height="15" rx="2"></rect><path d="M16 18v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h3"></path></svg>';
            source.parentNode.insertBefore(button, source.nextSibling);
        });
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('.sqs-copy-button');
        if (!button || button.disabled) {
            return;
        }
        var source = button.parentNode.querySelector('.sqs-copy-source');
        if (!source) {
            return;
        }
        var value = (source.innerText === undefined ? source.textContent : source.innerText).trim();
        if (!value) {
            return;
        }
        if (!navigator.clipboard || !navigator.clipboard.writeText) {
            announce(labels.failed, true);
            return;
        }
        button.disabled = true;
        navigator.clipboard.writeText(value).then(function () {
            announce(labels.done, false);
        }, function () {
            announce(labels.failed, true);
        }).then(function () {
            button.disabled = false;
        });
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise);
    } else {
        initialise();
    }
}());
