import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, type DefineComponent, h } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.config.globalProperties.$t = (key: string, replacements: Record<string, string | number> = {}) => {
            const pageProps = (props.initialPage.props as any) || {};
            const translations = pageProps.translations?.messages || pageProps.translations || {};
            const cleanKey = key.startsWith('messages.') ? key.replace(/^messages\./, '') : key;
            const parts = cleanKey.split('.');
            let translation: any = translations;

            for (const part of parts) {
                if (translation && typeof translation === 'object' && part in translation) {
                    translation = translation[part];
                } else {
                    translation = null;
                    break;
                }
            }

            let result = typeof translation === 'string' ? translation : key;

            for (const [placeholder, value] of Object.entries(replacements)) {
                result = result.replace(new RegExp(`:${placeholder}`, 'g'), String(value));
            }

            return result;
        };

        app.use(plugin).mount(el);
    },
    progress: {
        color: typeof document !== 'undefined'
            ? (getComputedStyle(document.documentElement).getPropertyValue('--tenant-primary').trim() || '#4f46e5')
            : '#4f46e5',
        showSpinner: false,
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
