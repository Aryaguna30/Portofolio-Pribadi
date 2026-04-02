import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue, route } from '../../vendor/tightenco/ziggy';
import { Ziggy } from './ziggy.js';
import i18n from './i18n/index.js';

createInertiaApp({
    title: (title) => title ? `${title} — Portfolio` : 'Portfolio',
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        // Merge server-side Ziggy config (from shared props) with static file
        const serverZiggy = props.initialPage?.props?.ziggy;
        const ziggyConfig = serverZiggy
            ? { ...Ziggy, ...serverZiggy, routes: { ...Ziggy.routes, ...serverZiggy.routes } }
            : Ziggy;

        // Make route() available globally in script setup contexts
        window.route = (name, params, absolute) => route(name, params, absolute, ziggyConfig);

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, ziggyConfig)
            .use(i18n)
            .mount(el);
    },
    progress: {
        color: '#8b5cf6',
    },
});
