(() => {
    const themeStorageKey = 'fluidtypo3-theme';
    const themeMedia = window.matchMedia('(prefers-color-scheme: dark)');

    const getStoredTheme = () => {
        try {
            return window.localStorage.getItem(themeStorageKey);
        } catch {
            return null;
        }
    };

    const setStoredTheme = (theme) => {
        try {
            window.localStorage.setItem(themeStorageKey, theme);
        } catch {
            // The selected theme still applies for this page view.
        }
    };

    const applyTheme = (theme) => {
        document.documentElement.setAttribute('data-bs-theme', theme);

        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            const nextTheme = theme === 'dark' ? 'light' : 'dark';
            const label = `Use ${nextTheme} theme`;
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);

            const icon = button.querySelector('[data-theme-icon]');
            if (icon) {
                icon.textContent = theme === 'dark' ? '☀︎' : '☾';
            }
        });
    };

    applyTheme(document.documentElement.getAttribute('data-bs-theme') || 'light');

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        const button = target.closest('[data-theme-toggle]');
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setStoredTheme(nextTheme);
        applyTheme(nextTheme);
    });

    document.addEventListener('change', (event) => {
        const selector = event.target;
        if (
            selector instanceof HTMLSelectElement
            && selector.matches('[data-documentation-selector]')
            && selector.value
        ) {
            window.location.assign(selector.value);
        }
    });

    document.addEventListener('shown.bs.modal', (event) => {
        const modal = event.target;
        if (!(modal instanceof Element) || !modal.matches('[data-features-video-modal]')) {
            return;
        }

        const video = modal.querySelector('[data-features-video]');
        if (video instanceof HTMLVideoElement) {
            video.play().catch(() => {
                // Native controls remain available if autoplay is unavailable.
            });
        }
    });

    document.addEventListener('hidden.bs.modal', (event) => {
        const modal = event.target;
        if (!(modal instanceof Element) || !modal.matches('[data-features-video-modal]')) {
            return;
        }

        const video = modal.querySelector('[data-features-video]');
        if (video instanceof HTMLVideoElement) {
            video.pause();
            video.currentTime = 0;
        }
    });

    themeMedia.addEventListener('change', (event) => {
        if (getStoredTheme() === null) {
            applyTheme(event.matches ? 'dark' : 'light');
        }
    });

    window.addEventListener('storage', (event) => {
        if (event.key === themeStorageKey && (event.newValue === 'dark' || event.newValue === 'light')) {
            applyTheme(event.newValue);
        }
    });
})();
