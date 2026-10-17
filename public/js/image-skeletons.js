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
})();
