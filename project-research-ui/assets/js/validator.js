/* =====================================================================
   validator.js - client-side form checks for the research mock.
   Reused from the real project's Frontend/js/validator.js. Removed: the
   fetch() submit path and the plain-HTTP "stuck POST" workaround, which only
   mattered on the live server. The PHP Validator (app/core.php) re-checks
   everything; this layer only makes mistakes visible sooner.

     <form data-validate novalidate> ... </form>
     data-pattern="username|callsign|password|code6|designation"
     data-match="#password"   data-label="Username"
     Extras: data-counter (live character counter), data-strength
   Copyright (c) 2026 Ar Kar Phyo. All rights reserved.
   ===================================================================== */
'use strict';
/** Same regexes as Validator::PATTERNS in app/core.php */
const PATTERNS = {
    username: { regex: /^[A-Za-z0-9\-_]{3,50}$/, message: 'Use 3-50 letters, numbers, dashes or underscores (e.g. demo_user).' },
    callsign: { regex: /^[A-Za-z0-9 .'_\-]{2,50}$/, message: 'Use 2-50 letters, numbers, spaces, dots, dashes or underscores.' },
    password: { regex: /^(?=.*[A-Za-z])(?=.*\d).{8,}$/, message: 'At least 8 characters, including a letter and a number.' },
    code6: { regex: /^\d{6}$/, message: 'Enter the 6-digit code.' },
    designation: { regex: /^[A-Za-z0-9\/\-. ]{1,50}$/, message: 'Letters, numbers, "/", "-" and "." only (e.g. AR-01).' },
};
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
const URL_RE = /^https?:\/\/[^\s/$.?#].[^\s]*$/i;
// Field name for messages: data-label, else the <label> text.
function labelOf(field) {
    if (field.dataset.label)
        return field.dataset.label;
    const lbl = field.id ? document.querySelector(`label[for="${field.id}"]`) : null;
    return lbl ? (lbl.textContent || '').replace(/[*(][\s\S]*$/, '').trim() : 'This field';
}
/** Returns an error message, or '' when the field is valid. */
function checkField(field) {
    const value = field.value.trim();
    const label = labelOf(field);
    if (field instanceof HTMLInputElement && field.type === 'file') {
        const max = Number(field.dataset.maxBytes || 0);
        const file = field.files && field.files[0];
        if (file && max && file.size > max)
            return `${label}: file is too large (max ${(max / 1048576).toFixed(0)} MB).`;
        return '';
    }
    if (field.hasAttribute('required') && value === '')
        return `${label} is required.`;
    if (value === '')
        return ''; // optional + empty = fine
    const min = field.getAttribute('minlength');
    const max = field.getAttribute('maxlength');
    if (min && value.length < Number(min))
        return `${label} needs at least ${min} characters (${value.length} so far).`;
    if (max && value.length > Number(max))
        return `${label} allows at most ${max} characters.`;
    if (field instanceof HTMLInputElement) {
        if (field.type === 'email' && !EMAIL_RE.test(value))
            return 'Enter a valid email address (name@domain.org).';
        if (field.type === 'url' && !URL_RE.test(value))
            return 'Enter a full link starting with http:// or https://';
        if (field.type === 'number') {
            const n = Number(value);
            if (!Number.isInteger(n))
                return `${label} must be a whole number.`;
            if (field.min !== '' && n < Number(field.min))
                return `${label} must be at least ${field.min}.`;
            if (field.max !== '' && n > Number(field.max))
                return `${label} must be at most ${field.max}.`;
        }
    }
    const key = field.dataset.pattern;
    if (key && PATTERNS[key] && !PATTERNS[key].regex.test(value))
        return PATTERNS[key].message;
    const match = field.dataset.match;
    if (match) {
        const other = document.querySelector(match);
        if (other && other.value !== field.value)
            return 'Passwords do not match.';
    }
    return '';
}
// The <p> that shows this field's error (created when missing).
function errorEl(field) {
    const id = `${field.id || field.name}-error`;
    let el = document.getElementById(id);
    if (!el) {
        el = document.createElement('p');
        el.id = id;
        el.className = 'field-error';
        el.setAttribute('aria-live', 'polite');
        const group = field.closest('.form-input-group') || field.parentElement;
        group.appendChild(el);
        const described = field.getAttribute('aria-describedby');
        field.setAttribute('aria-describedby', described ? `${described} ${id}` : id);
    }
    return el;
}
// Show or clear a field's error; returns true when it is valid.
function showResult(field, message) {
    const el = errorEl(field);
    el.textContent = message;
    el.hidden = message === '';
    field.classList.toggle('is-invalid', message !== '');
    field.classList.toggle('is-valid', message === '' && field.value.trim() !== '');
    field.setAttribute('aria-invalid', message ? 'true' : 'false');
    return message === '';
}
// The fields of a form that need checking (not hidden, buttons, boxes, or disabled).
function fieldsOf(form) {
    return Array.from(form.querySelectorAll('input, textarea, select'))
        .filter(f => !['hidden', 'submit', 'button', 'checkbox', 'radio'].includes(f.type) && !f.disabled);
}
/** Validate every field; focus + scroll to the first problem. */
function validateForm(form, scope) {
    let first = null;
    fieldsOf(form)
        .filter(f => !scope || scope.contains(f))
        .forEach(f => { if (!showResult(f, checkField(f)) && !first)
        first = f; });
    if (first) {
        first.focus();
        first.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }
    return first === null;
}
/* ---------- Character counters ---------- */
function attachCounter(field) {
    const max = Number(field.getAttribute('maxlength') || 0);
    const min = Number(field.getAttribute('minlength') || 0);
    const out = document.createElement('div');
    out.className = 'char-counter';
    out.setAttribute('aria-hidden', 'true');
    field.insertAdjacentElement('afterend', out);
    const update = () => {
        const n = field.value.length;
        let text = max ? `${n.toLocaleString()} / ${max.toLocaleString()}` : `${n.toLocaleString()} characters`;
        if (min && n < min)
            text += ` · ${min - n} more needed`;
        out.textContent = text;
        out.classList.toggle('is-warn', (!!max && n > max * 0.9) || (!!min && n > 0 && n < min));
    };
    field.addEventListener('input', update);
    update();
}
/* ---------- Password strength meter ---------- */
function strengthScore(pw) {
    let s = 0;
    if (pw.length >= 8)
        s++;
    if (pw.length >= 12)
        s++;
    if (/[a-z]/.test(pw) && /[A-Z]/.test(pw))
        s++;
    if (/\d/.test(pw))
        s++;
    if (/[^A-Za-z0-9]/.test(pw))
        s++;
    return Math.min(s, 4);
}
// Add the password strength bar under a password field.
function attachStrength(field) {
    const labels = ['Too weak', 'Weak', 'Fair', 'Strong', 'Excellent'];
    const wrap = document.createElement('div');
    wrap.className = 'strength';
    wrap.innerHTML = '<div class="strength-bar"><span></span></div><small class="strength-label"></small>';
    (field.closest('.form-input-group') || field.parentElement).appendChild(wrap);
    const bar = wrap.querySelector('span');
    const txt = wrap.querySelector('small');
    field.addEventListener('input', () => {
        const score = field.value ? strengthScore(field.value) : -1;
        bar.style.width = score < 0 ? '0' : `${(score + 1) * 20}%`;
        bar.dataset.score = String(score);
        txt.textContent = score < 0 ? '' : `Strength: ${labels[score]}`;
    });
}
/* ---------- Show / hide password ---------- */
function attachReveal(field) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'pw-toggle';
    btn.textContent = 'Show';
    btn.setAttribute('aria-label', 'Show password');
    btn.setAttribute('aria-pressed', 'false');
    const holder = document.createElement('div');
    holder.className = 'pw-wrap';
    field.parentNode.insertBefore(holder, field);
    holder.appendChild(field);
    holder.appendChild(btn);
    btn.addEventListener('click', () => {
        const show = field.type === 'password';
        field.type = show ? 'text' : 'password';
        btn.textContent = show ? 'Hide' : 'Show';
        btn.setAttribute('aria-pressed', String(show));
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        field.focus();
    });
}
/* Some phone browsers try HTTPS first for any registry-style hostname and
 * silently drop a plain-HTTP form POST when the HTTPS attempt fails (GETs
/* ---------- Wiring ---------- */
function initForm(form) {
    fieldsOf(form).forEach((f) => {
        f.addEventListener('blur', () => showResult(f, checkField(f)));
        f.addEventListener('input', () => { if (f.classList.contains('is-invalid')) showResult(f, checkField(f)); });
        f.addEventListener('change', () => showResult(f, checkField(f)));
    });
    form.addEventListener('submit', (ev) => {
        const submitter = ev.submitter;
        if (submitter && submitter.hasAttribute('formnovalidate')) return; // e.g. "Save as draft"
        if (!validateForm(form)) {
            ev.preventDefault();
            ev.stopImmediatePropagation();
            if (typeof window.mockToast === 'function') window.mockToast('Please fix the highlighted fields.', 'error');
        }
    });
}
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-validate]').forEach(initForm);
    document.querySelectorAll('[data-counter]').forEach(attachCounter);
    document.querySelectorAll('input[data-strength]').forEach(attachStrength);
    document.querySelectorAll('input[type="password"]').forEach(attachReveal);
});
// Public API for other scripts (the article wizard validates one step at a time).
window.MockValidator = { validateForm, checkField, showResult, PATTERNS };
