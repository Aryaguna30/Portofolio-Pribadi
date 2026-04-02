import { onUnmounted } from 'vue';

export function useIntersectionObserver(callback, options = {}) {
    const { threshold = 0.1, rootMargin = '0px' } = options;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                callback(entry);
            });
        },
        { threshold, rootMargin }
    );

    function observeEl(el) {
        if (el) observer.observe(el);
    }

    function unobserveEl(el) {
        if (el) observer.unobserve(el);
    }

    onUnmounted(() => {
        observer.disconnect();
    });

    return { observeEl, unobserveEl };
}
