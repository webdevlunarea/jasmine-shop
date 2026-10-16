(() => {
    'use strict';
    const toggle = document.getElementById('admin-menu-toggle');
    const sidebar = document.getElementById('admin-navigation');
    const trigger = document.querySelector('[data-admin-menu-open]');
    if (!toggle || !sidebar || !trigger) return;
    const desktop = window.matchMedia('(min-width: 993px)');
    const closeButton = sidebar.querySelector('[data-admin-menu-close]');
    function setMenu(open, restoreFocus = true) {
        const drawerOpen = Boolean(open) && !desktop.matches;
        toggle.checked = drawerOpen;
        trigger.setAttribute('aria-expanded', String(drawerOpen));
        sidebar.inert = !desktop.matches && !drawerOpen;
        document.body.classList.toggle('admin-menu-open', drawerOpen);
        if (drawerOpen) closeButton?.focus();
        else if (restoreFocus && !desktop.matches) trigger.focus();
    }
    trigger.addEventListener('click', () => setMenu(!toggle.checked));
    document.querySelectorAll('[data-admin-menu-close]').forEach(button => button.addEventListener('click', () => setMenu(false)));
    sidebar.addEventListener('click', event => { if (event.target.closest('a')) setMenu(false, false); });
    document.addEventListener('keydown', event => {
        if (!toggle.checked || desktop.matches) return;
        if (event.key === 'Escape') { event.preventDefault(); setMenu(false); return; }
        if (event.key !== 'Tab') return;
        const items = Array.from(sidebar.querySelectorAll('a[href], button:not([disabled])')).filter(item => item.getClientRects().length > 0);
        if (!items.length) return;
        const first = items[0], last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });
    desktop.addEventListener('change', () => setMenu(false, false));
    setMenu(false, false);
})();
