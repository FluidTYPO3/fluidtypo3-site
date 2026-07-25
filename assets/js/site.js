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

    const registerTypoScriptLanguage = (highlighter) => {
        if (highlighter.getLanguage('typoscript')) {
            return;
        }

        highlighter.registerLanguage('typoscript', (hljs) => ({
            name: 'TypoScript',
            aliases: ['tsconfig'],
            case_insensitive: true,
            contains: [
                hljs.COMMENT(/^\s*#/, /$/),
                hljs.COMMENT(/\/\//, /$/),
                {
                    className: 'variable',
                    begin: /\{\$[a-z0-9_.-]+}/,
                },
                {
                    className: 'attribute',
                    begin: /^\s*[a-z0-9_.-]+(?=\s*(?:=|<|:=|=<))/,
                },
                {
                    className: 'operator',
                    begin: /:=|=<|<=|[={}()<>]/,
                },
                hljs.QUOTE_STRING_MODE,
                hljs.NUMBER_MODE,
            ],
        }));
    };

    const initializeDocumentationCodeBlocks = () => {
        const highlighter = window.hljs;
        if (!highlighter) {
            return;
        }

        registerTypoScriptLanguage(highlighter);

        document.querySelectorAll('.documentation-markdown pre > code[class*="language-"]').forEach((code) => {
            const pre = code.parentElement;
            const languageClass = [...code.classList].find((className) => className.startsWith('language-'));
            if (!(pre instanceof HTMLPreElement) || !languageClass) {
                return;
            }

            const language = languageClass.substring('language-'.length);
            if (highlighter.getLanguage(language)) {
                highlighter.highlightElement(code);
            } else {
                code.classList.add('hljs');
            }

            const source = code.textContent.replace(/\r\n?/g, '\n').replace(/\n$/, '');
            const lineCount = source === '' ? 1 : source.split('\n').length;
            const lineNumbers = document.createElement('span');
            lineNumbers.className = 'documentation-code-line-numbers';
            lineNumbers.setAttribute('aria-hidden', 'true');
            lineNumbers.textContent = Array.from(
                { length: lineCount },
                (_, index) => index + 1,
            ).join('\n');

            const codeBlock = document.createElement('div');
            codeBlock.className = 'documentation-code-block';
            pre.before(codeBlock);
            pre.classList.add('documentation-code-content');
            codeBlock.append(lineNumbers, pre);
        });
    };

    initializeDocumentationCodeBlocks();

    const searchInput = document.querySelector('.site-search-input');
    const searchTerm = new URLSearchParams(window.location.search).get('tx_solr[q]');
    if (searchInput instanceof HTMLInputElement && searchTerm !== null) {
        searchInput.value = searchTerm;
    }

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('[data-library-filter]')) {
            return;
        }

        const tagSelect = form.querySelector('[data-filter-tag]');
        const extensionSelect = form.querySelector('[data-filter-extension]');
        if (!(tagSelect instanceof HTMLSelectElement) || !(extensionSelect instanceof HTMLSelectElement)) {
            return;
        }

        const routeSegments = [];
        const tag = tagSelect.selectedOptions[0]?.dataset.routeValue;
        const extension = extensionSelect.selectedOptions[0]?.dataset.routeValue;

        if (tag) {
            routeSegments.push(form.dataset.tagSegment || 'context', tag);
        }
        if (extension) {
            routeSegments.push(form.dataset.extensionSegment || 'extension', extension);
        }

        const target = new URL(form.action, window.location.href);
        const basePath = target.pathname.replace(/\/+$/, '');
        target.pathname = [basePath, ...routeSegments.map(encodeURIComponent)].join('/') || '/';
        target.search = '';
        target.hash = '';

        event.preventDefault();
        window.location.assign(target.toString());
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        const historyBackLink = target.closest('[data-history-back]');
        if (
            historyBackLink instanceof HTMLAnchorElement
            && event instanceof MouseEvent
            && event.button === 0
            && !event.altKey
            && !event.ctrlKey
            && !event.metaKey
            && !event.shiftKey
            && window.history.length > 1
        ) {
            event.preventDefault();
            window.history.back();
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
            && selector.matches('[data-filter-tag], [data-filter-extension]')
            && selector.form?.matches('[data-library-filter]')
        ) {
            selector.form.requestSubmit();
            return;
        }

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
