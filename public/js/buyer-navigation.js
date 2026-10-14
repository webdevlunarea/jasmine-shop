(() => {
    'use strict';
    const header = document.querySelector('.buyer-mobile-header');
    if (!header) return; // The admin layout uses its own navigation.

    const desktopHeader = document.querySelector('.buyer-desktop-header');
    const promoBanners = document.querySelectorAll('.buyer-promo-banner');
    const mobile = window.matchMedia('(max-width: 991.98px)');
    const toggle = document.getElementById('buyer-account-toggle');
    const menu = document.getElementById('buyer-account-menu');
    const root = document.documentElement;
    let returnFocus = null;

    function syncAccountTabs() {
        const tabs = document.querySelector('.account-mobile-tabs');
        const active = tabs?.querySelector('[aria-current="page"], .active');
        if (!tabs || !active || !mobile.matches) return;
        const container = tabs.getBoundingClientRect();
        const item = active.getBoundingClientRect();
        tabs.scrollLeft += item.left - container.left - (tabs.clientWidth - item.width) / 2;
    }

    function syncHeader() {
        let promoBottom = 0;
        promoBanners.forEach(banner => {
            if (getComputedStyle(banner).display !== 'none') {
                promoBottom = Math.max(promoBottom, banner.getBoundingClientRect().bottom);
            }
        });
        root.style.setProperty('--buyer-promo-height', `${Math.ceil(promoBottom)}px`);
        const visibleHeader = mobile.matches ? header : desktopHeader;
        if (!visibleHeader) return;
        const bottom = Math.ceil(visibleHeader.getBoundingClientRect().bottom);
        root.style.setProperty('--buyer-header-bottom', `${bottom + 12}px`);
        syncAccountTabs();
    }

    function closeMenu(restoreFocus = true) {
        if (!menu || menu.hidden) return;
        menu.hidden = true;
        root.classList.remove('buyer-menu-open');
        toggle?.setAttribute('aria-expanded', 'false');
        if (restoreFocus && returnFocus?.isConnected) returnFocus.focus({ preventScroll: true });
    }

    function openMenu() {
        if (!menu || !mobile.matches) return;
        returnFocus = document.activeElement;
        window.dispatchEvent(new Event('buyer-navigation-open'));
        menu.hidden = false;
        root.classList.add('buyer-menu-open');
        toggle?.setAttribute('aria-expanded', 'true');
        menu.querySelector('[data-buyer-menu-close]')?.focus({ preventScroll: true });
    }

    toggle?.addEventListener('click', () => menu?.hidden ? openMenu() : closeMenu());
    menu?.addEventListener('click', event => {
        if (event.target === menu || event.target.closest('[data-buyer-menu-close]')) closeMenu();
        else if (event.target.closest('a[href]')) closeMenu(false);
    });
    document.addEventListener('keydown', event => {
        if (!menu || menu.hidden) return;
        if (event.key === 'Escape') { event.preventDefault(); closeMenu(); return; }
        if (event.key !== 'Tab') return;
        const controls = Array.from(menu.querySelectorAll('button:not([disabled]), a[href]'));
        const first = controls[0];
        const last = controls[controls.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    });
    mobile.addEventListener('change', () => { closeMenu(false); syncHeader(); });
    window.addEventListener('pageshow', () => { closeMenu(false); syncHeader(); });
    window.addEventListener('resize', syncHeader, { passive: true });
    if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(syncHeader);
        observer.observe(header);
        if (desktopHeader) observer.observe(desktopHeader);
        promoBanners.forEach(banner => observer.observe(banner));
    }
    syncHeader();

})();
