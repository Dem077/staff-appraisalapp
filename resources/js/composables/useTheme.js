import { ref, watch } from 'vue';

const STORAGE_KEY = 'theme';
const isDark = ref(false);
let initialized = false;

function prefersDark() {
    return typeof window !== 'undefined'
        && window.matchMedia('(prefers-color-scheme: dark)').matches;
}

function applyTheme(dark) {
    if (typeof document === 'undefined') return;
    document.documentElement.classList.toggle('dark', dark);
    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) {
        meta.setAttribute('content', dark ? '#020617' : '#059669');
    }
}

export function initTheme() {
    if (initialized || typeof window === 'undefined') return;
    initialized = true;

    const stored = localStorage.getItem(STORAGE_KEY);
    isDark.value = stored === 'dark' || (stored !== 'light' && prefersDark());
    applyTheme(isDark.value);

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
        if (localStorage.getItem(STORAGE_KEY)) return;
        isDark.value = event.matches;
        applyTheme(isDark.value);
    });
}

export function useTheme() {
    if (!initialized) {
        initTheme();
    }

    watch(isDark, (dark) => {
        applyTheme(dark);
        localStorage.setItem(STORAGE_KEY, dark ? 'dark' : 'light');
    });

    function toggle() {
        isDark.value = !isDark.value;
    }

    function setDark(value) {
        isDark.value = value;
    }

    return { isDark, toggle, setDark };
}
