(() => {
    'use strict';
    const toggle = document.getElementById('admin-menu-toggle');
    const sidebar = document.getElementById('admin-navigation');
    const trigger = document.querySelector('[data-admin-menu-open]');
    if (!toggle || !sidebar || !trigger) return;
    const desktop = window.matchMedia('(min-width: 993px)');
    const closeButton = sidebar.querySelector('[data-admin-menu-close]');
    const sidebarToggle = document.querySelector('[data-admin-sidebar-toggle]');
    const sidebarPreferenceKey = 'lunarea-admin-sidebar-collapsed';
    function setSidebarCollapsed(collapsed, persist = true) {
        const isCollapsed = Boolean(collapsed) && desktop.matches;
        document.body.classList.toggle('admin-sidebar-collapsed', isCollapsed);
        if (sidebarToggle) {
            sidebarToggle.checked = isCollapsed;
            sidebarToggle.setAttribute('aria-label', isCollapsed ? 'Perlebar menu admin' : 'Minimalkan menu admin');
            sidebarToggle.title = isCollapsed ? 'Perlebar menu admin' : 'Minimalkan menu admin';
            const sidebarToggleLabel = document.querySelector('label[for="admin-sidebar-collapse-toggle"]');
            if (sidebarToggleLabel) sidebarToggleLabel.title = sidebarToggle.title;
            const icon = sidebarToggleLabel?.querySelector('.material-icons');
            if (icon) icon.textContent = isCollapsed ? 'chevron_right' : 'chevron_left';
        }
        if (persist) {
            try { localStorage.setItem(sidebarPreferenceKey, isCollapsed ? '1' : '0'); } catch (_) { /* Storage may be unavailable. */ }
        }
    }
    function getSidebarPreference() {
        try { return localStorage.getItem(sidebarPreferenceKey) === '1'; } catch (_) { return false; }
    }
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
    sidebarToggle?.addEventListener('change', () => setSidebarCollapsed(sidebarToggle.checked));
    desktop.addEventListener('change', event => {
        setMenu(false, false);
        setSidebarCollapsed(event.matches && getSidebarPreference(), false);
    });
    setSidebarCollapsed(getSidebarPreference(), false);
    setMenu(false, false);

    const noticeToggle = document.querySelector('[data-admin-notice-toggle]');
    const noticePanel = document.querySelector('[data-admin-notice-panel]');
    if (noticeToggle && noticePanel) {
        const closeNotice = (restoreFocus = false) => {
            noticePanel.hidden = true;
            noticeToggle.setAttribute('aria-expanded', 'false');
            if (restoreFocus) noticeToggle.focus();
        };
        noticeToggle.addEventListener('click', () => {
            const open = noticeToggle.getAttribute('aria-expanded') !== 'true';
            noticePanel.hidden = !open;
            noticeToggle.setAttribute('aria-expanded', String(open));
        });
        document.addEventListener('click', event => {
            if (!event.target.closest('.admin-account-notice')) closeNotice();
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && noticeToggle.getAttribute('aria-expanded') === 'true') closeNotice(true);
        });
    }

    const logoutForm = document.querySelector('form[data-admin-logout-form]');
    if (logoutForm) {
        const dialog = document.createElement('dialog');
        dialog.className = 'admin-logout-dialog';
        dialog.setAttribute('aria-labelledby', 'admin-logout-title');
        dialog.setAttribute('aria-describedby', 'admin-logout-description');
        dialog.innerHTML = `
            <span class="admin-logout-dialog-icon" aria-hidden="true"><i class="material-icons">logout</i></span>
            <h2 id="admin-logout-title">Keluar dari workspace admin?</h2>
            <p id="admin-logout-description">Anda perlu masuk kembali untuk membuka panel admin.</p>
            <div class="admin-logout-dialog-actions">
                <button type="button" data-admin-logout-cancel>Batal</button>
                <button type="button" data-admin-logout-confirm>Keluar</button>
            </div>`;
        document.body.append(dialog);
        const cancelButton = dialog.querySelector('[data-admin-logout-cancel]');
        const confirmButton = dialog.querySelector('[data-admin-logout-confirm]');
        let confirmed = false;
        logoutForm.addEventListener('submit', event => {
            if (confirmed) return;
            event.preventDefault();
            setMenu(false, false);
            if (!dialog.open) dialog.showModal();
        });
        cancelButton.addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', event => {
            if (event.target === dialog) dialog.close();
        });
        confirmButton.addEventListener('click', () => {
            if (confirmed) return;
            confirmed = true;
            confirmButton.disabled = true;
            logoutForm.requestSubmit();
        });
        window.addEventListener('pageshow', () => {
            confirmed = false;
            confirmButton.disabled = false;
            if (dialog.open) dialog.close();
        });
    }
})();
