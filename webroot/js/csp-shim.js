// CSP shim: re-apply inline styles via JS so the page still renders under strict
// CSP `style-src 'self'`. The browser refuses to apply `style="…"` attributes from
// HTML parsing, but `el.style.cssText = …` set from JS is not subject to style-src
// (it's DOM mutation, not a parsed style source). We do this in two passes:
//
// 1. `[data-style]` — explicit conversion in the demo's own templates. Strictly
//    preferred over a broad sweep.
// 2. `[style]` — fallback for inline styles emitted by frameworks (CakePHP's
//    FormHelper still emits `style="display:none;"` on CSRF wrappers and
//    `style="margin:0;"` on `Form->create()`). Applies to any `[style]` left in
//    the DOM after parsing.

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-style]').forEach(function (el) {
        el.style.cssText = el.dataset.style;
    });

    document.querySelectorAll('[style]').forEach(function (el) {
        var s = el.getAttribute('style');
        if (s) {
            el.style.cssText = s;
        }
    });
});

// Flash-message dismiss (CSP-safe replacement for onclick="this.classList.add('hidden')").
document.addEventListener('click', function (event) {
    var target = event.target.closest('[data-flash-dismiss]');
    if (target) {
        target.classList.add('hidden');
    }
});
