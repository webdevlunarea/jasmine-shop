(() => {
    'use strict';

    const forms = [...document.querySelectorAll('[data-smart-search]')];
    if (!forms.length) return;

    const close = (form) => {
        const input = form.querySelector('.search-input');
        const panel = form.querySelector('.smart-search-results');
        if (!input || !panel) return;
        panel.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
    };

    const render = (form, items, query) => {
        const input = form.querySelector('.search-input');
        const panel = form.querySelector('.smart-search-results');
        if (!input || !panel) return;
        panel.replaceChildren();

        if (!items.length) {
            const empty = document.createElement('p');
            empty.className = 'smart-search-empty';
            empty.textContent = `Belum ada saran untuk “${query}”. Tekan Enter untuk melihat hasil pencarian.`;
            panel.append(empty);
        } else {
            items.forEach((item, index) => {
                const link = document.createElement('a');
                link.className = 'smart-search-item';
                link.href = item.url;
                link.id = `${panel.id}-option-${index}`;
                link.setAttribute('role', 'option');
                link.setAttribute('aria-selected', 'false');

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
                panel.append(link);
            });
        }

        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    };

    forms.forEach((form, formIndex) => {
        const input = form.querySelector('.search-input');
        const panel = form.querySelector('.smart-search-results');
        if (!input || !panel) return;

        let timer = 0;
        let controller = null;
        let activeIndex = -1;

        input.addEventListener('input', () => {
            window.clearTimeout(timer);
            activeIndex = -1;
            const query = input.value.trim().replace(/\s+/g, ' ');
            if (query.length < 2) {
                controller?.abort();
                close(form);
                return;
            }

            timer = window.setTimeout(async () => {
                controller?.abort();
                controller = new AbortController();
                try {
                    const url = new URL(form.dataset.suggestUrl, window.location.origin);
                    url.searchParams.set('q', query);
                    const response = await fetch(url, {
                        headers: { Accept: 'application/json' },
                        credentials: 'same-origin',
                        signal: controller.signal,
                    });
                    if (!response.ok) throw new Error('Search suggestions unavailable');
                    const data = await response.json();
                    if (input.value.trim().replace(/\s+/g, ' ') !== query) return;
                    render(form, Array.isArray(data.items) ? data.items : [], query);
                } catch (error) {
                    if (error.name !== 'AbortError') close(form);
                }
            }, 180);
        });

        input.addEventListener('keydown', (event) => {
            if (panel.hidden) return;
            const options = [...panel.querySelectorAll('[role="option"]')];
            if (event.key === 'Escape') {
                close(form);
            } else if (event.key === 'ArrowDown' && options.length) {
                event.preventDefault();
                activeIndex = (activeIndex + 1) % options.length;
            } else if (event.key === 'ArrowUp' && options.length) {
                event.preventDefault();
                activeIndex = activeIndex <= 0 ? options.length - 1 : activeIndex - 1;
            } else if (event.key === 'Enter' && options[activeIndex]) {
                event.preventDefault();
                window.location.assign(options[activeIndex].href);
                return;
            } else {
                return;
            }

            options.forEach((option, index) => {
                const selected = index === activeIndex;
                option.setAttribute('aria-selected', String(selected));
                if (selected) {
                    input.setAttribute('aria-activedescendant', option.id);
                    option.scrollIntoView({ block: 'nearest' });
                }
            });
        });

        input.addEventListener('focus', () => {
            if (input.value.trim().length >= 2) input.dispatchEvent(new Event('input', { bubbles: true }));
        });

        form.addEventListener('submit', () => close(form));
        form.addEventListener('click', (event) => event.stopPropagation());
        // Keep independent desktop/mobile input state while using one delegated outside-close handler.
        form.dataset.smartSearchInstance = String(formIndex);
    });

    document.addEventListener('click', () => forms.forEach(close));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') forms.forEach(close);
    });
})();
