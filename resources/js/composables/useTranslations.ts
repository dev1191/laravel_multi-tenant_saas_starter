import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useTranslations() {
    const page = usePage();

    const translations = computed(() => {
        return (page.props as any).translations || {};
    });

    const currentLocale = computed(() => {
        return ((page.props as any).locale as string) || 'en';
    });

    /**
     * Translate a given key with optional placeholder replacements.
     * Example: t('tenant.trial_active', { days: 5 }) or t('messages.tenant.trial_active')
     */
    function t(key: string, replacements: Record<string, string | number> = {}): string {
        const cleanKey = key.startsWith('messages.') ? key.replace(/^messages\./, '') : key;
        const msgDict = translations.value?.messages || translations.value;
        const parts = cleanKey.split('.');
        let translation: any = msgDict;

        for (const part of parts) {
            if (translation && typeof translation === 'object' && part in translation) {
                translation = translation[part];
            } else {
                translation = null;
                break;
            }
        }

        let result = typeof translation === 'string' ? translation : key;

        // Replace parameters like :name or :days
        for (const [placeholder, value] of Object.entries(replacements)) {
            result = result.replace(new RegExp(`:${placeholder}`, 'g'), String(value));
        }

        return result;
    }

    return {
        t,
        trans: t,
        locale: currentLocale,
        translations,
    };
}
