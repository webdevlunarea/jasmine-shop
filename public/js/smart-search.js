(() => {
    'use strict';

    const forms = [...document.querySelectorAll('[data-smart-search]')];
    if (!forms.length) return;

    const historyKey = 'lunarea.search.history.v1';
    let activeForm = null;
    const panels = [];
    const panelByForm = new WeakMap();
    const getPanel = form => panelByForm.get(form) || null;

    const readHistory = () => {
        try {
            const value = JSON.parse(localStorage.getItem(historyKey) || '[]');
            return Array.isArray(value) ? value.filter(item => typeof item === 'string').slice(0, 6) : [];
        } catch (_) { return []; }
    };
    const saveHistory = (query) => {
        const clean = String(query || '').trim().replace(/\s+/g, ' ').slice(0, 100);
        if (clean.length < 2) return;
        try {
            const next = [clean, ...readHistory().filter(item => item.toLocaleLowerCase('id') !== clean.toLocaleLowerCase('id'))].slice(0, 6);
            localStorage.setItem(historyKey, JSON.stringify(next));
        } catch (_) { /* Search remains usable when browser storage is unavailable. */ }
    };
    const searchUrl = query => `/find/${encodeURIComponent(query.trim().replace(/\s+/g, '-'))}`;

    const positionPanel = (form) => {
        const panel = getPanel(form);
        const box = form.getBoundingClientRect();
        const mobileHeader = document.querySelector('.buyer-mobile-header');
        const desktopHeader = document.querySelector('.buyer-desktop-header');
        const header = window.matchMedia('(max-width: 991.98px)').matches ? mobileHeader : desktopHeader;
        if (!panel || !header) return;
        const top = Math.max(8, Math.ceil(header.getBoundingClientRect().bottom + 8));
        const width = Math.min(Math.max(box.width, 300), window.innerWidth - 24);
        const left = Math.max(12, Math.min(Math.round(box.left), window.innerWidth - width - 12));
        panel.style.top = `${top}px`;
        panel.style.left = `${left}px`;
        panel.style.width = `${width}px`;
    };

    const hidePanel = (form) => {
        const input = form.querySelector('.search-input');
        const panel = getPanel(form);
        if (!input || !panel) return;
        panel.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        if (activeForm === form) activeForm = null;
    };

    const showPanel = (form) => {
        const input = form.querySelector('.search-input');
        const panel = getPanel(form);
        if (!input || !panel) return;
        forms.forEach(other => { if (other !== form) hidePanel(other); });
        activeForm = form;
        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        positionPanel(form);
    };

    const renderHistory = (form) => {
        const input = form.querySelector('.search-input');
        const panel = getPanel(form);
        const list = panel?.querySelector('.smart-search-list');
        const heading = panel?.querySelector('[data-smart-search-heading]');
        const clear = panel?.querySelector('[data-search-history-clear]');
        if (!input || !panel || !list || !heading || !clear) return;
        const history = readHistory();
        list.replaceChildren();
        heading.textContent = history.length ? 'Pencarian terakhir' : 'Mulai cari produk';
        clear.hidden = history.length === 0;

        if (!history.length) {
            const hint = document.createElement('p');
            hint.className = 'smart-search-empty';
            hint.textContent = 'Ketik nama produk, kategori, atau kata kunci.';
            list.append(hint);
        } else {
            history.forEach((query, index) => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'smart-search-history-item';
                item.id = `${list.id}-option-${index}`;
                item.setAttribute('role', 'option');
                item.setAttribute('aria-selected', 'false');
                item.dataset.searchQuery = query;
                item.innerHTML = '<span class="material-icons" aria-hidden="true">history</span>';
                const text = document.createElement('span');
                text.textContent = query;
                item.append(text);
                list.append(item);
            });
        }
        showPanel(form);
    };

    const renderSuggestions = (form, items, query) => {
        const panel = getPanel(form);
        const list = panel?.querySelector('.smart-search-list');
        const heading = panel?.querySelector('[data-smart-search-heading]');
        const clear = panel?.querySelector('[data-search-history-clear]');
        if (!panel || !list || !heading || !clear) return;
        list.replaceChildren();
        heading.textContent = 'Hasil yang cocok';
        clear.hidden = true;

        if (!items.length) {
            const empty = document.createElement('p');
            empty.className = 'smart-search-empty';
            empty.textContent = `Belum ada saran untuk “${query}”. Tekan Enter untuk melihat semua hasil.`;
            list.append(empty);
        } else {
            items.forEach((item, index) => {
                const link = document.createElement('a');
                link.className = 'smart-search-item';
                link.href = item.url;
                link.id = `${list.id}-option-${index}`;
                link.setAttribute('role', 'option');
                link.setAttribute('aria-selected', 'false');
                link.dataset.searchQuery = query;

                const image = document.createElement('img');
                image.src = item.image;
                image.alt = '';
                image.loading = 'lazy';
                image.width = 48;
                image.height = 48;
                image.onerror = () => { image.hidden = true; };

                const details = document.createElement('span');
                details.className = 'smart-search-item__details';
                const name = document.createElement('span');
                name.className = 'smart-search-item__name';
                name.textContent = item.name;
                const meta = document.createElement('span');
                meta.className = 'smart-search-item__meta';
                const category = document.createElement('span');
                category.textContent = item.category || 'Furniture';
                const price = document.createElement('strong');
                price.textContent = item.price;
                meta.append(category, price);
                details.append(name, meta);
                link.append(image, details);
                list.append(link);
            });
        }
        showPanel(form);
    };

    forms.forEach(form => {
        const input = form.querySelector('.search-input');
        const panel = form.querySelector('.smart-search-results');
        if (!input || !panel) return;
        panelByForm.set(form, panel);
        // Render the dropdown in the page overlay layer, aligned to the search field but below the nav.
        document.body.append(panel);
        panels.push(panel);
        let timer = 0;
        let controller = null;
        let activeIndex = -1;

        input.addEventListener('input', () => {
            window.clearTimeout(timer);
            activeIndex = -1;
            const query = input.value.trim().replace(/\s+/g, ' ');
            if (query.length < 2) {
                controller?.abort();
                if (document.activeElement === input) renderHistory(form);
                else hidePanel(form);
                return;
            }

            timer = window.setTimeout(async () => {
                controller?.abort();
                controller = new AbortController();
                try {
                    const url = new URL(form.dataset.suggestUrl, window.location.origin);
                    url.searchParams.set('q', query);
                    const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin', signal: controller.signal });
                    if (!response.ok) throw new Error('Search suggestions unavailable');
                    const data = await response.json();
                    if (input.value.trim().replace(/\s+/g, ' ') !== query) return;
                    renderSuggestions(form, Array.isArray(data.items) ? data.items : [], query);
                } catch (error) {
                    if (error.name !== 'AbortError') hidePanel(form);
                }
            }, 180);
        });

        input.addEventListener('focus', () => {
            if (input.value.trim().length < 2) renderHistory(form);
            else input.dispatchEvent(new Event('input', { bubbles: true }));
        });

        input.addEventListener('keydown', (event) => {
            if (panel.hidden) return;
            const options = [...panel.querySelectorAll('[role="option"]')];
            if (event.key === 'Escape') {
                hidePanel(form);
                return;
            }
            if ((event.key === 'ArrowDown' || event.key === 'ArrowUp') && options.length) {
                event.preventDefault();
                activeIndex = event.key === 'ArrowDown'
                    ? (activeIndex + 1) % options.length
                    : (activeIndex <= 0 ? options.length - 1 : activeIndex - 1);
                options.forEach((option, index) => {
                    const selected = index === activeIndex;
                    option.setAttribute('aria-selected', String(selected));
                    if (selected) {
                        input.setAttribute('aria-activedescendant', option.id);
                        option.scrollIntoView({ block: 'nearest' });
                    }
                });
            } else if (event.key === 'Enter' && options[activeIndex]) {
                event.preventDefault();
                options[activeIndex].click();
            }
        });

        form.addEventListener('submit', () => {
            saveHistory(input.value);
            hidePanel(form);
        });
        panel.addEventListener('click', event => {
            const clear = event.target.closest('[data-search-history-clear]');
            if (clear) {
                try { localStorage.removeItem(historyKey); } catch (_) { /* no-op */ }
                renderHistory(form);
                input.focus({ preventScroll: true });
                return;
            }
            const selected = event.target.closest('[data-search-query]');
            if (!selected) return;
            saveHistory(selected.dataset.searchQuery);
            if (selected.matches('button')) window.location.assign(searchUrl(selected.dataset.searchQuery));
            else hidePanel(form);
        });
    });

    document.addEventListener('click', event => {
        if (forms.some(form => form.contains(event.target)) || panels.some(panel => panel.contains(event.target))) return;
        forms.forEach(hidePanel);
    });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') forms.forEach(hidePanel); });
    const reposition = () => { if (activeForm) positionPanel(activeForm); };
    window.addEventListener('resize', reposition, { passive: true });
    window.addEventListener('scroll', reposition, { passive: true });
})();
