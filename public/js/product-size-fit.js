const SVG_NS = 'http://www.w3.org/2000/svg';

function makeElement(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text) node.textContent = text;
    return node;
}

function makeSvg(tag, attributes = {}) {
    const node = document.createElementNS(SVG_NS, tag);
    Object.entries(attributes).forEach(([name, value]) => node.setAttribute(name, String(value)));
    return node;
}

export function mountSizeFit(root) {
    if (root.dataset.mounted === 'true') return;
    root.dataset.mounted = 'true';
    const width = Number(root.dataset.sizeWidth);
    const depth = Number(root.dataset.sizeDepth);
    const height = Number(root.dataset.sizeHeight);
    if (![width, depth, height].every(value => Number.isFinite(value) && value > 0 && value <= 800)) {
        root.textContent = 'Dimensi produk belum cukup lengkap untuk simulasi.';
        root.setAttribute('role', 'status');
        return;
    }

    const layout = makeElement('div', 'size-fit-layout');
    const info = makeElement('div', 'size-fit-product-info');
    info.append(
        makeElement('span', 'size-fit-product-info__label', 'Ukuran tercatat'),
        makeElement('strong', 'size-fit-product-info__value', `${width} × ${depth} × ${height} cm`),
        makeElement('span', 'size-fit-product-info__hint', 'Panjang × lebar/kedalaman × tinggi')
    );

    const form = makeElement('div', 'size-fit-room-inputs');
    form.setAttribute('role', 'group');
    form.setAttribute('aria-label', 'Ukuran ruang');
    const fields = [
        ['size-fit-room-width', 'Lebar ruang', 'Masukkan cm'],
        ['size-fit-room-depth', 'Kedalaman ruang', 'Masukkan cm'],
    ].map(([id, labelText, placeholder]) => {
        const label = makeElement('label', 'size-fit-field');
        label.htmlFor = id;
        label.append(makeElement('span', '', labelText));
        const input = makeElement('input');
        input.id = id;
        input.type = 'number';
        input.min = '10';
        input.max = '5000';
        input.step = '1';
        input.inputMode = 'decimal';
        input.placeholder = placeholder;
        input.autocomplete = 'off';
        input.setAttribute('aria-describedby', 'size-fit-unit-note');
        label.append(input, makeElement('span', 'size-fit-field__unit', 'cm'));
        form.append(label);
        return input;
    });

    const unitNote = makeElement('p', 'size-fit-unit-note', 'Ukur area lantai yang tersedia, bukan luas seluruh ruangan.');
    unitNote.id = 'size-fit-unit-note';
    const diagramWrap = makeElement('div', 'size-fit-diagram-wrap');
    const diagramHeading = makeElement('div', 'size-fit-diagram-heading');
    diagramHeading.append(makeElement('strong', '', 'Perbandingan tampak atas'));
    const legend = makeElement('div', 'size-fit-diagram-legend');
    legend.setAttribute('aria-label', 'Keterangan diagram');
    legend.append(
        makeElement('span', 'size-fit-legend-item size-fit-legend-item--room', 'Ruang'),
        makeElement('span', 'size-fit-legend-item size-fit-legend-item--furniture', 'Furnitur')
    );
    const diagram = makeSvg('svg', { viewBox: '0 0 720 250', role: 'img', 'aria-label': 'Perbandingan ukuran furnitur dengan area ruang dari tampak atas' });
    const diagramHint = makeElement('p', 'size-fit-diagram-hint', 'Masukkan ukuran ruang untuk melihat simulasi.');
    const status = makeElement('p', 'size-fit-status', 'Masukkan ukuran ruang untuk melihat perbandingan.');
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');
    diagramWrap.append(diagramHeading, legend, diagram, diagramHint);
    layout.append(info, form, unitNote, diagramWrap, status);
    root.replaceChildren(layout);

    const update = () => {
        const roomWidth = Number(fields[0].value);
        const roomDepth = Number(fields[1].value);
        diagram.replaceChildren();
        if (!(roomWidth >= 10 && roomWidth <= 5000 && roomDepth >= 10 && roomDepth <= 5000)) {
            const placeholder = makeSvg('text', { x: 360, y: 126, 'text-anchor': 'middle', class: 'size-fit-svg-placeholder' });
            placeholder.textContent = 'Masukkan ukuran ruang';
            diagram.append(placeholder);
            diagramHint.textContent = 'Isi lebar dan kedalaman ruang (10–5.000 cm).';
            status.textContent = 'Belum ada ukuran ruang untuk dibandingkan.';
            status.classList.remove('is-fit', 'is-no-fit');
            return;
        }

        // Fit both rectangles in the canvas so an oversized product visibly
        // extends beyond the room outline instead of being clipped.
        const scale = Math.min(620 / Math.max(roomWidth, width), 160 / Math.max(roomDepth, depth));
        const roomDrawWidth = roomWidth * scale;
        const roomDrawDepth = roomDepth * scale;
        const furnitureDrawWidth = width * scale;
        const furnitureDrawDepth = depth * scale;
        const roomX = (720 - roomDrawWidth) / 2;
        const roomY = 22 + (160 - roomDrawDepth) / 2;
        const furnitureX = 360 - furnitureDrawWidth / 2;
        const furnitureY = 102 - furnitureDrawDepth / 2;
        diagramHint.textContent = `Ruang ${roomWidth} × ${roomDepth} cm · Furnitur ${width} × ${depth} cm`;
        diagram.append(
            makeSvg('rect', { x: roomX, y: roomY, width: roomDrawWidth, height: roomDrawDepth, rx: 2, class: 'size-fit-room-outline' }),
            makeSvg('rect', { x: furnitureX, y: furnitureY, width: furnitureDrawWidth, height: furnitureDrawDepth, rx: 3, class: 'size-fit-furniture' })
        );
        if (furnitureDrawWidth >= 56 && furnitureDrawDepth >= 24) {
            const furnitureLabel = makeSvg('text', { x: 360, y: furnitureY + furnitureDrawDepth / 2 + 4, 'text-anchor': 'middle', class: 'size-fit-svg-furniture-label' });
            furnitureLabel.textContent = 'Furnitur';
            diagram.append(furnitureLabel);
        }
        const dimensionCaption = makeSvg('text', { x: 360, y: 232, 'text-anchor': 'middle', class: 'size-fit-svg-label' });
        dimensionCaption.textContent = `Area ruang: ${roomWidth} cm × ${roomDepth} cm`;
        diagram.append(dimensionCaption);
        const fits = width <= roomWidth && depth <= roomDepth;
        status.textContent = fits
            ? 'Furnitur muat pada area ini.'
            : 'Furnitur lebih besar dari salah satu sisi ruang.';
        status.classList.toggle('is-fit', fits);
        status.classList.toggle('is-no-fit', !fits);
        diagram.setAttribute('aria-label', `Area ruang ${roomWidth} kali ${roomDepth} sentimeter dan furnitur ${width} kali ${depth} sentimeter, tampak atas.`);
    };
    fields.forEach(input => input.addEventListener('input', update));
}
