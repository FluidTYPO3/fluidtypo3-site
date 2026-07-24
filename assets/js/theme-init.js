(() => {
    const storageKey = 'fluidtypo3-theme';
    let storedTheme = null;

    try {
        storedTheme = window.localStorage.getItem(storageKey);
    } catch {
        storedTheme = null;
    }

    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches
        ? 'dark'
        : 'light';
    const theme = storedTheme === 'dark' || storedTheme === 'light'
        ? storedTheme
        : systemTheme;

    document.documentElement.setAttribute('data-bs-theme', theme);
})();
