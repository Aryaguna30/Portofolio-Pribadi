import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const STORAGE_KEY = 'locale';
const SUPPORTED_LOCALES = ['id', 'en'];

export function useLanguage() {
    const { locale: i18nLocale } = useI18n();

    const saved = localStorage.getItem(STORAGE_KEY);
    const initialLocale = SUPPORTED_LOCALES.includes(saved) ? saved : 'id';

    const locale = ref(initialLocale);
    i18nLocale.value = initialLocale;

    function setLanguage(lang) {
        if (!SUPPORTED_LOCALES.includes(lang)) return;
        locale.value = lang;
        i18nLocale.value = lang;
        localStorage.setItem(STORAGE_KEY, lang);
    }

    function toggleLanguage() {
        const next = locale.value === 'id' ? 'en' : 'id';
        setLanguage(next);
    }

    return { locale, toggleLanguage, setLanguage };
}
