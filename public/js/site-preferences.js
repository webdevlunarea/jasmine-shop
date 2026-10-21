(function () {
    'use strict';

    var VERSION = '2';
    var STORAGE_KEY = 'lunarea-cookie-choice-v' + VERSION;
    var COOKIE_KEY = 'lunarea_cookie_choice';
    var banner = document.getElementById('cookie-consent');
    if (!banner) return;

    var settings = document.getElementById('cookie-settings');
    var toggle = document.getElementById('cookie-optional-toggle');
    var settingsButton = document.getElementById('cookie-open-settings');

    function readCookie() {
        var prefix = COOKIE_KEY + '=';
        var entry = document.cookie.split('; ').find(function (part) { return part.indexOf(prefix) === 0; });
        if (!entry) return '';
        try { return decodeURIComponent(entry.substring(prefix.length)); } catch (_) { return ''; }
    }

    function readChoice() {
        try {
            var value = window.localStorage.getItem(STORAGE_KEY);
            if (value === 'optional' || value === 'required') return value;
        } catch (_) { /* Cookie fallback below. */ }
        var cookie = readCookie();
        return cookie === 'optional' || cookie === 'required' ? cookie : '';
    }

    function storeChoice(choice) {
        var maxAge = 31536000;
        try { window.localStorage.setItem(STORAGE_KEY, choice); } catch (_) { /* Cookie fallback below. */ }
        document.cookie = COOKIE_KEY + '=' + encodeURIComponent(choice) + '; Max-Age=' + maxAge + '; Path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
    }

    function removeOptionalCookies() {
        var names = document.cookie.split(';').map(function (item) { return item.trim().split('=')[0]; });
        names.forEach(function (name) {
            if (!/^(_ga($|_)|_gid$|_gat($|_)|_gcl_)/.test(name)) return;
            document.cookie = name + '=; Max-Age=0; Path=/; SameSite=Lax; Secure';
            document.cookie = name + '=; Max-Age=0; Path=/; Domain=.lunareafurniture.com; SameSite=Lax; Secure';
        });
    }

    function updateOptionalConsent(granted) {
        window['ga-disable-G-K134RFDV6Z'] = !granted;
        if (typeof window.gtag === 'function') {
            window.gtag('consent', 'update', {
                analytics_storage: granted ? 'granted' : 'denied',
                ad_storage: 'denied',
                ad_user_data: 'denied',
                ad_personalization: 'denied'
            });
        }
    }

    function closeBanner() {
        banner.hidden = true;
        settings.hidden = true;
        settingsButton.setAttribute('aria-expanded', 'false');
    }

    function revealWhenFooterIsViewed() {
        var footer = document.querySelector('.buyer-footer');
        if (!footer) return;

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                if (!entries.some(function (entry) { return entry.isIntersecting; })) return;
                banner.hidden = false;
                observer.disconnect();
            }, { threshold: 0.01 });
            observer.observe(footer);
            return;
        }

        function checkFooterVisibility() {
            var rect = footer.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                banner.hidden = false;
                window.removeEventListener('scroll', checkFooterVisibility);
                window.removeEventListener('resize', checkFooterVisibility);
            }
        }
        window.addEventListener('scroll', checkFooterVisibility, { passive: true });
        window.addEventListener('resize', checkFooterVisibility);
        checkFooterVisibility();
    }

    function save(choice) {
        storeChoice(choice);
        if (choice === 'optional') {
            updateOptionalConsent(true);
            if (typeof window.lunareaLoadOptionalTags === 'function') window.lunareaLoadOptionalTags();
        } else {
            updateOptionalConsent(false);
            removeOptionalCookies();
        }
        closeBanner();
    }

    // Delegate clicks from document so footer controls still work when layouts
    // render or replace their footer after this script has initialized.
    document.addEventListener('click', function (event) {
        var target = event.target instanceof Element ? event.target : null;
        if (!target) return;

        var preferenceControl = target.closest('[data-cookie-settings-open]');
        if (preferenceControl) {
            event.preventDefault();
            banner.hidden = false;
            settings.hidden = false;
            settingsButton.setAttribute('aria-expanded', 'true');
            toggle.checked = readChoice() === 'optional';
            toggle.focus();
            return;
        }

        if (target.closest('#cookie-only-required')) {
            save('required');
        } else if (target.closest('#cookie-allow-optional')) {
            save('optional');
        } else if (target.closest('#cookie-open-settings')) {
            settings.hidden = !settings.hidden;
            settingsButton.setAttribute('aria-expanded', String(!settings.hidden));
            toggle.checked = readChoice() === 'optional';
            if (!settings.hidden) toggle.focus();
        } else if (target.closest('#cookie-save-settings')) {
            save(toggle.checked ? 'optional' : 'required');
        }
    });

    var choice = readChoice();
    if (choice === 'optional' && typeof window.lunareaLoadOptionalTags === 'function') {
        window.lunareaLoadOptionalTags();
    } else if (!choice) {
        revealWhenFooterIsViewed();
    }
}());
