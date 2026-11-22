// FILE: assets/js/mock-ui.js - all page behaviour for the research mock (vanilla JavaScript, no libraries).
// Parts A and B are reused from the real project's Frontend/js/app.js, with names changed to the mock's
// neutral vocabulary and every server call removed. Part C rewrites the jQuery/GSAP/AJAX pieces in plain JS.
// Form checks live in validator.js (loaded first). Copyright (c) 2026 Ar Kar Phyo. All rights reserved.

/* #################### PART A - UI kit #################### */
(function () {
    'use strict';

    var html = document.documentElement;
    function $(sel, root) { return (root || document).querySelector(sel); }
    function $all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
    // Escape text before putting it into innerHTML.
    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    /* ================= 1. TOASTS ================= */
    var toastRegion = $('#toast-region');
    var ICONS = { success: '&#10004;&#xFE0E;', error: '&#9888;&#xFE0E;', info: '&#8505;&#xFE0E;', warning: '&#9888;&#xFE0E;' };

    // Show a toast message (success / error / info) that hides itself.
    function toast(message, type, opts) {
        if (!toastRegion) return;
        type = type || 'info';
        opts = opts || {};
        var el = document.createElement('div');
        el.className = 'toast toast-' + type;
        el.setAttribute('role', type === 'error' ? 'alert' : 'status');
        el.innerHTML = '<span class="toast-icon" aria-hidden="true">' + (ICONS[type] || ICONS.info) + '</span>' +
            '<span class="toast-msg">' + escapeHtml(message) + '</span>' +
            (opts.action ? '<button type="button" class="toast-action">' + escapeHtml(opts.action.label) + '</button>' : '') +
            '<button type="button" class="toast-close" aria-label="Dismiss">&times;</button>';
        toastRegion.appendChild(el);
        requestAnimationFrame(function () { el.classList.add('is-in'); });

        // Fade the toast out, then remove it.
        function close() {
            el.classList.remove('is-in');
            setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 250);
        }
        el.querySelector('.toast-close').addEventListener('click', close);
        if (opts.action) {
            el.querySelector('.toast-action').addEventListener('click', function () { opts.action.onClick(); close(); });
        }
        var timer = setTimeout(close, opts.duration || (type === 'error' ? 7000 : 4500));
        el.addEventListener('mouseenter', function () { clearTimeout(timer); });
        el.addEventListener('mouseleave', function () { timer = setTimeout(close, 2500); });
        return el;
    }
    window.mockToast = toast;

    // Flash messages handed over by PHP (Flash::set in app/core.php, JSON block in app/layout.php)
    try {
        var flash = JSON.parse(($('#flash-data') || {}).textContent || '[]');
        flash.forEach(function (f, i) { setTimeout(function () { toast(f.message, f.type); }, 250 + i * 200); });
    } catch (e) {}

    /* ================= 2. MODAL + FOCUS TRAP ================= */
    var FOCUSABLE = 'a[href], button:not([disabled]), textarea, input:not([type="hidden"]):not([disabled]), select, [tabindex]:not([tabindex="-1"])';
    var openStack = [];

    // Open a dialog: show it, remember the focused element, trap Tab inside.
    function openModal(backdrop) {
        var returnTo = document.activeElement;
        backdrop.hidden = false;
        html.classList.add('modal-open');
        requestAnimationFrame(function () { backdrop.classList.add('is-open'); });
        var first = $all(FOCUSABLE, backdrop)[0];
        if (first) first.focus();

        // Esc closes the dialog; Tab and Shift+Tab cycle through its buttons only.
        function onKey(e) {
            if (e.key === 'Escape') { e.preventDefault(); closeModal(backdrop); }
            if (e.key !== 'Tab') return;
            var items = $all(FOCUSABLE, backdrop).filter(function (n) { return n.offsetParent !== null; });
            if (!items.length) return;
            var a = items[0], z = items[items.length - 1];
            if (e.shiftKey && document.activeElement === a) { e.preventDefault(); z.focus(); }
            else if (!e.shiftKey && document.activeElement === z) { e.preventDefault(); a.focus(); }
        }
        // A click on the dark backdrop or a [data-close-modal] button closes it.
        function onClick(e) { if (e.target === backdrop || e.target.closest('[data-close-modal]')) closeModal(backdrop); }
        backdrop.addEventListener('keydown', onKey);
        backdrop.addEventListener('click', onClick);
        openStack.push({ el: backdrop, returnTo: returnTo, onKey: onKey, onClick: onClick });
    }

    // Close a dialog and give focus back to the element that opened it.
    function closeModal(backdrop) {
        var idx = -1;
        openStack.forEach(function (m, i) { if (m.el === backdrop) idx = i; });
        if (idx < 0) return;
        var m = openStack.splice(idx, 1)[0];
        backdrop.removeEventListener('keydown', m.onKey);
        backdrop.removeEventListener('click', m.onClick);
        backdrop.classList.remove('is-open');
        setTimeout(function () { backdrop.hidden = true; }, 180);
        if (!openStack.length) html.classList.remove('modal-open');
        if (m.returnTo && m.returnTo.focus) m.returnTo.focus();      // focus restore
        if (backdrop.dataset.temp) setTimeout(function () { backdrop.remove(); }, 200);
    }
    window.mockModal = { open: openModal, close: closeModal };

    /* ================= 3. CONFIRM / REASON DIALOGS ================= */
    function confirmDialog(opts) {
        return new Promise(function (resolve) {
            var b = document.createElement('div');
            b.className = 'modal-backdrop';
            b.dataset.temp = '1';
            b.hidden = true;
            var rid = 'reason-' + Date.now();
            b.innerHTML =
                '<div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="' + rid + '-t">' +
                '<h2 id="' + rid + '-t" class="text-lg font-bold text-white mb-2">' + escapeHtml(opts.title || 'Please confirm') + '</h2>' +
                '<p class="text-sm text-gray-300 mb-4">' + escapeHtml(opts.message) + '</p>' +
                (opts.reason ?
                    '<label for="' + rid + '" class="block text-sm font-medium text-gray-300 mb-1">' +
                    escapeHtml(opts.reasonLabel || 'Reason *') + '</label>' +
                    '<textarea id="' + rid + '" class="form-input" rows="4" maxlength="500" placeholder="' +
                    escapeHtml(opts.placeholder || 'Write at least 10 characters.') + '"></textarea>' +
                    '<p class="field-error" hidden>Please write at least 10 characters.</p>' : '') +
                '<div class="flex justify-end gap-3 mt-5">' +
                '<button type="button" class="btn-outline" data-cancel>Cancel</button>' +
                '<button type="button" class="form-button ' + (opts.tone === 'danger' ? 'is-danger' : '') + '" data-ok>' +
                escapeHtml(opts.okLabel || 'Confirm') + '</button></div></div>';
            document.body.appendChild(b);
            openModal(b);
            var ta = b.querySelector('textarea');
            if (ta) ta.focus();
            var done = false;
            // Close the confirm dialog once and return the answer to the waiting Promise.
            function finish(val) { if (done) return; done = true; closeModal(b); resolve(val); }
            b.querySelector('[data-cancel]').addEventListener('click', function () { finish(null); });
            b.querySelector('[data-ok]').addEventListener('click', function () {
                if (ta) {
                    var v = ta.value.trim();
                    var err = b.querySelector('.field-error');
                    if (v.length < 10) { err.hidden = false; ta.classList.add('is-invalid'); ta.focus(); return; }
                    finish(v);
                } else finish(true);
            });
            // Escape / backdrop close resolves as "cancel"
            var obs = new MutationObserver(function () { if (b.hidden) { obs.disconnect(); finish(null); } });
            obs.observe(b, { attributes: true, attributeFilter: ['hidden'] });
        });
    }
    window.mockConfirm = confirmDialog;

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches || !form.matches('form[data-confirm]') || form.dataset.confirmed) return;
        e.preventDefault();
        // Check the fields first, so the dialog never confirms a form that would be rejected.
        if (form.hasAttribute('data-validate') && window.MockValidator && !window.MockValidator.validateForm(form)) return;   // validator.js shows the toast
        confirmDialog({
            title: form.dataset.confirmTitle,
            message: form.dataset.confirm,
            okLabel: form.dataset.confirmLabel,
            tone: form.dataset.confirmTone,
            reason: !!form.dataset.confirmReason,
            reasonLabel: form.dataset.confirmReasonLabel,
            placeholder: form.dataset.confirmPlaceholder
        }).then(function (result) {
            if (result === null) return;
            if (form.dataset.confirmReason) {
                var input = form.querySelector('[name="' + form.dataset.confirmReason + '"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = form.dataset.confirmReason;
                    form.appendChild(input);
                }
                input.value = result;
            }
            form.dataset.confirmed = '1';
            HTMLFormElement.prototype.submit.call(form);
        });
    }, true);

    /* ================= 4. DISPLAY SETTINGS ================= */
    var PREF_KEY = 'demo-prefs';
    // Read the saved display settings from localStorage.
    function readPrefs() { try { return JSON.parse(localStorage.getItem(PREF_KEY) || '{}'); } catch (e) { return {}; } }
    // Apply the settings as classes on <html>: no-video, reduce-motion, text-large / text-xlarge,
    // high-contrast, and text-spaced, font-readable, links-underline, focus-strong, plus the
    // reading guide.
    function applyPrefs(p) {
        html.classList.toggle('no-video', p.video === false);
        html.classList.toggle('reduce-motion', p.motion === false);
        html.classList.toggle('text-large', p.text === 'large');
        html.classList.toggle('text-xlarge', p.text === 'xlarge');
        html.classList.toggle('high-contrast', p.contrast === true);
        html.classList.toggle('text-spaced', p.spacing === true);
        html.classList.toggle('font-readable', p.font === true);
        html.classList.toggle('links-underline', p.links === true);
        html.classList.toggle('focus-strong', p.focus === true);
        readingGuide(p.guide === true);
        var v = $('#video-background');
        if (v) { if (p.video === false) { v.pause(); } else if (v.paused) { var pr = v.play(); if (pr && pr.catch) pr.catch(function () {}); } }
    }
    // Reading guide: a soft horizontal bar that follows the pointer.
    var guideEl = null, guideMove = null;
    function readingGuide(on) {
        guideEl = guideEl || $('.reading-guide');
        if (!guideEl) return;
        guideEl.hidden = !on;
        if (on && !guideMove) {
            guideMove = function (e) { guideEl.style.transform = 'translateY(' + (e.clientY - 22) + 'px)'; };
            document.addEventListener('pointermove', guideMove, { passive: true });
        } else if (!on && guideMove) {
            document.removeEventListener('pointermove', guideMove);
            guideMove = null;
        }
    }
    // Switches that are ON unless the user turned them off.
    var DEFAULT_ON = ['video', 'motion', 'autoplay'];
    var settings = $('#settings-dialog');
    if (settings) {
        // Set the switches in the settings dialog to the saved values.
        var syncInputs = function () {
            var p = readPrefs();
            $all('[data-pref]', settings).forEach(function (input) {
                var key = input.dataset.pref;
                if (input.type === 'radio') input.checked = (p[key] || 'normal') === input.value;
                else if (DEFAULT_ON.indexOf(key) >= 0) input.checked = p[key] !== false;
                else input.checked = p[key] === true;
            });
        };
        $all('[data-pref]', settings).forEach(function (input) {
            input.addEventListener('change', function () {
                var p = readPrefs(), key = input.dataset.pref;
                p[key] = input.type === 'radio' ? input.value : input.checked;
                try { localStorage.setItem(PREF_KEY, JSON.stringify(p)); } catch (e) {}
                applyPrefs(p);
                toast('Display setting saved.', 'success', { duration: 1800 });
            });
        });
        var reset = $('[data-reset-prefs]', settings);
        if (reset) reset.addEventListener('click', function () {
            try { localStorage.removeItem(PREF_KEY); } catch (e) {}
            applyPrefs({}); syncInputs(); toast('Display settings reset.', 'info');
        });
        $all('[data-open-settings]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.body.classList.remove('menu-open');
                syncInputs();
                // BOM read-out: window, screen and navigator objects (INFO 651 Wk 6)
                var env = $('#env-info');
                if (env) {
                    env.textContent = 'Viewport ' + window.innerWidth + '×' + window.innerHeight +
                        ' · Screen ' + screen.width + '×' + screen.height +
                        ' · ' + (navigator.onLine ? 'Online' : 'Offline') +
                        ' · ' + (navigator.language || 'en');
                }
                openModal(settings);
            });
        });
    }
    applyPrefs(readPrefs());

    /* ================= 5. BACK TO TOP (after 400px) ================= */
    var topBtn = $('.back-to-top');
    if (topBtn) {
        // Show the back-to-top button after 400px of scrolling.
        var bttFill = topBtn.querySelector('.btt-fill'), ticking = false;
        var onScroll = function () {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(function () {            // at most one update per frame (smooth on phones)
                var max = document.documentElement.scrollHeight - window.innerHeight;
                topBtn.classList.toggle('is-visible', window.scrollY > 400);
                if (bttFill) bttFill.style.strokeDashoffset = String(100 - (max > 0 ? Math.min(100, window.scrollY / max * 100) : 0));
                ticking = false;
            });
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
        topBtn.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: html.classList.contains('reduce-motion') ? 'auto' : 'smooth' });
            var main = $('#main-content'); if (main) main.focus({ preventScroll: true });
        });
    }

    /* ================= 6. ONLINE / OFFLINE ================= */
    var offline = $('#offline-banner');
    // Show or hide the offline banner when the connection changes.
    function netChange() {
        if (!offline) return;
        offline.hidden = navigator.onLine;
        if (navigator.onLine && offline.dataset.wasOff) toast('Connection restored.', 'success');
        if (!navigator.onLine) offline.dataset.wasOff = '1';
    }
    window.addEventListener('online', netChange);
    window.addEventListener('offline', netChange);

    /* ================= 7. SHARE / PRINT / BACK ================= */
    // Share sheet: copy box + share links. Works on plain HTTP, where navigator.share/clipboard do not exist.
    function copyText(input) {
        input.focus(); input.select(); input.setSelectionRange(0, input.value.length);
        var ok = false;
        try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
        if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(input.value).catch(function () {}); ok = true; }
        toast(ok ? 'Link copied.' : 'Press and hold the selected link to copy it.', ok ? 'success' : 'info');
    }
    function shareSheet(url, title) {
        var u = encodeURIComponent(url), t = encodeURIComponent(title), tu = encodeURIComponent(title + ' ' + url);
        var links = [['WhatsApp', 'https://wa.me/?text=' + tu], ['Telegram', 'https://t.me/share/url?url=' + u + '&text=' + t],
            ['Email', 'mailto:?subject=' + t + '&body=' + u], ['Gmail', 'https://mail.google.com/mail/?view=cm&fs=1&su=' + t + '&body=' + u],
            ['Facebook', 'https://www.facebook.com/sharer/sharer.php?u=' + u], ['X', 'https://x.com/intent/post?url=' + u + '&text=' + t],
            ['LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url=' + u]];
        var b = document.createElement('div');
        b.className = 'modal-backdrop'; b.dataset.temp = '1'; b.hidden = true;
        b.innerHTML = '<div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="share-t">' +
            '<h2 id="share-t" class="text-lg font-bold text-white mb-1">Share</h2>' +
            '<p class="text-sm text-gray-400 mb-3">' + escapeHtml(title) + '</p>' +
            '<div class="flex gap-2"><label for="share-url" class="sr-only">Link</label>' +
            '<input id="share-url" class="form-input flex-1" readonly value="' + escapeHtml(url) + '">' +
            '<button type="button" class="form-button" style="width:auto" data-copy>Copy link</button></div>' +
            '<div class="share-grid">' + links.map(function (l) {
                return '<a class="btn-outline" target="_blank" rel="noopener" data-no-transition href="' + escapeHtml(l[1]) + '">' + l[0] + '</a>';
            }).join('') + '</div>' +
            '<div class="flex justify-end mt-4"><button type="button" class="btn-outline" data-close-modal>Close</button></div></div>';
        document.body.appendChild(b);
        openModal(b);
        b.querySelector('[data-copy]').addEventListener('click', function () { copyText(b.querySelector('#share-url')); });
    }

    document.addEventListener('click', function (e) {
        var share = e.target.closest('[data-share]');
        if (share) {
            e.preventDefault();
            var url = share.dataset.share || window.location.href;          // location object
            var title = share.dataset.title || document.title;
            // The phone's own share sheet needs HTTPS; on a plain-HTTP address our own sheet is used instead.
            if (navigator.share) navigator.share({ title: title, url: url }).catch(function (err) { if (err.name !== 'AbortError') shareSheet(url, title); });
            else shareSheet(url, title);
        }
        var print = e.target.closest('[data-print]');
        if (print) { e.preventDefault(); window.print(); }
        // go back in history only when the previous page was another page of this site (not this same page
        // after a form redirect, not the login screen); otherwise follow the link to the logical parent.
        var back = e.target.closest('[data-back]');
        if (back && window.history.length > 1 && document.referrer) {
            try {
                var ref = new URL(document.referrer);
                if (ref.host === location.host && ref.pathname !== location.pathname
                    && !/(login|signup|logout)/.test(ref.pathname + ref.search)) {
                    e.preventDefault(); window.history.back();             // history object
                }
            } catch (err) { /* malformed referrer: use the link */ }
        }
    });

    /* ================= 8. "/" FOCUSES SEARCH ================= */
    document.addEventListener('keydown', function (e) {
        if (e.key !== '/' || e.ctrlKey || e.metaKey) return;
        var tag = (document.activeElement && document.activeElement.tagName) || '';
        if (/INPUT|TEXTAREA|SELECT/.test(tag) || document.activeElement.isContentEditable) return;
        var input = window.innerWidth >= 1024 ? $('#global-search') : ($('#archive-q') || $('#mobile-search'));
        if (input) { e.preventDefault(); if (input.id === 'mobile-search') document.body.classList.add('menu-open'); input.focus(); }
    });

    /* ================= 9. DRAFT AUTOSAVE ================= */
    $all('form[data-draft]').forEach(function (form) {
        var key = 'demo-draft-' + form.dataset.draft;
        var fields = $all('input, textarea, select', form).filter(function (f) {
            return f.name && !/^(csrf_token|password|confirm_password|current_password|new_password)$/.test(f.name) &&
                   ['hidden', 'file', 'submit', 'button'].indexOf(f.type) === -1;
        });
        var status = $('[data-draft-status]', form);
        // Save the form fields to localStorage (draft autosave).
        function save() {
            var data = { _t: Date.now() };
            fields.forEach(function (f) {
                if (f.type === 'checkbox') { data[f.name] = data[f.name] || []; if (f.checked) data[f.name].push(f.value); }
                else if (f.type === 'radio') { if (f.checked) data[f.name] = f.value; }
                else data[f.name] = f.value;
            });
            try { localStorage.setItem(key, JSON.stringify(data)); } catch (e) {}
            if (status) status.textContent = 'Draft saved ' + new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
        var t;
        form.addEventListener('input', function () { clearTimeout(t); t = setTimeout(save, 600); });
        form.addEventListener('change', function () { clearTimeout(t); t = setTimeout(save, 300); });
        form.addEventListener('submit', function (ev) {
            // runs after every other submit handler — keep the draft if validation blocked the submit
            setTimeout(function () { if (!ev.defaultPrevented) { try { localStorage.removeItem(key); } catch (e) {} } }, 0);
        });
        if (form.dataset.draftClear) { try { localStorage.removeItem(key); } catch (e) {} return; }

        var saved; try { saved = JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) { saved = null; }
        if (!saved) return;
        var empty = fields.every(function (f) { return f.type === 'checkbox' || f.type === 'radio' || f.tagName === 'SELECT' || f.value === '' || f.value === f.defaultValue; });
        if (!empty) return;  // the server already re-filled the form (e.g. after a validation error)
        // Put a saved draft back into the form.
        function restore() {
            fields.forEach(function (f) {
                if (!(f.name in saved)) return;
                if (f.type === 'checkbox') f.checked = (saved[f.name] || []).indexOf(f.value) !== -1;
                else if (f.type === 'radio') f.checked = saved[f.name] === f.value;
                else f.value = saved[f.name];
                f.dispatchEvent(new Event('input', { bubbles: true }));
            });
            toast('Draft restored.', 'success', { duration: 2500 });
        }
        var mins = Math.max(1, Math.round((Date.now() - saved._t) / 60000));
        toast('You have an unsaved draft from ' + (mins < 60 ? mins + ' min' : Math.round(mins / 60) + ' h') + ' ago.', 'info', {
            duration: 12000, action: { label: 'Restore', onClick: restore }
        });
    });

    /* ================= 10. MULTI-STEP WIZARD ================= */
    $all('[data-wizard]').forEach(function (wiz) {
        var form = wiz.closest('form');
        var steps = $all('.wizard-step', wiz);
        var dots = $all('.stepper-item', wiz);
        var current = 0;
        if (!steps.length) return;
        wiz.classList.add('is-enhanced');

        // Copy the typed answers into the wizard's review step.
        function fillReview() {
            $all('[data-review-for]', wiz).forEach(function (out) {
                var name = out.dataset.reviewFor;
                var els = $all('[name="' + name + '"]', form);
                var val = '';
                if (els.length && els[0].type === 'checkbox') {
                    val = els.filter(function (c) { return c.checked; })
                             .map(function (c) { return c.dataset.text || c.value; }).join(', ');
                } else if (els.length && els[0].tagName === 'SELECT') {
                    val = els[0].value ? els[0].options[els[0].selectedIndex].text : '';
                } else if (els.length) { val = els[0].value.trim(); }
                if (out.dataset.reviewTrim && val.length > +out.dataset.reviewTrim) val = val.slice(0, +out.dataset.reviewTrim) + '…';
                out.textContent = val || '—';
                out.classList.toggle('is-empty', !val);
            });
        }
        // Show wizard step i and update the step dots.
        function go(i, focus) {
            current = Math.max(0, Math.min(i, steps.length - 1));
            steps.forEach(function (s, n) { s.classList.toggle('is-active', n === current); s.hidden = n !== current; });
            dots.forEach(function (d, n) {
                d.classList.toggle('is-active', n === current);
                d.classList.toggle('is-done', n < current);
                if (n === current) d.setAttribute('aria-current', 'step'); else d.removeAttribute('aria-current');
            });
            if (steps[current].hasAttribute('data-review')) fillReview();
            if (focus !== false) {
                var h = $('h2, h3', steps[current]) || steps[current];
                h.setAttribute('tabindex', '-1'); h.focus({ preventScroll: true });
                wiz.scrollIntoView({ block: 'start', behavior: 'smooth' });
            }
        }
        // Validate only the fields of the current wizard step.
        function stepValid() {
            return !window.MockValidator || window.MockValidator.validateForm(form, steps[current]);
        }
        wiz.addEventListener('click', function (e) {
            var next = e.target.closest('[data-next]'), prev = e.target.closest('[data-prev]'),
                jump = e.target.closest('[data-goto-step]');
            if (next) { e.preventDefault(); if (stepValid()) go(current + 1); }
            if (prev) { e.preventDefault(); go(current - 1); }
            if (jump) {
                e.preventDefault();
                var target = +jump.dataset.gotoStep;
                if (target < current || stepValid()) go(target);
            }
        });
        // If the server rejected the submission, open the step with the first error
        var bad = $('.is-invalid, [data-server-error]', wiz);
        var start = 0;
        if (bad) steps.forEach(function (s, n) { if (s.contains(bad)) start = n; });
        go(start, false);
    });

    /* ================= 11. ACCORDION + FAQ FILTER ================= */
    $all('.accordion-trigger').forEach(function (btn) {
        var panel = document.getElementById(btn.getAttribute('aria-controls'));
        // panels are visible without JS (progressive enhancement); collapse them now
        if (panel && btn.getAttribute('aria-expanded') !== 'true') panel.hidden = true;
        btn.addEventListener('click', function () {
            var open = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', String(!open));
            if (panel) panel.hidden = open;
        });
        btn.addEventListener('keydown', function (e) {        // arrow-key navigation between questions
            if (['ArrowDown', 'ArrowUp', 'Home', 'End'].indexOf(e.key) === -1) return;
            var all = $all('.accordion-trigger').filter(function (b) { return b.offsetParent !== null; });
            var i = all.indexOf(btn);
            var n = e.key === 'ArrowDown' ? i + 1 : e.key === 'ArrowUp' ? i - 1 : e.key === 'Home' ? 0 : all.length - 1;
            if (all[(n + all.length) % all.length]) { e.preventDefault(); all[(n + all.length) % all.length].focus(); }
        });
    });
    // Deep link: faq#q-review opens that answer
    if (location.hash) {
        var target = document.getElementById(location.hash.slice(1));
        var trig = target && target.querySelector('.accordion-trigger');
        if (trig && trig.getAttribute('aria-expanded') !== 'true') trig.click();
    }
    // Email deep links like article?slug=x#comment-19 must land on the message.
    (function () {
        var id = location.hash.slice(1);
        if (!/^comment-\d+$/.test(id)) return;
        var tries = 0;
        (function jump() {
            var t = document.getElementById(id);
            if (t) {
                var r = t.getBoundingClientRect();
                if (r.top >= 0 && r.bottom <= window.innerHeight) return;   // already visible
                try { t.scrollIntoView({ block: 'center' }); } catch (e) { t.scrollIntoView(); }
                return;
            }
            if (++tries < 8) setTimeout(jump, 350);
        })();
    })();
    $all('[data-expand-all]').forEach(function (b) {
        b.addEventListener('click', function () {
            var expand = b.dataset.state !== 'open';
            $all('.accordion-trigger').forEach(function (t) {
                if ((t.getAttribute('aria-expanded') === 'true') !== expand) t.click();
            });
            b.dataset.state = expand ? 'open' : 'closed';
            b.textContent = expand ? 'Collapse all' : 'Expand all';
        });
    });
    var faqFilter = $('[data-faq-filter]');
    if (faqFilter) {
        var count = $('[data-faq-count]');
        faqFilter.addEventListener('input', function () {
            var q = faqFilter.value.trim().toLowerCase(), shown = 0;
            $all('.accordion-item').forEach(function (item) {
                var hit = !q || item.textContent.toLowerCase().indexOf(q) !== -1;
                item.hidden = !hit; if (hit) shown++;
            });
            $all('.faq-group').forEach(function (g) { g.hidden = !$all('.accordion-item', g).some(function (i) { return !i.hidden; }); });
            if (count) count.textContent = q ? shown + ' matching answer' + (shown === 1 ? '' : 's') : '';
        });
    }

    /* ================= 12. COUNT-UP STATS ================= */
    var counters = $all('[data-countup]');
    if (counters.length) {
        // Count a statistic up from 0 to its value (skipped when motion is reduced).
        var run = function (el) {
            var end = parseInt(el.dataset.countup, 10) || 0;
            if (html.classList.contains('reduce-motion') || end === 0) { el.textContent = end.toLocaleString(); return; }
            var t0 = null, dur = 1200;
            (function step(ts) {
                if (!t0) t0 = ts;
                var p = Math.min((ts - t0) / dur, 1);
                el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3))).toLocaleString();
                if (p < 1) requestAnimationFrame(step);
            })(performance.now());
        };
        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) { if (en.isIntersecting) { run(en.target); io.unobserve(en.target); } });
            }, { threshold: 0.4 });
            counters.forEach(function (c) { c.textContent = '0'; io.observe(c); });
        } else counters.forEach(run);
    }

    /* ================= 13. READING PROGRESS ================= */
    var article = $('[data-read-progress]');
    if (article) {
        var bar = document.createElement('div');
        bar.className = 'read-progress';
        bar.setAttribute('role', 'progressbar');
        bar.setAttribute('aria-label', 'Reading progress');
        bar.setAttribute('aria-valuemin', '0');
        bar.setAttribute('aria-valuemax', '100');
        bar.innerHTML = '<span></span>';
        document.body.appendChild(bar);
        var readFill = bar.firstChild;
        // Update the reading progress bar from the scroll position.
        var upd = function () {
            var r = article.getBoundingClientRect();
            var total = article.offsetHeight - window.innerHeight;
            var pct = total <= 0 ? (r.top < 0 ? 100 : 0) : Math.min(100, Math.max(0, (-r.top / total) * 100));
            readFill.style.width = pct + '%';
            bar.setAttribute('aria-valuenow', String(Math.round(pct)));
        };
        window.addEventListener('scroll', upd, { passive: true });
        window.addEventListener('resize', upd);
        upd();
    }

    /* ================= 14. "SEE MORE" CLAMP ================= */
    $all('.clamp').forEach(function (box) {
        if (box.scrollHeight <= box.clientHeight + 4) { box.classList.add('clamp-fits'); return; }
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'see-more';
        b.textContent = 'See more';
        b.setAttribute('aria-expanded', 'false');
        box.insertAdjacentElement('afterend', b);
        b.addEventListener('click', function () {
            var open = box.classList.toggle('is-open');
            b.textContent = open ? 'See less' : 'See more';
            b.setAttribute('aria-expanded', String(open));
        });
    });

    /* ================= 15. LIVE SCORE GAUGE ================= */
    $all('[data-score-preview]').forEach(function (gauge) {
        var input = $(gauge.dataset.scorePreview);
        var fillEl = $('.score-fill', gauge), label = $('[data-score-label]', gauge), note = $('[data-score-note]', gauge);
        if (!input || !fillEl) return;
        // Redraw the score gauge from the 1-10 input.
        function upd() {
            var v = Math.max(0, Math.min(10, parseInt(input.value, 10) || 0));
            fillEl.style.width = Math.max(2, v * 10) + '%';
            fillEl.className = 'score-fill ' + (v >= 7 ? 'hf-high' : v >= 4 ? 'hf-mid' : 'hf-low');
            if (label) label.textContent = v + ' / 10';
            var scale = null;
            try { scale = JSON.parse(gauge.dataset.scoreScale || 'null'); } catch (e) {}
            if (note) note.textContent = (scale && scale[v - 1]) ? scale[v - 1]
                : v >= 7 ? 'High on the scale.'
                : v >= 4 ? 'Middle of the scale.' : 'Low on the scale.';
            $all('[data-live-hz]').forEach(function (b) { b.textContent = 'S' + v; b.className = 'ctb-hz ' + (v >= 7 ? 'is-high' : v >= 4 ? 'is-mid' : 'is-low'); });
        }
        input.addEventListener('input', upd);
        upd();
    });

    /* "Where does it sit?" — choosing a rung sets a consistent score */
    $all('[data-score-suggest]').forEach(function (sel) {
        var range = $(sel.dataset.scoreSuggest);
        if (!range) return;
        sel.addEventListener('change', function () {
            if (!sel.value) return;
            range.value = sel.value;
            range.dispatchEvent(new Event('input', { bubbles: true }));
            range.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });

    /* live card preview on the contribute page */
    $all('[data-live-form]').forEach(function (form) {
        var card = $('[data-live-card]');
        if (!card) return;
        var defaults = {};
        $all('[data-live]', card).forEach(function (el) { defaults[el.dataset.live] = el.dataset.live === 'code' ? 'NO CODE' : el.textContent; });
        defaults.title = 'Untitled article'; defaults.summary = 'Your summary appears here as you type.';
        var tags = $('[data-live-tags]', card);
        // Copy form values into the live preview card.
        function render() {
            $all('[data-live]', card).forEach(function (el) {
                var f = form.elements[el.dataset.live];
                var v = f ? f.value.trim() : '';
                el.textContent = v || defaults[el.dataset.live] || '';
            });
            if (tags) tags.textContent = $all('input[name="categories[]"]:checked', form).map(function (c) { return c.dataset.text; }).join(' · ');
        }
        form.addEventListener('input', render);
        form.addEventListener('change', render);
        render();
    });

    /* ================= 16. TABLE QUICK FILTER ================= */
    $all('[data-table-filter]').forEach(function (input) {
        var table = $(input.dataset.tableFilter);
        var out = input.dataset.countTarget ? $(input.dataset.countTarget) : null;
        if (!table) return;
        input.addEventListener('input', function () {
            var q = input.value.trim().toLowerCase(), n = 0;
            $all('tbody tr', table).forEach(function (tr) {
                var hit = tr.textContent.toLowerCase().indexOf(q) !== -1;
                tr.hidden = !hit; if (hit) n++;
            });
            if (out) out.textContent = q ? n + ' match' + (n === 1 ? '' : 'es') : '';
        });
    });

    /* ================= 17. IMAGE PREVIEW (File API + FileReader) ================= */
    $all('input[type=file][data-file-preview]').forEach(function (input) {
        var fig = $(input.dataset.filePreview);
        if (!fig || !window.FileReader) return;
        var img = $('img', fig), cap = $('figcaption', fig);
        input.addEventListener('change', function () {
            var f = input.files && input.files[0];
            if (!f || !/^image\//.test(f.type)) { fig.hidden = true; return; }
            var reader = new FileReader();
            reader.onload = function () {
                img.src = reader.result;
                if (cap) cap.textContent = f.name + ' · ' + (f.size / 1024 / 1024).toFixed(2) + ' MB';
                fig.hidden = false;
            };
            reader.readAsDataURL(f);
        });
    });

    /* ================= 17b. MEDIA SUBMIT: type cards + drag-and-drop ================= */
    $all('[data-media-form]').forEach(function (form) {
        var file = $('#media_file', form), zone = $('[data-dropzone]', form);
        function sync() {
            var r = $('input[name=media_type]:checked', form), type = r ? r.value : 'IMAGE';
            $all('[data-media-only]', form).forEach(function (el) { el.hidden = el.dataset.mediaOnly !== type; });
            if (type !== 'IMAGE' && file && file.value) {          // a hidden file must not be sent
                file.value = ''; file.dispatchEvent(new Event('change'));
            }
        }
        $all('input[name=media_type]', form).forEach(function (r) { r.addEventListener('change', sync); });
        sync();
        if (!zone || !file) return;
        ['dragenter', 'dragover'].forEach(function (ev) {
            zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add('is-over'); });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            zone.addEventListener(ev, function () { zone.classList.remove('is-over'); });
        });
        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            if (!e.dataTransfer || !e.dataTransfer.files.length) return;
            file.files = e.dataTransfer.files;
            file.dispatchEvent(new Event('change', { bubbles: true }));
        });
        file.addEventListener('change', function () { zone.classList.toggle('has-file', !!file.value); });
    });

    /* ================= 19. UPLOAD SIZE PRE-CHECK ================= */
    $all('input[type="file"][data-max-bytes]').forEach(function (input) {
        var max = parseInt(input.dataset.maxBytes, 10);
        if (!max) return;
        input.addEventListener('change', function () {
            var f = input.files && input.files[0];
            var old = $('.file-too-big', input.form);
            if (old) old.parentNode.removeChild(old);
            if (f && f.size > max) {
                var note = document.createElement('p');
                note.className = 'file-too-big';
                note.setAttribute('role', 'alert');
                note.textContent = 'That file is ' + (f.size / 1024 / 1024).toFixed(1) +
                    ' MB — the limit is ' + Math.round(max / 1024 / 1024) + ' MB. Choose a smaller file.';
                input.parentNode.insertBefore(note, input.nextSibling);
                input.value = '';
            }
        });
    });
})();

/* ---- 20. Gallery filter ------------------------------------------ */
(function () {
    var bar = document.querySelector('[data-gallery-filter]');
    var grid = document.querySelector('[data-gallery-grid]');
    if (!bar || !grid) return;
    var empty = document.querySelector('[data-gallery-empty]');
    bar.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-filter]');
        if (!btn) return;
        var f = btn.getAttribute('data-filter'), shown = 0;
        bar.querySelectorAll('[data-filter]').forEach(function (b) {
            var on = b === btn; b.classList.toggle('is-active', on); b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        grid.querySelectorAll('.g-card').forEach(function (c) {
            var ok = f === 'all' || c.getAttribute('data-group') === f;
            c.hidden = !ok; if (ok) shown++;
        });
        if (empty) empty.classList.toggle('hidden', shown > 0);
    });
})();

/* ---- 21. Media library -------------------------------------------- */
(function () {
    var frame = document.querySelector('[data-player]');
    if (frame) {
        var title = document.querySelector('[data-player-title]');
        var note = document.querySelector('[data-player-note]');
        var credit = document.querySelector('[data-player-credit]');
        // The real site swapped the poster for a youtube-nocookie.com iframe. The mock never contacts YouTube.
        var load = function () { if (window.mockToast) window.mockToast('Mock only: the real site plays the video here.', 'info'); };
        // Mark a playlist item as current and load its video.
        var select = function (btn, autoplay) {
            document.querySelectorAll('.playlist-item').forEach(function (b) {
                var on = b === btn; b.classList.toggle('is-current', on); b.setAttribute('aria-current', on ? 'true' : 'false');
            });
            title.textContent = btn.getAttribute('data-pl-title');
            note.textContent = btn.getAttribute('data-pl-note');
            var by = btn.getAttribute('data-pl-credit') || '';
            credit.textContent = by;
            var src = document.querySelector('[data-player-src]'), link = document.querySelector('[data-player-link]'),
                ball = document.querySelector('[data-player-ball]');
            if (src) src.textContent = btn.getAttribute('data-pl-src') || '';
            if (link) link.href = btn.getAttribute('data-pl-url');
            if (ball) ball.textContent = by.charAt(0).toUpperCase();
        };
        document.addEventListener('click', function (e) {
            var item = e.target.closest('.playlist-item');
            if (item) { select(item, true); return; }
            var jump = e.target.closest('[data-pl-jump]');
            if (jump) {
                var target = document.querySelector('.playlist-item[data-pl-id="' + jump.getAttribute('data-pl-jump') + '"]');
                if (target) {
                    select(target, true);
                    frame.scrollIntoView({ block: 'start', behavior: 'smooth' });
                }
            }
        });
    }

    // Search + chips + sort work together.
    var tools = document.querySelector('[data-media-tools]');
    var grid = document.querySelector('[data-media-grid]');
    if (tools && grid) {
        var bar = tools.querySelector('[data-media-filter]');
        var q = tools.querySelector('[data-media-q]');
        var sortSel = tools.querySelector('[data-media-sort]');
        var status = document.querySelector('[data-media-status]');
        var empty = document.querySelector('[data-media-empty]');
        var cards = Array.prototype.slice.call(grid.querySelectorAll('.mx-card'));
        var filter = 'all';
        var apply = function () {
            var term = (q.value || '').trim().toLowerCase(), shown = 0;
            cards.forEach(function (c) {
                var okType = filter === 'all' || c.getAttribute('data-type') === filter || c.getAttribute('data-source') === filter;
                var ok = okType && (!term || c.getAttribute('data-search').indexOf(term) !== -1);
                c.hidden = !ok; if (ok) shown++;
            });
            var mode = sortSel.value, sorted = cards.slice();
            sorted.sort(function (x, y) {
                if (mode === 'new') return (+y.dataset.date) - (+x.dataset.date) || (+x.dataset.order) - (+y.dataset.order);
                if (mode === 'old') return (+x.dataset.date || 9e12) - (+y.dataset.date || 9e12);
                if (mode === 'az') return x.dataset.title.localeCompare(y.dataset.title);
                return (+x.dataset.order) - (+y.dataset.order);
            });
            sorted.forEach(function (c) { grid.appendChild(c); });
            var active = filter !== 'all' || term;
            status.innerHTML = '';
            if (active) {
                var label = bar.querySelector('[data-filter="' + filter + '"]');
                var txt = 'Showing ' + shown + ' of ' + cards.length;
                if (filter !== 'all' && label) txt += ' \u00b7 ' + label.firstChild.textContent.trim();
                if (term) txt += ' \u00b7 \u201c' + term + '\u201d';
                status.appendChild(document.createTextNode(txt + ' '));
                var clr = document.createElement('button');
                clr.type = 'button'; clr.className = 'mx-clear'; clr.textContent = 'Clear';
                clr.setAttribute('data-media-clear', '');
                status.appendChild(clr);
            }
            if (empty) empty.hidden = shown > 0;
        };
        bar.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-filter]');
            if (!btn) return;
            filter = btn.getAttribute('data-filter');
            bar.querySelectorAll('[data-filter]').forEach(function (b) {
                var on = b === btn; b.classList.toggle('is-active', on); b.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            apply();
        });
        var timer;
        q.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(apply, 120); });
        sortSel.addEventListener('change', apply);
        document.addEventListener('click', function (e) {
            if (!e.target.closest('[data-media-clear]')) return;
            q.value = ''; sortSel.value = 'default'; filter = 'all';
            bar.querySelectorAll('[data-filter]').forEach(function (b) {
                var on = b.getAttribute('data-filter') === 'all'; b.classList.toggle('is-active', on); b.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            apply(); q.focus();
        });
    }

    var dlg = document.querySelector('[data-lightbox-dialog]');
    if (dlg && typeof dlg.showModal === 'function') {
        var art = dlg.querySelector('[data-lightbox-img]'), cap = dlg.querySelector('[data-lightbox-cap]');
        document.addEventListener('click', function (e) {
            var t = e.target.closest('[data-lightbox]');
            if (!t) return;
            // The real lightbox showed the uploaded image; the mock shows the same generated colour art.
            art.style.setProperty('--h', t.getAttribute('data-lightbox-hue') || '220');
            art.textContent = (t.getAttribute('aria-label') || '').replace(/^View image:\s*/, '');
            cap.textContent = t.getAttribute('data-lightbox-alt') || '';
            dlg.showModal();
        });
        dlg.addEventListener('click', function (e) { if (e.target === dlg || e.target.closest('[data-close-modal]')) dlg.close(); });
    }
})();

/* ---- 22. Caps Lock hint on password fields -------------------------- */
(function () {
    var fields = document.querySelectorAll('input[data-capslock]');
    if (!fields.length) return;
    fields.forEach(function (f) {
        var scope = f.closest('form') || document;
        var note = scope.querySelector('[data-capslock-note]');
        if (!note) return;
        // Show the Caps Lock hint while Caps Lock is on.
        var check = function (e) {
            if (e && typeof e.getModifierState === 'function') note.hidden = !e.getModifierState('CapsLock');
        };
        f.addEventListener('keydown', check);
        f.addEventListener('keyup', check);
        f.addEventListener('blur', function () { note.hidden = true; });
    });
})();

(function () {
    document.querySelectorAll('table').forEach(function (table) {
        if (table.classList.contains('no-stack') || !table.tHead) return;
        var titles = Array.prototype.map.call(
            table.tHead.querySelectorAll('th'), function (th) { return th.textContent.trim(); });
        Array.prototype.forEach.call(table.tBodies, function (tbody) {
            Array.prototype.forEach.call(tbody.rows, function (row) {
                Array.prototype.forEach.call(row.cells, function (cell, i) {
                    // row headers (<th scope="row">) are the card title, no label needed
                    if (cell.tagName === 'TD' && !cell.hasAttribute('data-label')) {
                        cell.setAttribute('data-label', titles[i] || '');
                    }
                });
            });
        });
        table.classList.add('m-stack');
    });
})();

/* ---- 24. Gallery carousel ------------------------------------ */
(function () {
    document.querySelectorAll('[data-vcar]').forEach(function (root) {
        var slides = Array.prototype.slice.call(root.querySelectorAll('.vcar-slide'));
        var n = slides.length;
        if (n < 2) return;
        var html = document.documentElement;
        var prefs = {}; try { prefs = JSON.parse(localStorage.getItem('demo-prefs') || '{}'); } catch (e) {}
        var noAuto = html.classList.contains('reduce-motion') || prefs.autoplay === false
            || (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
        var wait = parseInt(root.dataset.interval, 10) || 5000;
        var cur = 0, timer = null, userPaused = noAuto, hover = false;
        var numEl = root.querySelector('[data-vcar-num]'), nameEl = root.querySelector('[data-vcar-name]');
        var ticksEl = root.querySelector('[data-vcar-ticks]'), pauseBtn = root.querySelector('[data-vcar-pause]');
        var ticks = [];

        // One tick per rung, coloured by its class (style.css PART 13).
        slides.forEach(function (s, i) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'vcar-tick';
            b.dataset.class = s.dataset.class;
            b.setAttribute('aria-label', 'Rung ' + (i + 1) + ': ' + s.dataset.name);
            b.addEventListener('click', function () { go(i, true); });
            ticksEl.appendChild(b);
            ticks.push(b);
        });

        // Place every slide by its distance from the current one (-2..2).
        function layout() {
            slides.forEach(function (s, i) {
                var d = i - cur;
                if (d > n / 2) d -= n;
                if (d < -n / 2) d += n;
                s.dataset.pos = Math.abs(d) <= 2 ? String(d) : 'off';
                s.setAttribute('aria-hidden', d === 0 ? 'false' : 'true');
                var link = s.querySelector('a');
                if (link) link.tabIndex = d === 0 ? 0 : -1;
            });
            ticks.forEach(function (t, i) {
                t.classList.toggle('is-on', i === cur);
                t.classList.toggle('is-past', i < cur);
                t.setAttribute('aria-current', i === cur ? 'true' : 'false');
            });
            numEl.textContent = String(cur + 1).padStart(2, '0');
            nameEl.textContent = slides[cur].dataset.name + ' \u00b7 ' + slides[cur].dataset.meta.replace(/&middot;/g, '\u00b7');
        }
        function go(i, byUser) {
            cur = (i + n) % n;
            layout();
            restart();
        }
        // (Re)start the 5-second timer and the progress fill on the current tick.
        function restart() {
            clearTimeout(timer);
            root.classList.remove('is-running');
            void root.offsetWidth;                      // restart the CSS fill animation
            if (userPaused || hover || document.hidden) return;
            root.style.setProperty('--vcar-wait', wait + 'ms');
            root.classList.add('is-running');
            timer = setTimeout(function () { go(cur + 1); }, wait);
        }
        function setPaused(p) {
            userPaused = p;
            root.classList.toggle('is-paused', p);
            pauseBtn.setAttribute('aria-pressed', p ? 'true' : 'false');
            pauseBtn.setAttribute('aria-label', p ? 'Resume auto-advance' : 'Pause auto-advance');
            restart();
        }

        root.querySelector('[data-vcar-prev]').addEventListener('click', function () { go(cur - 1, true); });
        root.querySelector('[data-vcar-next]').addEventListener('click', function () { go(cur + 1, true); });
        pauseBtn.addEventListener('click', function () { setPaused(!userPaused); });

        // A click on a peeking neighbour moves the carousel instead of leaving the page.
        slides.forEach(function (s, i) {
            s.addEventListener('click', function (e) {
                if (i !== cur) { e.preventDefault(); go(i, true); }
            });
        });
        root.addEventListener('mouseenter', function () { hover = true; restart(); });
        root.addEventListener('mouseleave', function () { hover = false; restart(); });
        root.addEventListener('focusin', function () { hover = true; restart(); });
        root.addEventListener('focusout', function (e) { if (!root.contains(e.relatedTarget)) { hover = false; restart(); } });
        document.addEventListener('visibilitychange', restart);
        root.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') { e.preventDefault(); go(cur - 1, true); }
            if (e.key === 'ArrowRight') { e.preventDefault(); go(cur + 1, true); }
        });
        // Touch swipe: 40 px sideways is enough, vertical scrolling still works.
        var sx = null, sy = 0;
        root.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; sy = e.touches[0].clientY; }, { passive: true });
        root.addEventListener('touchend', function (e) {
            if (sx === null) return;
            var dx = e.changedTouches[0].clientX - sx, dy = e.changedTouches[0].clientY - sy;
            if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) go(cur + (dx < 0 ? 1 : -1), true);
            sx = null;
        }, { passive: true });

        root.querySelectorAll('[hidden]').forEach(function (el) { el.hidden = false; });
        root.classList.add('is-ready');
        root.tabIndex = 0;
        setPaused(userPaused);
        layout();
    });
})();

/* ---- 26. Greeting + "Surprise me" spin ----------------------- */
(function () {
    var h = new Date().getHours();
    document.querySelectorAll('[data-greet]').forEach(function (el) {
        el.textContent = h < 5 ? 'Working late' : h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
    });
    document.querySelectorAll('[data-surprise]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            if (document.documentElement.classList.contains('reduce-motion')) return;
            e.preventDefault();
            a.classList.add('is-spinning');
            setTimeout(function () { location.href = a.href; }, 420);
        });
    });
})();

/* #################### PART B - sidebar, ball, motion #################### */
// PART B (was main.js): sidebar, account menu, draggable menu ball.
(function () {
    'use strict';

    var body = document.body;

    /* ---- Desktop sidebar collapse (remembers your choice) ---- */
    try {
        if (localStorage.getItem('demo-sidebar') === 'hidden') {
            body.classList.add('sidebar-hidden');
        }
    } catch (e) { /* storage unavailable */ }

    var sidebarToggle = document.getElementById('sidebar-toggle');
    // Update the sidebar toggle's label and aria state.
    function syncToggle() {
        if (!sidebarToggle) return;
        var hidden = body.classList.contains('sidebar-hidden');
        var label = hidden ? 'Show sidebar' : 'Hide sidebar';   // icon flips via CSS: "<" ↔ ">"
        sidebarToggle.setAttribute('aria-label', label);
        sidebarToggle.setAttribute('title', label);
        sidebarToggle.setAttribute('aria-expanded', hidden ? 'false' : 'true');
    }
    if (sidebarToggle) {
        syncToggle();
        sidebarToggle.addEventListener('click', function () {
            body.classList.toggle('sidebar-hidden');
            syncToggle();
            try {
                localStorage.setItem('demo-sidebar',
                    body.classList.contains('sidebar-hidden') ? 'hidden' : 'visible');
            } catch (e) {}
        });
    }

    /* ---- top-bar account menu (click / Esc / outside click) ---- */
    var acct = document.querySelector('[data-account-menu]');
    if (acct) {
        var acctBtn = acct.querySelector('.topbar-avatar'), acctMenu = acct.querySelector('.account-menu');
        // Open or close the account menu in the top bar.
        var setAcct = function (open) {
            acctMenu.hidden = !open;
            acctBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) { var f = acctMenu.querySelector('a'); if (f) f.focus(); }
        };
        acctBtn.addEventListener('click', function (e) { e.stopPropagation(); setAcct(acctMenu.hidden); });
        document.addEventListener('click', function (e) { if (!acct.contains(e.target)) setAcct(false); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !acctMenu.hidden) { setAcct(false); acctBtn.focus(); }
        });
    }

    /* ---- Mobile: DRAGGABLE ball + chip navbar ------------------ */
    var ball = document.getElementById('nav-ball');
    var menu = document.getElementById('mobile-menu');
    var chipNav = document.getElementById('chip-nav') || document.querySelector('.chip-nav');

    var BALL_SIZE = 52, BALL_MARGIN = 12, BALL_TOP_MIN = 66, BALL_TOP_MAX_VH = 0.82;

    // Visible screen height (handles mobile browser bars).
    function viewportH() {
        return (window.visualViewport && window.visualViewport.height)
            ? window.visualViewport.height : window.innerHeight;
    }
    // Keep the menu ball inside the allowed vertical area.
    function clampBallY(y) {
        var max = viewportH() * BALL_TOP_MAX_VH - BALL_SIZE;
        return Math.min(Math.max(y, BALL_TOP_MIN), Math.max(BALL_TOP_MIN, max));
    }
    // Move the ball to the left or right edge at height y.
    function placeBall(side, y) {
        if (!ball) return;
        ball.style.top = clampBallY(y) + 'px';
        ball.style.left = (side === 'left' ? BALL_MARGIN
            : window.innerWidth - BALL_SIZE - BALL_MARGIN) + 'px';
        ball.style.right = 'auto';
        ball.dataset.side = side;
    }

    // Close the mobile menu sheet.
    function closeMenu() {
        body.classList.remove('menu-open');
        if (ball && ball.dataset.liftFrom) { ball.style.top = ball.dataset.liftFrom; delete ball.dataset.liftFrom; }
        if (ball) ball.setAttribute('aria-expanded', 'false');
        if (menu) menu.setAttribute('aria-hidden', 'true');      // Keep screen readers in sync
    }
    // Open the mobile menu sheet and centre the active chip.
    function openMenu() {
        body.classList.add('menu-open');
        if (ball) ball.setAttribute('aria-expanded', 'true');
        // Lift the ball above the sheet so it never covers the Log out / Log in button
        var sheet = menu && menu.querySelector('.mm-sheet');
        if (ball && sheet) {
            var sheetTop = window.innerHeight - sheet.offsetHeight, top = parseFloat(ball.style.top) || ball.offsetTop;
            if (top + BALL_SIZE > sheetTop - 8) { ball.dataset.liftFrom = ball.style.top; ball.style.top = Math.max(BALL_MARGIN, sheetTop - BALL_SIZE - 12) + 'px'; }
        }
        if (menu) menu.setAttribute('aria-hidden', 'false');
        if (chipNav) {
            // centre the active chip when the sheet opens
            var active = chipNav.querySelector('.chip.active');
            if (active && active.scrollIntoView) {
                try { active.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'instant' }); }
                catch (e) { active.scrollIntoView(); }
            }
        }
    }

    if (ball) {
        // restore the remembered spot (default: lower right, clear of content)
        (function restoreBall() {
            var saved = null;
            try { saved = JSON.parse(localStorage.getItem('demo-ball') || 'null'); } catch (e) {}
            if (saved && (saved.side === 'left' || saved.side === 'right')) {
                placeBall(saved.side, typeof saved.y === 'number' ? saved.y : 260);
            } else {
                placeBall('right', 99999);   // lowest allowed spot: bottom-right, above back-to-top
            }
            // keep the ball on-screen across rotation / URL-bar collapses
            var recheck = function () {
                if (body.classList.contains('menu-open')) return;
                placeBall(ball.dataset.side || 'right', parseFloat(ball.style.top) || 300);
            };
            window.addEventListener('resize', recheck);
            window.addEventListener('orientationchange', recheck);
            if (window.visualViewport) window.visualViewport.addEventListener('resize', recheck);
        })();

        var dragging = false, moved = false, pid = null, sx = 0, sy = 0, sLeft = 0, sTop = 0;
        var suppressClick = false;

        ball.addEventListener('pointerdown', function (e) {
            if (body.classList.contains('menu-open')) return;   // no drag while open
            if (e.pointerType === 'mouse' && e.button !== 0) return;
            dragging = true; moved = false; pid = e.pointerId;
            sx = e.clientX; sy = e.clientY;
            var r = ball.getBoundingClientRect();
            sLeft = r.left; sTop = r.top;
            ball.classList.add('dragging');      // full opacity, 1:1 follow
            ball.style.transition = 'none';
            try { ball.setPointerCapture(e.pointerId); } catch (err) {}
        });

        ball.addEventListener('pointermove', function (e) {
            if (!dragging || e.pointerId !== pid) return;
            var dx = e.clientX - sx, dy = e.clientY - sy;
            if (Math.abs(dx) > 6 || Math.abs(dy) > 6) moved = true;
            if (!moved) return;
            ball.style.left = Math.min(Math.max(sLeft + dx, BALL_MARGIN),
                                       window.innerWidth - BALL_SIZE - BALL_MARGIN) + 'px';
            ball.style.right = 'auto';
            ball.style.top = clampBallY(sTop + dy) + 'px';
        });

        // Finish dragging the ball: snap it to the nearest side and save the position.
        function endDrag() {
            if (!dragging) return;
            dragging = false;
            ball.classList.remove('dragging');
            ball.style.transition = '';           // CSS transition returns for the snap
            if (!moved) return;                   // plain tap -> the click handler opens the sheet

            // snap to the nearest side with a spring-ish ease
            var r = ball.getBoundingClientRect();
            var goLeft = (r.left + BALL_SIZE / 2) < window.innerWidth / 2;
            placeBall(goLeft ? 'left' : 'right', r.top);
            ball.style.transitionTimingFunction = 'cubic-bezier(.34,1.56,.64,1)';
            setTimeout(function () { ball.style.transitionTimingFunction = ''; }, 350);
            try { localStorage.setItem('demo-ball',
                JSON.stringify({ side: goLeft ? 'left' : 'right', y: parseFloat(ball.style.top) })); } catch (e) {}

            // the click that follows a drag must not toggle the sheet
            suppressClick = true;
            setTimeout(function () { suppressClick = false; }, 350);
        }
        ball.addEventListener('pointerup', endDrag);
        ball.addEventListener('pointercancel', function () {
            dragging = false;
            ball.classList.remove('dragging');
            ball.style.transition = '';
        });

        ball.addEventListener('click', function () {
            if (suppressClick) return;
            if (body.classList.contains('menu-open')) closeMenu(); else openMenu();
        });
    }
    if (menu) {
        menu.addEventListener('click', function (e) {
            if (e.target === menu || e.target.closest('.mobile-menu-link')) closeMenu();
        });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
    });
})();

/* ---- 28. Discussion dock (was app.js PART D) ---- */
(function () {
    'use strict';
    var chat = document.querySelector('[data-cm-chat]');
    if (chat) {
        // Park the dock on <body>: a fixed panel inside <main> would be positioned against whichever
        // ancestor is mid-transition (the page-enter tween moves <main>), and would jump for a moment.
        if (chat.parentNode !== document.body) document.body.appendChild(chat);
        var panel = chat.querySelector('.cm-chat');
        var openers = Array.prototype.slice.call(document.querySelectorAll('[data-cm-open]'));
        var badge = chat.querySelector('[data-cm-badge]');
        var fab = chat.querySelector('.cm-fab');
        var total = chat.querySelector('[data-cm-total]');
        var headerCount = chat.querySelector('[data-cm-count]');
        var last = parseInt(headerCount ? headerCount.textContent : '0', 10) || 0;
        var unread = 0, isOpen = false;

        function paintBadge() {
            if (badge) {
                badge.hidden = unread < 1;
                badge.textContent = unread > 9 ? '9+' : String(unread || '');
            }
            chat.classList.toggle('has-unread', unread > 0);
            if (fab) {
                fab.setAttribute('aria-label', unread
                    ? 'Open the discussion (' + unread + ' new message' + (unread === 1 ? '' : 's') + ')'
                    : 'Open the discussion');
            }
        }
        function focusInput() {
            var input = panel.querySelector('[data-cm-input]');
            setTimeout(function () {
                if (input) input.focus();
                else { var c = panel.querySelector('[data-cm-close]'); if (c) c.focus(); }
            }, 200);
        }
        function setOpen(open, focus) {
            if (open === isOpen) { if (open && focus) focusInput(); return; }
            isOpen = open;
            chat.classList.toggle('is-open', open);
            panel.setAttribute('aria-hidden', open ? 'false' : 'true');
            openers.forEach(function (b) { b.setAttribute('aria-expanded', open ? 'true' : 'false'); });
            if (open) {
                unread = 0;
                paintBadge();
                var list = panel.querySelector('[data-cm-list]');
                if (list) requestAnimationFrame(function () { list.scrollTop = list.scrollHeight; });
                if (focus) focusInput();
            } else if (focus) {
                if (fab) fab.focus();
            }
        }

        openers.forEach(function (b) {
            b.addEventListener('click', function (e) {
                e.preventDefault();
                setOpen(!isOpen, true);
            });
        });
        panel.addEventListener('click', function (e) {
            if (e.target.closest && e.target.closest('[data-cm-close]')) { e.preventDefault(); setOpen(false, true); }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen) setOpen(false, true);
        });
        document.addEventListener('pointerdown', function (e) {
            if (!isOpen || chat.contains(e.target)) return;
            if (e.target.closest && e.target.closest('[data-cm-open]')) return;
            setOpen(false, false);
        });
        // A link to #discussion (an email deep link, the "join the discussion" line) opens the dock, and so
        // does an email link to one message (#comment-19): the message is scrolled into view inside it.
        function fromHash() {
            var h = window.location.hash || '';
            if (h === '#discussion') { setOpen(true, true); return; }
            var m = /^#comment-(\d+)$/.exec(h);
            if (!m) return;
            setOpen(true, false);
            var node = document.getElementById('comment-' + m[1]);
            if (!node) return;
            setTimeout(function () {
                try { node.scrollIntoView({ block: 'center', behavior: 'smooth' }); } catch (e) { /* ignore */ }
            }, 260);
        }
        window.addEventListener('hashchange', fromHash);
        fromHash();
        paintBadge();

    }
})();

/* ---- 29. Glossary "On this page" rail ---- */
(function () {
    'use strict';
    var rail = document.querySelector('[data-cx-rail]');
    if (!rail) return;
    var links = Array.prototype.slice.call(rail.querySelectorAll('a'));
    var secs = links.map(function (a) { return document.querySelector(a.getAttribute('href')); });
    var current = null, ticking = false;
    function pick() {
        ticking = false;
        var r = rail.getBoundingClientRect();
        var sticky = getComputedStyle(rail).display === 'flex';        // phone layout: rail is a bar on top
        var line = (sticky ? r.bottom : 0) + window.innerHeight * 0.25; // reading line
        var idx = 0;
        secs.forEach(function (sec, i) { if (sec && sec.getBoundingClientRect().top <= line) idx = i; });
        // At the very bottom the last short sections can never reach the line: then the lowest section
        // whose heading is on screen wins.
        if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4) {
            secs.forEach(function (sec, i) { if (sec && sec.getBoundingClientRect().top < window.innerHeight * 0.6) idx = i; });
        }
        if (current === idx) return;
        current = idx;
        links.forEach(function (a, i) { a.classList.toggle('is-active', i === idx); });
        if (rail.scrollWidth > rail.clientWidth) {                       // keep the active tab in view
            var a = links[idx];
            rail.scrollTo({ left: Math.max(0, a.offsetLeft - (rail.clientWidth - a.offsetWidth) / 2), behavior: 'smooth' });
        }
    }
    function queue() { if (!ticking) { ticking = true; requestAnimationFrame(pick); } }
    window.addEventListener('scroll', queue, { passive: true });
    window.addEventListener('resize', queue);
    window.addEventListener('load', queue);
    links.forEach(function (a) { a.addEventListener('click', function () { setTimeout(queue, 600); }); });
    pick();
})();

/* #################### PART C - mock-only behaviour ####################
   Small vanilla rewrites of things the real site did with jQuery, GSAP or AJAX
   (ajax.js). Nothing here talks to a server: every action stays on this screen. */
(function () {
    'use strict';
    var html = document.documentElement;
    function $(sel, root) { return (root || document).querySelector(sel); }
    function $all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
    function toast(m, t) { if (window.mockToast) window.mockToast(m, t || 'info'); }
    var reduce = html.classList.contains('reduce-motion') || (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    var MOCK = window.MOCK || {};

    /* ---- Preview as: switching role submits at once (the button stays for no-JS) ---- */
    $all('[data-preview-as]').forEach(function (f) {
        var go = $('.preview-go', f); if (go) go.hidden = true;
        var sel = $('select', f);
        if (sel) sel.addEventListener('change', function () { HTMLFormElement.prototype.submit.call(f); });
    });

    /* ---- GET filter forms re-run when a select changes ---- */
    $all('form[data-autosubmit]').forEach(function (f) {
        $all('select', f).forEach(function (s) { s.addEventListener('change', function () { f.submit(); }); });
    });

    /* ---- Buttons that only pretend: [data-mock-msg] and button[data-confirm] outside forms ---- */
    document.addEventListener('click', function (e) {
        var m = e.target.closest('[data-mock-msg]');
        if (m) { e.preventDefault(); toast(m.getAttribute('data-mock-msg'), 'info'); return; }
        var b = e.target.closest('button[data-confirm]');
        if (b && window.mockConfirm) {
            e.preventDefault();
            window.mockConfirm({
                title: b.dataset.confirmTitle, message: b.dataset.confirm, okLabel: b.dataset.confirmLabel,
                tone: b.dataset.confirmTone || 'danger', reason: b.hasAttribute('data-reason'), reasonLabel: 'Reason (the author is told) *'
            }).then(function (r) { if (r !== null) toast('Mock only: nothing was changed. The real site would do this now.', 'success'); });
        }
    });

    /* ---- Notification bell: dropdown (was ajax.js, which also polled the server every 30 s) ---- */
    var notif = $('[data-notif]');
    if (notif) {
        var panel = $('#notif-panel', notif) || $('.notif-panel', notif), toggle = $('[data-notif-toggle]', notif);
        var open = function (o) { if (!panel) return; panel.hidden = !o; toggle.setAttribute('aria-expanded', String(o)); };
        if (toggle && panel) {
            toggle.addEventListener('click', function (e) {
                if (window.innerWidth < 380) return;                // tiny screens: open the full page
                e.preventDefault(); open(panel.hidden);
            });
            document.addEventListener('click', function (e) { if (!notif.contains(e.target)) open(false); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.hidden) { open(false); toggle.focus(); } });
            var all = $('[data-notif-readall]', notif);
            if (all) all.addEventListener('click', function () {
                $all('.notif-item', notif).forEach(function (i) { i.classList.remove('is-unread'); });
                $all('.notif-dot', notif).forEach(function (d) { d.remove(); });
                var b = $('[data-notif-badge]', notif); if (b) b.remove();
                toast('All marked as read (on this screen only).', 'success');
            });
        }
    }

    /* ---- Bulletin board: expand, mark read, All / Unread filter (was ajax.js + jQuery effects) ---- */
    var board = $('#bulletin-board');
    if (board) {
        var filter = 'all';
        var counts = function () {
            var n = $all('.bulletin.is-unread', board).length;
            $all('[data-bulletin-filter="unread"] .count-pill').forEach(function (p) { p.textContent = n; });
            var empty = $('#bulletin-empty-unread');
            if (empty) empty.hidden = !(filter === 'unread' && n === 0);
        };
        board.addEventListener('click', function (e) {
            var t = e.target.closest('.bulletin-toggle'); if (!t) return;
            var item = t.closest('.bulletin'), body = $('.bulletin-body', item);
            var isOpen = t.getAttribute('aria-expanded') === 'true';
            t.setAttribute('aria-expanded', String(!isOpen));
            if (body) body.style.display = isOpen ? 'none' : 'block';
            if (!isOpen && item.classList.contains('is-unread')) {
                item.classList.remove('is-unread'); item.classList.add('is-read'); item.dataset.unread = '0';
                var dot = $('.unread-dot', item); if (dot) dot.style.display = 'none';
                counts();
            }
        });
        $all('[data-bulletin-filter]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                filter = btn.dataset.bulletinFilter;
                $all('[data-bulletin-filter]').forEach(function (b) { var on = b === btn; b.classList.toggle('is-active', on); b.setAttribute('aria-pressed', String(on)); });
                $all('.bulletin', board).forEach(function (li) { li.hidden = filter === 'unread' && !li.classList.contains('is-unread'); });
                counts();
            });
        });
        if (/^#b-\d+$/.test(location.hash)) { var tg = $(location.hash + ' .bulletin-toggle', board); if (tg) tg.click(); }
        counts();
    }

    /* ---- Small list filters ---- */
    $all('[data-mock-filter]').forEach(function (input) {
        var sel = input.dataset.mockFilter;
        input.addEventListener('input', function () {
            var q = input.value.trim().toLowerCase();
            $all(sel).forEach(function (r) { r.hidden = q !== '' && r.textContent.toLowerCase().indexOf(q) === -1; });
        });
    });
    $all('[data-level-filter]').forEach(function (sel) {
        var table = $(sel.dataset.levelFilter); if (!table) return;
        sel.addEventListener('change', function () {
            var v = sel.value;
            $all('tbody tr', table).forEach(function (tr) {
                var ok = !v || v === 'all' || tr.dataset.level === v
                    || (v === 'SUSPENDED' && tr.dataset.status === 'SUSPENDED') || (v === 'ONLINE' && tr.dataset.online === '1');
                tr.hidden = !ok;
            });
        });
    });

    /* ---- Complaint form: one evidence panel at a time ---- */
    var evRadios = $all('input[name="evidence_kind"]');
    if (evRadios.length) {
        var syncEv = function () {
            var r = evRadios.filter(function (x) { return x.checked; })[0], k = r ? r.value : '';
            $all('[data-evpanel]').forEach(function (p) {
                var on = p.dataset.evpanel === k; p.hidden = !on;
                $all('input, textarea', p).forEach(function (f) { f.disabled = !on; });
            });
        };
        evRadios.forEach(function (r) { r.addEventListener('change', syncEv); });
        syncEv();
    }

    /* ---- Reading list star (the real site saved it to the account) ---- */
    $all('[data-bookmark]').forEach(function (b) {
        var key = 'demo-bookmark-' + location.search;
        var paint = function (on) { b.setAttribute('aria-pressed', String(on)); var l = $('.bm-label', b); if (l) l.textContent = on ? 'Saved to reading list' : 'Save to reading list'; };
        try { paint(localStorage.getItem(key) === '1'); } catch (e) {}
        b.addEventListener('click', function () {
            var on = b.getAttribute('aria-pressed') !== 'true'; paint(on);
            try { if (on) localStorage.setItem(key, '1'); else localStorage.removeItem(key); } catch (e) {}
            toast(on ? 'Saved to your reading list (this browser only).' : 'Removed from your reading list.', 'success');
        });
    });

    /* ---- Discussion form: the message appears on this screen only ---- */
    var cmForm = $('[data-cm-form]');
    if (cmForm) {
        var input = $('[data-cm-input]', cmForm), list = $('[data-cm-list]');
        var send = function () {
            var v = input.value.trim(); if (!v || !list) return;
            var li = document.createElement('li'); li.className = 'cm-msg is-mine';
            var ball = $('.cm-ball-me', cmForm);
            li.innerHTML = '<span class="cm-ball">' + (ball ? ball.innerHTML : '') + '</span><div class="cm-bubble"><header class="cm-head"><span class="cm-name">You</span><time>just now</time></header><p class="cm-body"></p></div>';
            $('.cm-body', li).textContent = v;                    // textContent: never HTML
            list.appendChild(li); list.scrollTop = list.scrollHeight; input.value = '';
            toast('Mock only: your message was not saved.', 'info');
        };
        cmForm.addEventListener('submit', function (e) { e.preventDefault(); send(); });
        input.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); } });
        document.addEventListener('click', function (e) {
            var r = e.target.closest('[data-cm-reply]'); if (!r) return;
            var name = $('.cm-name', r.closest('.cm-msg'));
            input.value = '@' + (name ? name.textContent : '') + ' '; input.focus();
        });
    }

    /* ---- Scroll reveals (the real site used GSAP; this is a plain IntersectionObserver) ---- */
    var revealSel = '[data-arc-reveal], [data-stagger] > *, .reveal';
    var els = $all(revealSel);
    if (els.length && !reduce && 'IntersectionObserver' in window) {
        html.classList.add('js-anim');
        els.forEach(function (el) { el.classList.add('reveal'); });
        var io = new IntersectionObserver(function (en) {
            en.forEach(function (x) { if (x.isIntersecting) { x.target.classList.add('revealed'); io.unobserve(x.target); } });
        }, { rootMargin: '0px 0px -8% 0px' });
        els.forEach(function (el, i) {
            if (el.parentNode && el.parentNode.hasAttribute && el.parentNode.hasAttribute('data-stagger')) el.style.transitionDelay = Math.min(i % 8, 7) * 60 + 'ms';
            io.observe(el);
        });
    }

    /* ---- Keyboard shortcuts (was section 25; addresses come from window.MOCK) ---- */
    var dlg = document.getElementById('shortcuts-dialog'), pendingG = 0;
    var GO = { h: MOCK.home, g: MOCK.gallery, e: MOCK.explore, m: MOCK.media, d: MOCK.dashboard };
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey || e.metaKey || e.altKey) return;
        var el = document.activeElement, tag = (el && el.tagName) || '';
        if (/INPUT|TEXTAREA|SELECT/.test(tag) || (el && el.isContentEditable)) return;
        if (html.classList.contains('modal-open')) return;
        var k = e.key;
        if (k === '?' && dlg && window.mockModal) { e.preventDefault(); window.mockModal.open(dlg); return; }
        if (pendingG && Date.now() - pendingG < 1200 && GO[k]) { e.preventDefault(); pendingG = 0; location.href = GO[k]; return; }
        pendingG = 0;
        if (k === 'g') { pendingG = Date.now(); return; }
        if (k === 'r' && MOCK.random) { location.href = MOCK.random; return; }
        if (k === 't') window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    });
    $all('[data-open-shortcuts]').forEach(function (b) { b.addEventListener('click', function () { if (dlg && window.mockModal) window.mockModal.open(dlg); }); });

    /* ---- Top bar edge once the page moves (from the real animation layer) ---- */
    var topbar = $('.topbar');
    if (topbar) { var edge = function () { topbar.classList.toggle('is-scrolled', window.scrollY > 8); }; window.addEventListener('scroll', edge, { passive: true }); edge(); }
})();
