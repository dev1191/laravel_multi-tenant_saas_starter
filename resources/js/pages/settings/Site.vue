<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/settings/Layout.vue';
import { useAppearance } from '@/composables/useAppearance';
import CurrencySelect from '@/components/CurrencySelect.vue';
import TimezoneSelect from '@/components/TimezoneSelect.vue';
import LanguageSelect from '@/components/LanguageSelect.vue';
import FileUpload from '@/components/FileUpload.vue';
import ThemeModeSelect from '@/components/ThemeModeSelect.vue';
import { Check, Eye, Globe, Laptop, Mail, Monitor, Moon, Palette, Plus, Save, Send, Server, Settings, Smartphone, Sparkles, Sun } from 'lucide-vue-next';

interface Locale {
    id: number;
    code: string;
    name: string;
    direction: string;
    is_default: boolean;
}

interface LanguageOption {
    code: string;
    name: string;
    native_name?: string;
    direction?: string;
    flag?: string;
}

interface Props {
    settings: {
        site_name: string;
        logo_path: string | null;
        logo_light_path?: string | null;
        logo_dark_path?: string | null;
        primary_color: string;
        theme?: string;
        default_locale: string;
        default_currency: string;
        timezone: string;
        registration_enabled: boolean;
        mail_driver?: string;
        mail_host?: string;
        mail_port?: number;
        mail_username?: string;
        mail_password?: string;
        mail_encryption?: string;
        mail_from_address?: string;
        mail_from_name?: string;
    };
    locales: Locale[];
    available_languages?: LanguageOption[];
    currencies?: Record<string, string>;
    timezones?: Record<string, string>;
}

const props = defineProps<Props>();
const { updateAppearance, updateBrandColor } = useAppearance();

const activeTab = ref<'branding' | 'localization' | 'mail' | 'preview'>('branding');

const settingsForm = useForm({
    site_name: props.settings.site_name,
    logo_light_path: props.settings.logo_light_path || props.settings.logo_path || '',
    logo_dark_path: props.settings.logo_dark_path || '',
    primary_color: props.settings.primary_color,
    theme: props.settings.theme || 'system',
    default_locale: props.settings.default_locale,
    default_currency: props.settings.default_currency,
    timezone: props.settings.timezone,
    registration_enabled: props.settings.registration_enabled,
    mail_driver: props.settings.mail_driver || 'default',
    mail_host: props.settings.mail_host || '',
    mail_port: props.settings.mail_port || 587,
    mail_username: props.settings.mail_username || '',
    mail_password: props.settings.mail_password || '',
    mail_encryption: props.settings.mail_encryption || 'tls',
    mail_from_address: props.settings.mail_from_address || '',
    mail_from_name: props.settings.mail_from_name || '',
});

watch(
    () => settingsForm.primary_color,
    (newColor) => {
        if (newColor && /^#([a-f0-9]{6}|[a-f0-9]{3})$/i.test(newColor)) {
            updateBrandColor(newColor);
        }
    }
);

const setTheme = (mode: 'light' | 'dark' | 'system') => {
    settingsForm.theme = mode;
    updateAppearance(mode);
};

const saveSettings = () => {
    settingsForm.patch('/settings/site', {
        onSuccess: () => {
            updateAppearance(settingsForm.theme as any);
            updateBrandColor(settingsForm.primary_color);
        },
    });
};

const showTestEmailModal = ref(false);
const testEmailForm = useForm({
    recipient_email: '',
});

const sendTestEmail = () => {
    testEmailForm.post('/settings/site/test-email', {
        onSuccess: () => {
            showTestEmailModal.value = false;
            testEmailForm.reset();
        },
    });
};

const previewDevice = ref<'desktop' | 'mobile'>('desktop');
const previewUrl = computed(() => {
    const params = new URLSearchParams({
        site_name: settingsForm.site_name || '',
        primary_color: settingsForm.primary_color || '#4f46e5',
    });
    return `/settings/site/email-preview?${params.toString()}`;
});

const showAddLocale = ref(false);
const selectedPresetLanguage = ref('');

const localeForm = useForm({
    code: '',
    name: '',
    direction: 'ltr',
    is_default: false,
});

const onPresetLanguageChange = (e: Event) => {
    const code = (e.target as HTMLSelectElement).value;
    selectedPresetLanguage.value = code;

    const found = (props.available_languages || []).find((l) => l.code === code);
    if (found) {
        localeForm.code = found.code;
        localeForm.name = found.name;
        localeForm.direction = found.direction || (['ar', 'fa', 'he', 'ur'].includes(found.code) ? 'rtl' : 'ltr');
    }
};

const openAddLocaleModal = () => {
    selectedPresetLanguage.value = '';
    localeForm.reset();
    showAddLocale.value = true;
};

const addLocale = () => {
    localeForm.post('/settings/locales', {
        onSuccess: () => {
            showAddLocale.value = false;
            localeForm.reset();
        },
    });
};

const breadcrumbs = [
    { title: 'Workspace Settings', href: '/settings/site' },
];
</script>

<template>
    <Head :title="$t('settings.general') || 'Workspace Settings'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6 max-w-5xl">
            <!-- Header with Title and Global Save Action -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ $t('settings.general') || 'Workspace Settings' }}</h1>
                    <p class="text-sm text-muted-foreground mt-0.5">
                        {{ $t('settings.localization') || 'Customize your workspace branding, regional preferences, mail delivery, and translations.' }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="saveSettings"
                        :disabled="settingsForm.processing"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-sm font-semibold shadow-sm transition cursor-pointer"
                    >
                        <Save class="w-4 h-4" />
                        <span>{{ settingsForm.processing ? 'Saving...' : ($t('common.save') || 'Save Changes') }}</span>
                    </button>
                </div>
            </div>

            <!-- Tabbed Navigation Bar -->
            <div class="flex items-center gap-1.5 p-1 bg-muted/60 dark:bg-muted/40 rounded-xl border w-fit overflow-x-auto">
                <button
                    type="button"
                    @click="activeTab = 'branding'"
                    :class="[
                        'inline-flex items-center gap-2 px-3.5 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition cursor-pointer',
                        activeTab === 'branding'
                            ? 'bg-background text-foreground shadow-xs font-semibold'
                            : 'text-muted-foreground hover:text-foreground'
                    ]"
                >
                    <Palette class="w-4 h-4 text-indigo-500" />
                    <span>Branding & Visuals</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'localization'"
                    :class="[
                        'inline-flex items-center gap-2 px-3.5 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition cursor-pointer',
                        activeTab === 'localization'
                            ? 'bg-background text-foreground shadow-xs font-semibold'
                            : 'text-muted-foreground hover:text-foreground'
                    ]"
                >
                    <Globe class="w-4 h-4 text-blue-500" />
                    <span>Regional & Languages</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'mail'"
                    :class="[
                        'inline-flex items-center gap-2 px-3.5 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition cursor-pointer',
                        activeTab === 'mail'
                            ? 'bg-background text-foreground shadow-xs font-semibold'
                            : 'text-muted-foreground hover:text-foreground'
                    ]"
                >
                    <Mail class="w-4 h-4 text-emerald-500" />
                    <span>Mail Server & SMTP</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'preview'"
                    :class="[
                        'inline-flex items-center gap-2 px-3.5 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition cursor-pointer',
                        activeTab === 'preview'
                            ? 'bg-background text-foreground shadow-xs font-semibold'
                            : 'text-muted-foreground hover:text-foreground'
                    ]"
                >
                    <Eye class="w-4 h-4 text-purple-500" />
                    <span>Live Email Preview</span>
                </button>
            </div>

            <!-- Tab 1: Branding & Visuals -->
            <div v-show="activeTab === 'branding'" class="space-y-6">
                <div class="rounded-xl border bg-card p-6 shadow-sm space-y-6">
                    <div class="flex items-center gap-2 border-b pb-4">
                        <Palette class="w-5 h-5 text-indigo-500" />
                        <div>
                            <h2 class="text-base font-semibold">{{ $t('settings.appearance') || 'Workspace Identity & Colors' }}</h2>
                            <p class="text-xs text-muted-foreground">Define your workspace name, primary brand colors, and logos.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1.5">{{ $t('settings.site_name') || 'Workspace Name' }}</label>
                            <input
                                v-model="settingsForm.site_name"
                                type="text"
                                required
                                class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent focus:ring-2 focus:ring-indigo-500"
                            />
                            <p v-if="settingsForm.errors.site_name" class="text-xs text-red-500 mt-1">{{ settingsForm.errors.site_name }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1.5">{{ $t('settings.appearance') || 'Primary Brand Color' }}</label>
                            <div class="flex items-center gap-2.5">
                                <input
                                    v-model="settingsForm.primary_color"
                                    type="color"
                                    class="w-10 h-9 p-0.5 border rounded-lg bg-transparent cursor-pointer"
                                />
                                <input
                                    v-model="settingsForm.primary_color"
                                    type="text"
                                    class="flex-1 px-3 py-2 border rounded-lg text-sm bg-transparent font-mono"
                                />
                            </div>
                        </div>

                        <!-- Light Mode Logo -->
                        <div class="col-span-1">
                            <FileUpload
                                v-model="settingsForm.logo_light_path"
                                label="Light Mode Logo"
                                helper-text="Used across headers and transactional documents in light mode."
                                preview-variant="light"
                            />
                        </div>

                        <!-- Dark Mode Logo -->
                        <div class="col-span-1">
                            <FileUpload
                                v-model="settingsForm.logo_dark_path"
                                label="Dark Mode Logo"
                                helper-text="Used across headers and transactional documents in dark mode."
                                preview-variant="dark"
                            />
                        </div>

                        <!-- Default Theme Mode -->
                        <div class="col-span-full pt-2">
                            <label class="block text-xs font-medium text-muted-foreground mb-2">{{ $t('settings.appearance') || 'Default Theme Mode' }}</label>
                            <ThemeModeSelect
                                v-model="settingsForm.theme"
                                @update:model-value="(v) => updateAppearance(v as any)"
                            />
                            <p v-if="settingsForm.errors.theme" class="text-xs text-red-500 mt-1">{{ settingsForm.errors.theme }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Regional & Languages -->
            <div v-show="activeTab === 'localization'" class="space-y-6">
                <!-- Regional Preferences Card -->
                <div class="rounded-xl border bg-card p-6 shadow-sm space-y-5">
                    <div class="flex items-center gap-2 border-b pb-4">
                        <Globe class="w-5 h-5 text-blue-500" />
                        <div>
                            <h2 class="text-base font-semibold">Regional Defaults & Timezone</h2>
                            <p class="text-xs text-muted-foreground">Configure default language, operating currency, and member signup settings.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1.5">{{ $t('settings.language') || 'Default Language' }}</label>
                            <LanguageSelect
                                v-model="settingsForm.default_locale"
                                :languages="available_languages"
                            />
                            <p v-if="settingsForm.errors.default_locale" class="text-xs text-red-500 mt-1">{{ settingsForm.errors.default_locale }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1.5">{{ $t('settings.currency') || 'Default Currency' }}</label>
                            <CurrencySelect
                                v-model="settingsForm.default_currency"
                                :currencies="currencies"
                            />
                            <p v-if="settingsForm.errors.default_currency" class="text-xs text-red-500 mt-1">{{ settingsForm.errors.default_currency }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1.5">{{ $t('settings.timezone') || 'Default Timezone' }}</label>
                            <TimezoneSelect
                                v-model="settingsForm.timezone"
                                :timezones="timezones"
                            />
                            <p v-if="settingsForm.errors.timezone" class="text-xs text-red-500 mt-1">{{ settingsForm.errors.timezone }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 pt-2">
                        <input
                            v-model="settingsForm.registration_enabled"
                            id="reg"
                            type="checkbox"
                            class="rounded text-indigo-600 focus:ring-indigo-500"
                        />
                        <label for="reg" class="text-sm font-medium">Allow member registration via workspace invitations</label>
                    </div>
                </div>

                <!-- Configured Locales Table Card -->
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4 border-b pb-4">
                        <div>
                            <h2 class="text-base font-semibold">{{ $t('settings.languages') || 'Configured Languages & RTL' }}</h2>
                            <p class="text-xs text-muted-foreground">Supported language options available to users in this workspace.</p>
                        </div>
                        <button
                            type="button"
                            @click="openAddLocaleModal"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 rounded-lg text-xs font-medium transition cursor-pointer"
                        >
                            <Plus class="w-3.5 h-3.5" />
                            <span>{{ $t('language.management') || 'Add Language' }}</span>
                        </button>
                    </div>

                    <div class="divide-y divide-border/60">
                        <div
                            v-for="loc in locales"
                            :key="loc.id"
                            class="py-3 flex items-center justify-between text-sm"
                        >
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-xs px-2 py-0.5 rounded bg-muted font-bold">
                                    {{ loc.code }}
                                </span>
                                <span class="font-medium">{{ loc.name }}</span>
                                <span v-if="loc.is_default" class="text-[10px] bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 font-bold px-1.5 py-0.5 rounded">
                                    {{ $t('language.is_default') || 'Default' }}
                                </span>
                            </div>
                            <span class="text-xs text-muted-foreground uppercase font-mono">
                                {{ $t('language.direction') || 'Direction' }}: {{ loc.direction }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Mail Server & SMTP -->
            <div v-show="activeTab === 'mail'" class="space-y-6">
                <div class="rounded-xl border bg-card p-6 shadow-sm space-y-5">
                    <div class="flex items-center justify-between border-b pb-4">
                        <div class="flex items-center gap-2">
                            <Mail class="w-5 h-5 text-emerald-500" />
                            <div>
                                <h2 class="text-base font-semibold">Mail Server & White-Label Delivery</h2>
                                <p class="text-xs text-muted-foreground">Configure custom SMTP servers for white-labeled transactional email delivery.</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="showTestEmailModal = true"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 dark:hover:bg-emerald-900/60 rounded-lg text-xs font-medium transition cursor-pointer"
                        >
                            <Send class="w-3.5 h-3.5" />
                            <span>Send Test Email</span>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1.5">Mail Delivery Mode</label>
                            <select
                                v-model="settingsForm.mail_driver"
                                class="w-full px-3 py-2 border rounded-lg text-sm bg-background text-foreground dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 font-medium"
                            >
                                <option value="default">Use Platform Mail Delivery (Default)</option>
                                <option value="smtp">Custom SMTP Server (White-label domain)</option>
                            </select>
                        </div>

                        <!-- Custom SMTP Settings (Collapsible) -->
                        <div v-if="settingsForm.mail_driver === 'smtp'" class="space-y-4 pt-3 border-t border-dashed">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">SMTP Host</label>
                                    <input
                                        v-model="settingsForm.mail_host"
                                        type="text"
                                        placeholder="smtp.mailgun.org, smtp.sendgrid.net, etc."
                                        class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent font-mono"
                                    />
                                    <p v-if="settingsForm.errors.mail_host" class="text-xs text-red-500 mt-1">{{ settingsForm.errors.mail_host }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">SMTP Port</label>
                                    <input
                                        v-model="settingsForm.mail_port"
                                        type="number"
                                        placeholder="587"
                                        class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent font-mono"
                                    />
                                    <p v-if="settingsForm.errors.mail_port" class="text-xs text-red-500 mt-1">{{ settingsForm.errors.mail_port }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">SMTP Username</label>
                                    <input
                                        v-model="settingsForm.mail_username"
                                        type="text"
                                        placeholder="apikey, username, etc."
                                        class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">SMTP Password</label>
                                    <input
                                        v-model="settingsForm.mail_password"
                                        type="password"
                                        placeholder="••••••••"
                                        class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">Encryption</label>
                                    <select
                                        v-model="settingsForm.mail_encryption"
                                        class="w-full px-3 py-2 border rounded-lg text-sm bg-background text-foreground dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700"
                                    >
                                        <option value="tls">TLS (Port 587)</option>
                                        <option value="ssl">SSL (Port 465)</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">From Email Address</label>
                                    <input
                                        v-model="settingsForm.mail_from_address"
                                        type="email"
                                        placeholder="team@yourcompany.com"
                                        class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent"
                                    />
                                    <p v-if="settingsForm.errors.mail_from_address" class="text-xs text-red-500 mt-1">{{ settingsForm.errors.mail_from_address }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">From Sender Name</label>
                                    <input
                                        v-model="settingsForm.mail_from_name"
                                        type="text"
                                        placeholder="Acme Support"
                                        class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Live Email Preview -->
            <div v-show="activeTab === 'preview'" class="space-y-6">
                <div class="rounded-xl border bg-card p-6 shadow-sm space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b pb-4">
                        <div class="flex items-center gap-2">
                            <Eye class="w-5 h-5 text-purple-500" />
                            <div>
                                <h2 class="text-base font-semibold">Email Template & Live Simulation</h2>
                                <p class="text-xs text-muted-foreground">Interactive preview of how invitations appear to recipients with your branding.</p>
                            </div>
                        </div>

                        <!-- Device Toggle Switcher -->
                        <div class="flex items-center bg-muted/60 dark:bg-muted/40 p-1 rounded-lg border">
                            <button
                                type="button"
                                @click="previewDevice = 'desktop'"
                                :class="[
                                    'inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-md transition cursor-pointer',
                                    previewDevice === 'desktop' ? 'bg-background shadow-xs text-foreground font-semibold' : 'text-muted-foreground hover:text-foreground'
                                ]"
                            >
                                <Laptop class="w-3.5 h-3.5" />
                                <span>Desktop (580px)</span>
                            </button>
                            <button
                                type="button"
                                @click="previewDevice = 'mobile'"
                                :class="[
                                    'inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-md transition cursor-pointer',
                                    previewDevice === 'mobile' ? 'bg-background shadow-xs text-foreground font-semibold' : 'text-muted-foreground hover:text-foreground'
                                ]"
                            >
                                <Smartphone class="w-3.5 h-3.5" />
                                <span>Mobile (375px)</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-center bg-neutral-100/70 dark:bg-neutral-900/60 p-4 sm:p-8 rounded-xl border border-dashed">
                        <div
                            :style="{
                                width: previewDevice === 'mobile' ? '375px' : '100%',
                                maxWidth: previewDevice === 'mobile' ? '375px' : '620px',
                                transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
                            }"
                            class="relative rounded-xl overflow-hidden shadow-lg border bg-white"
                        >
                            <iframe
                                :src="previewUrl"
                                class="w-full h-[540px] border-0 bg-white"
                                title="Email Live Preview"
                            ></iframe>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                        <Sparkles class="w-4 h-4 text-amber-500 shrink-0" />
                        <span>Live simulation updates dynamically with your configured <strong>Workspace Name ({{ settingsForm.site_name }})</strong> and <strong>Brand Accent ({{ settingsForm.primary_color }})</strong>.</span>
                    </div>
                </div>
            </div>

            <!-- Add Locale Modal -->
            <div
                v-if="showAddLocale"
                class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
            >
                <div class="bg-card w-full max-w-sm rounded-xl border shadow-xl p-6">
                    <h3 class="text-base font-bold mb-3">{{ $t('language.management') || 'Add Supported Locale' }}</h3>
                    <form @submit.prevent="addLocale" class="space-y-3">
                        <div>
                            <label class="block text-xs text-muted-foreground mb-1">Choose from Available Languages</label>
                            <select
                                :value="selectedPresetLanguage"
                                @change="onPresetLanguageChange"
                                class="w-full px-3 py-2 border rounded-lg text-sm bg-background text-foreground dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700"
                            >
                                <option value="" class="bg-white text-gray-500 dark:bg-neutral-900 dark:text-neutral-400">-- Select a standard language --</option>
                                <option
                                    v-for="lang in (available_languages || [])"
                                    :key="lang.code"
                                    :value="lang.code"
                                    class="bg-white text-gray-900 dark:bg-neutral-900 dark:text-neutral-100"
                                >
                                    {{ lang.flag ? `${lang.flag} ` : '' }}{{ lang.name }} ({{ lang.code }})
                                </option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs text-muted-foreground mb-1">{{ $t('language.code') || 'Locale Code' }}</label>
                                <input
                                    v-model="localeForm.code"
                                    type="text"
                                    required
                                    maxlength="5"
                                    placeholder="e.g. fr"
                                    class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent font-mono uppercase"
                                />
                            </div>
                            <div>
                                <label class="block text-xs text-muted-foreground mb-1">{{ $t('language.direction') || 'Direction' }}</label>
                                <select v-model="localeForm.direction" class="w-full px-3 py-2 border rounded-lg text-sm bg-background text-foreground dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700">
                                    <option value="ltr">LTR</option>
                                    <option value="rtl">RTL</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs text-muted-foreground mb-1">{{ $t('language.name') || 'Language Name' }}</label>
                            <input
                                v-model="localeForm.name"
                                type="text"
                                required
                                placeholder="e.g. Français"
                                class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent"
                            />
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <input v-model="localeForm.is_default" id="def" type="checkbox" class="rounded text-indigo-600" />
                            <label for="def" class="text-xs">{{ $t('language.is_default') || 'Set as workspace default' }}</label>
                        </div>

                        <div class="flex justify-end gap-2 pt-3 border-t">
                            <button type="button" @click="showAddLocale = false" class="px-3 py-1.5 text-xs text-muted-foreground cursor-pointer">
                                {{ $t('common.cancel') }}
                            </button>
                            <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-semibold shadow-xs cursor-pointer">
                                {{ $t('common.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Send Test Email Modal -->
            <div
                v-if="showTestEmailModal"
                class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
            >
                <div class="bg-card w-full max-w-sm rounded-xl border shadow-xl p-6">
                    <h3 class="text-base font-bold mb-2">Send Test Verification Email</h3>
                    <p class="text-xs text-muted-foreground mb-4">Send a live test message to verify your workspace mail credentials.</p>
                    <form @submit.prevent="sendTestEmail" class="space-y-3">
                        <div>
                            <label class="block text-xs text-muted-foreground mb-1">Recipient Email Address</label>
                            <input
                                v-model="testEmailForm.recipient_email"
                                type="email"
                                required
                                placeholder="you@example.com"
                                class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent"
                            />
                        </div>

                        <div class="flex justify-end gap-2 pt-3 border-t">
                            <button type="button" @click="showTestEmailModal = false" class="px-3 py-1.5 text-xs text-muted-foreground cursor-pointer">
                                {{ $t('common.cancel') }}
                            </button>
                            <button
                                type="submit"
                                :disabled="testEmailForm.processing"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs cursor-pointer"
                            >
                                <Send class="w-3 h-3" />
                                <span>Send Test</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
