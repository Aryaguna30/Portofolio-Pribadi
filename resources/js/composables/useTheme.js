import { ref, computed } from 'vue';

const STORAGE_KEY = 'theme';

// Singleton state — shared across all useTheme() calls
const theme = ref('dark');

function getInitialTheme() {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved === 'dark' || saved === 'light') return saved;
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) return 'light';
    return 'dark';
}

function applyTheme(value) {
    // Set data-theme attribute (used by CSS variables in app.css)
    document.documentElement.setAttribute('data-theme', value);
    // Also toggle Tailwind dark class
    if (value === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

// Apply theme immediately on module load (before Vue mounts)
if (typeof window !== 'undefined') {
    const initial = (() => {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved === 'dark' || saved === 'light') return saved;
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) return 'light';
        return 'dark';
    })();
    theme.value = initial;
    applyTheme(initial);
}

export function useTheme() {
    const isDark = computed(() => theme.value === 'dark');

    function toggleTheme() {
        theme.value = theme.value === 'dark' ? 'light' : 'dark';
        localStorage.setItem(STORAGE_KEY, theme.value);
        applyTheme(theme.value);
    }

    return { theme, toggleTheme, isDark };
}
