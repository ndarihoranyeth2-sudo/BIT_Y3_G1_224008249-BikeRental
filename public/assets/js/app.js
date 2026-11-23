/* Online Bike Rental System -- client-side behaviour (vanilla JS).
   1. mobile nav toggle   2. confirm dialogs   3. client-side form validation
   4. live bike search through the JSON API (debounce + AbortController)
   Client-side checks are a convenience only; the server validates everything again. */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn); else fn();
    }

    ready(function () {
        initNav(); initConfirm(); initValidation(); initLiveSearch(); autoHideAlerts();
    });

    /* ---------- 1. Mobile nav ---------- */
    function initNav() {
        var btn = document.querySelector('.nav-toggle'), nav = document.getElementById('main-nav');
        if (!btn || !nav) return;
        btn.addEventListener('click', function () {
            var open = nav.classList.toggle('open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    /* ---------- 2. Confirm before destructive POST forms ---------- */
    function initConfirm() {
        document.addEventListener('submit', function (event) {
            var form = event.target.closest('form[data-confirm]');
            if (form && !window.confirm(form.getAttribute('data-confirm'))) event.preventDefault();
        });
    }

    function autoHideAlerts() {
        document.querySelectorAll('.alert-success').forEach(function (a) {
            setTimeout(function () { a.hidden = true; }, 6000);
        });
    }

    /* ---------- 3. Client-side validation (HTML5 constraint API + custom messages) ---------- */
    function messageFor(field) {
        var v = field.validity;
        if (v.valueMissing) return 'This field is required.';
        if (v.typeMismatch) return field.type === 'email' ? 'Enter a valid email address.' : 'Invalid value.';
        if (v.patternMismatch) return field.getAttribute('data-msg-pattern') || 'Invalid format.';
        if (v.tooShort) return 'Must be at least ' + field.minLength + ' characters.';
        if (v.rangeUnderflow) return 'Value is too small.';
        if (v.badInput) return 'Enter a valid value.';
        var match = field.getAttribute('data-match');
        if (match) {
            var other = field.form.elements[match];
            if (other && other.value !== field.value) return 'Values do not match.';
        }
        return '';
    }

    function showError(field, msg) {
        var holder = field.parentNode, err = holder.querySelector('.error-message');
        if (!msg) {
            field.classList.remove('input-error'); if (field.value) field.classList.add('input-success');
            if (err) err.remove();
            return;
        }
        field.classList.add('input-error'); field.classList.remove('input-success');
        if (!err) { err = document.createElement('span'); err.className = 'error-message'; holder.appendChild(err); }
        err.textContent = msg;     // textContent: never innerHTML
    }

    function checkField(field) {
        var msg = messageFor(field);
        if (!msg && field.getAttribute('data-match')) msg = '';
        showError(field, msg); return msg === '';
    }

    function initValidation() {
        document.querySelectorAll('form[data-validate]').forEach(function (form) {
            var fields = Array.prototype.filter.call(form.elements, function (f) {
                return f.name && f.type !== 'hidden' && f.type !== 'submit' && !f.disabled;
            });
            fields.forEach(function (f) {
                f.addEventListener('blur', function () { checkField(f); });
                f.addEventListener('input', function () { if (f.classList.contains('input-error')) checkField(f); });
            });
            form.addEventListener('submit', function (event) {
                var ok = true, first = null;
                fields.forEach(function (f) { if (!checkField(f)) { ok = false; first = first || f; } });
                if (!ok) { event.preventDefault(); first.focus(); }
            });
        });
    }

    /* ---------- 4. Live search with Fetch + JSON ---------- */
    function initLiveSearch() {
        var form = document.getElementById('bike-filter');
        if (!form) return;
        var api = form.getAttribute('data-api'),
            input = document.getElementById('search'),
            select = document.getElementById('status'),
            tbody = document.getElementById('bike-rows'),
            statusEl = document.getElementById('bike-status'),
            emptyEl = document.getElementById('bike-empty'),
            showUrl = tbody.getAttribute('data-show-url'),
            editUrl = tbody.getAttribute('data-edit-url');
        var timer = null, controller = null;

        function apiGet(url, signal) {
            return fetch(url, { headers: { Accept: 'application/json' }, signal: signal, credentials: 'same-origin' })
                .then(function (res) {
                    return res.json().catch(function () { throw new Error('Invalid server response.'); })
                        .then(function (body) {
                            if (!res.ok) throw new Error(body.message || 'Request failed (' + res.status + ').');
                            return body;
                        });
                });
        }

        function cell(text) { var td = document.createElement('td'); td.textContent = text; return td; }
        function link(href, text, cls) { var a = document.createElement('a'); a.href = href; a.textContent = text; if (cls) a.className = cls; return a; }

        function render(rows) {
            tbody.textContent = '';
            rows.forEach(function (b) {
                var tr = document.createElement('tr'), td = document.createElement('td');
                td.appendChild(link(showUrl + '&id=' + b.id, b.code)); tr.appendChild(td);
                tr.appendChild(cell(b.model));
                tr.appendChild(cell(Number(b.hourly_rate).toLocaleString('en-US', { maximumFractionDigits: 0 }) + ' RWF'));
                var st = document.createElement('td'), badge = document.createElement('span');
                badge.className = 'badge badge-' + b.status; badge.textContent = b.status.charAt(0).toUpperCase() + b.status.slice(1);
                st.appendChild(badge); tr.appendChild(st);
                var act = document.createElement('td'); act.className = 'actions';
                act.appendChild(link(showUrl + '&id=' + b.id, 'View', 'btn btn-small'));
                act.appendChild(link(editUrl + '&id=' + b.id, 'Edit', 'btn btn-small'));
                tr.appendChild(act); tbody.appendChild(tr);
            });
            emptyEl.hidden = rows.length > 0;
        }

        function search() {
            if (controller) controller.abort();         // cancel the older request
            controller = new AbortController();
            var q = 'search=' + encodeURIComponent(input.value.trim()) + '&status=' + encodeURIComponent(select.value);
            statusEl.textContent = 'Searching...';
            form.classList.add('loading');
            apiGet(api + '&' + q, controller.signal).then(function (body) {
                render(body.data); statusEl.textContent = body.meta.count + ' bike(s) found';
            }).catch(function (err) {
                if (err.name === 'AbortError') return;     // superseded by a newer request
                statusEl.textContent = 'Error: ' + err.message;
            }).then(function () { form.classList.remove('loading'); });
        }

        input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(search, 300); });   // debounce
        select.addEventListener('change', search);
        form.addEventListener('submit', function (e) { e.preventDefault(); search(); });
    }
})();
