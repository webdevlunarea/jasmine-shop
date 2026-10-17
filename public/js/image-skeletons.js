(function () {
    'use strict';

    function prepareImage(image) {
        const wrapper = image.closest('.image-skeleton');
        if (!wrapper) return;

        function markLoaded() {
            wrapper.classList.remove('is-image-error');
            wrapper.classList.add('is-image-ready');
        }

        function markFailed() {
            wrapper.classList.remove('is-image-ready');
            wrapper.classList.add('is-image-error');
        }

        image.addEventListener('load', markLoaded, { once: true });
        image.addEventListener('error', markFailed, { once: true });

        if (image.complete) {
            if (image.naturalWidth > 0) markLoaded();
            else markFailed();
        }
    }

    document.querySelectorAll('.image-skeleton [data-skeleton-image]').forEach(prepareImage);

    document.querySelectorAll('.image-skeleton [data-skeleton-background]').forEach(function (figure) {
        const wrapper = figure.closest('.image-skeleton');
        const background = window.getComputedStyle(figure).backgroundImage;
        const match = background && background.match(/^url\(["']?(.*?)["']?\)$/);
        if (!wrapper || !match || !match[1]) {
            if (wrapper) wrapper.classList.add('is-image-error');
            return;
        }
        const probe = new Image();
        probe.onload = function () {
            wrapper.classList.remove('is-image-error');
            wrapper.classList.add('is-image-ready');
        };
        probe.onerror = function () {
            wrapper.classList.remove('is-image-ready');
            wrapper.classList.add('is-image-error');
        };
        probe.src = match[1];
        if (probe.complete && probe.naturalWidth > 0) wrapper.classList.add('is-image-ready');
    });
})();
