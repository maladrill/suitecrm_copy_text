(function () {
    'use strict';

    var messages = {
        pl: { copy: 'Kopiuj wartość pola', done: 'Skopiowano', failed: 'Nie udało się skopiować' },
        en: { copy: 'Copy field value', done: 'Copied', failed: 'Copy failed' }
    };

    function labels() {
        return /^pl\b/i.test(document.documentElement.lang || '') ? messages.pl : messages.en;
    }

    function announce(button, message) {
        var status = button.parentNode.querySelector('.sqs-copy-status');
        if (!status) {
            status = document.createElement('span');
            status.className = 'sqs-copy-status';
            status.setAttribute('role', 'status');
            status.setAttribute('aria-live', 'polite');
            button.parentNode.appendChild(status);
        }
        status.textContent = message;
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
        if (!Array.isArray(configured)) {
            return;
        }
        var enabled = new Set(configured);
        document.querySelectorAll('td[field]').forEach(function (cell) {
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
            button.setAttribute('aria-label', labels().copy);
            button.setAttribute('title', labels().copy);
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
            announce(button, labels().failed);
            return;
        }
        button.disabled = true;
        navigator.clipboard.writeText(value).then(function () {
            announce(button, labels().done);
        }, function () {
            announce(button, labels().failed);
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
