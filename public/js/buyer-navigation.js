(() => {
    'use strict';
    const header = document.querySelector('.buyer-mobile-header');
    if (!header) return; // The admin layout uses its own navigation.

    const desktopHeader = document.querySelector('.buyer-desktop-header');
    const promoBanners = document.querySelectorAll('.buyer-promo-banner');
    const mobile = window.matchMedia('(max-width: 599.98px)');
    const toggle = document.getElementById('buyer-account-toggle');
    const menu = document.getElementById('buyer-account-menu');
    const root = document.documentElement;
    const searchToggle = document.getElementById('buyer-search-toggle');
    const searchPanel = document.getElementById('buyer-mobile-search');
    const searchInput = searchPanel?.querySelector('.search-input');
    let returnFocus = null;
    let lastScrollY = window.scrollY;
    let downwardScroll = 0;

    function closeSearch(restoreFocus = false) {
        if (!searchPanel || searchPanel.hidden) return;
        if (searchPanel.contains(document.activeElement)) document.activeElement.blur();
        searchPanel.hidden = true;
        searchToggle?.setAttribute('aria-expanded', 'false');
        searchToggle?.setAttribute('aria-label', 'Buka pencarian');
        syncHeader();
        downwardScroll = 0;
        lastScrollY = window.scrollY;
        if (restoreFocus) searchToggle?.focus({ preventScroll: true });
    }

    function openSearch() {
        if (!searchPanel || !mobile.matches) return;
        closeMenu(false);
        window.dispatchEvent(new Event('buyer-navigation-open'));
        searchPanel.hidden = false;
        searchToggle?.setAttribute('aria-expanded', 'true');
        searchToggle?.setAttribute('aria-label', 'Tutup pencarian');
        syncHeader();
        downwardScroll = 0;
        lastScrollY = window.scrollY;
        searchInput?.focus({ preventScroll: true });
    }

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
        closeSearch();
        returnFocus = document.activeElement;
        window.dispatchEvent(new Event('buyer-navigation-open'));
        menu.hidden = false;
        root.classList.add('buyer-menu-open');
        toggle?.setAttribute('aria-expanded', 'true');
        menu.querySelector('[data-buyer-menu-close]')?.focus({ preventScroll: true });
    }

    toggle?.addEventListener('click', () => menu?.hidden ? openMenu() : closeMenu());
    searchToggle?.addEventListener('click', () => searchPanel?.hidden ? openSearch() : closeSearch(true));
    document.addEventListener('click', event => {
        if (searchPanel && !searchPanel.hidden && !header.contains(event.target)) closeSearch();
    });
    window.addEventListener('scroll', () => {
        const current = Math.max(0, window.scrollY);
        const delta = current - lastScrollY;
        lastScrollY = current;
        if (!mobile.matches || !searchPanel || searchPanel.hidden) return;
        downwardScroll = delta > 0 ? downwardScroll + delta : 0;
        if (downwardScroll > 24) closeSearch();
    }, { passive: true });
    menu?.addEventListener('click', event => {
        if (event.target === menu || event.target.closest('[data-buyer-menu-close]')) closeMenu();
        else if (event.target.closest('a[href]')) closeMenu(false);
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && searchPanel && !searchPanel.hidden) {
            event.preventDefault();
            closeSearch(true);
        }
        if (!menu || menu.hidden) return;
        if (event.key === 'Escape') { event.preventDefault(); closeMenu(); return; }
        if (event.key !== 'Tab') return;
        const controls = Array.from(menu.querySelectorAll('button:not([disabled]), a[href]'));
        const first = controls[0];
        const last = controls[controls.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    });
    mobile.addEventListener('change', () => { closeMenu(false); closeSearch(); syncHeader(); });
    window.addEventListener('pageshow', () => { closeMenu(false); closeSearch(); syncHeader(); });
    window.addEventListener('resize', syncHeader, { passive: true });
    if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(syncHeader);
        observer.observe(header);
        if (desktopHeader) observer.observe(desktopHeader);
        promoBanners.forEach(banner => observer.observe(banner));
    }
    syncHeader();


    // Preserve logout/storage cleanup until the customer explicitly confirms.
    const logoutDialog = document.createElement('dialog');
    logoutDialog.className = 'buyer-logout-dialog';
    logoutDialog.setAttribute('aria-labelledby', 'buyer-logout-title');
    logoutDialog.setAttribute('aria-describedby', 'buyer-logout-description');
    logoutDialog.innerHTML = `
        <h2 id="buyer-logout-title">Yakin ingin keluar dari akun?</h2>
        <p id="buyer-logout-description">Anda perlu masuk kembali untuk mengakses akun Anda.</p>
        <div class="buyer-logout-actions">
            <button type="button" data-logout-cancel autofocus>Batal</button>
            <button type="button" data-logout-confirm>Keluar</button>
        </div>`;
    document.body.append(logoutDialog);
    let logoutUrl = null;
    let logoutTrigger = null;
    document.addEventListener('click', event => {
        const link = event.target.closest('a[data-buyer-logout]');
        if (!link) return;
        event.preventDefault();
        if (logoutDialog.open) return;
        const target = new URL(link.href, location.href);
        if (target.origin !== location.origin) return;
        logoutTrigger = link;
        logoutUrl = target.href;
        closeMenu(false);
        closeSearch();
        logoutDialog.showModal();
    });
    logoutDialog.querySelector('[data-logout-cancel]').addEventListener('click', () => logoutDialog.close());
    logoutDialog.addEventListener('click', event => {
        if (event.target !== logoutDialog) return;
        const bounds = logoutDialog.getBoundingClientRect();
        if (event.clientX < bounds.left || event.clientX > bounds.right ||
            event.clientY < bounds.top || event.clientY > bounds.bottom) logoutDialog.close();
    });
    logoutDialog.addEventListener('close', () => {
        logoutUrl = null;
        const focusTarget = logoutTrigger?.getClientRects().length ? logoutTrigger : toggle;
        focusTarget?.focus({ preventScroll: true });
        logoutTrigger = null;
    });
    logoutDialog.querySelector('[data-logout-confirm]').addEventListener('click', event => {
        if (!logoutUrl || event.currentTarget.disabled) return;
        event.currentTarget.disabled = true;
        window.location.assign(logoutUrl);
    });
    window.addEventListener('pageshow', () => {
        if (logoutDialog.open) logoutDialog.close();
        logoutDialog.querySelector('[data-logout-confirm]').disabled = false;
        logoutUrl = null;
    });
})();
