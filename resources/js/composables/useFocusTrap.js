import { ref } from 'vue';

const FOCUSABLE_SELECTORS = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(', ');

export function useFocusTrap() {
    const trapRef = ref(null);
    let handleKeydown = null;

    function getFocusableElements() {
        if (!trapRef.value) return [];
        return Array.from(trapRef.value.querySelectorAll(FOCUSABLE_SELECTORS)).filter(
            (el) => !el.closest('[hidden]') && el.offsetParent !== null
        );
    }

    function activateTrap() {
        const focusable = getFocusableElements();
        if (focusable.length > 0) {
            focusable[0].focus();
        }

        handleKeydown = (e) => {
            if (e.key !== 'Tab') return;

            const focusable = getFocusableElements();
            if (focusable.length === 0) return;

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (e.shiftKey) {
                if (document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                }
            } else {
                if (document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        };

        document.addEventListener('keydown', handleKeydown);
    }

    function deactivateTrap() {
        if (handleKeydown) {
            document.removeEventListener('keydown', handleKeydown);
            handleKeydown = null;
        }
    }

    return { trapRef, activateTrap, deactivateTrap };
}
