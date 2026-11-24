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
        var text = labels().copy;
        document.querySelectorAll('.sqs-copy-button').forEach(function (button) {
            button.setAttribute('aria-label', text);
            button.setAttribute('title', text);
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
