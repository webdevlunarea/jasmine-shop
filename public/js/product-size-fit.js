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
    const diagram = makeSvg('svg', { viewBox: '0 0 360 230', role: 'img', 'aria-label': 'Perbandingan jejak furnitur dengan ruang' });
    const status = makeElement('p', 'size-fit-status', 'Masukkan ukuran ruang untuk melihat perbandingan.');
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');
    diagramWrap.append(diagram);
    layout.append(info, form, unitNote, diagramWrap, status);
    root.replaceChildren(layout);

    const update = () => {
        const roomWidth = Number(fields[0].value);
        const roomDepth = Number(fields[1].value);
        diagram.replaceChildren();
        if (!(roomWidth >= 10 && roomWidth <= 5000 && roomDepth >= 10 && roomDepth <= 5000)) {
            status.textContent = 'Masukkan lebar dan kedalaman ruang (10–5.000 cm).';
            status.classList.remove('is-fit', 'is-no-fit');
            return;
        }

        const scale = Math.min(300 / roomWidth, 160 / roomDepth);
        const roomDrawWidth = roomWidth * scale;
        const roomDrawDepth = roomDepth * scale;
        const furnitureDrawWidth = width * scale;
        const furnitureDrawDepth = depth * scale;
        const roomX = (360 - roomDrawWidth) / 2;
        const roomY = 20 + (170 - roomDrawDepth) / 2;
        const furnitureX = 180 - furnitureDrawWidth / 2;
        const furnitureY = 105 - furnitureDrawDepth / 2;
        diagram.append(
            makeSvg('rect', { x: roomX, y: roomY, width: roomDrawWidth, height: roomDrawDepth, rx: 2, class: 'size-fit-room-outline' }),
            makeSvg('rect', { x: furnitureX, y: furnitureY, width: furnitureDrawWidth, height: furnitureDrawDepth, rx: 3, class: 'size-fit-furniture' }),
            makeSvg('text', { x: 180, y: 218, 'text-anchor': 'middle', class: 'size-fit-svg-label' })
        );
        diagram.lastElementChild.textContent = `Area lantai ${roomWidth} × ${roomDepth} cm`;
        const fits = width <= roomWidth && depth <= roomDepth;
        status.textContent = fits
            ? 'Jejak ukuran furnitur berada di dalam ukuran area yang dimasukkan.'
            : 'Jejak furnitur lebih besar dari salah satu ukuran area yang dimasukkan.';
        status.classList.toggle('is-fit', fits);
        status.classList.toggle('is-no-fit', !fits);
        diagram.setAttribute('aria-label', `Area lantai ${roomWidth} kali ${roomDepth} sentimeter; jejak furnitur ${width} kali ${depth} sentimeter.`);
    };
    fields.forEach(input => input.addEventListener('input', update));
}
