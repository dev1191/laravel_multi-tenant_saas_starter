<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    Boxes,
    Building2,
    Check,
    CheckCircle2,
    Database,
    Globe,
    Lock,
    Shield,
    Sparkles,
    User,
    Zap,
} from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import {
    Stepper,
    StepperDescription,
    StepperIndicator,
    StepperItem,
    StepperSeparator,
    StepperTitle,
    StepperTrigger,
} from '@/components/ui/stepper';
import { login } from '@/routes';
import { store } from '@/routes/register';

const page = usePage();
const isCentral = computed(() => !page.props.tenant);
const centralDomain = computed(() => (page.props.central_domain as string) || 'tenantforge.test');

const currentStep = ref(1);
const step1Error = ref('');

const workspaceName = ref('');
const subdomain = ref('');
const isSubdomainManual = ref(false);

const isStep1Valid = computed(() => {
    const name = workspaceName.value.trim();
    const slug = subdomain.value.trim();
    return name.length >= 2 && slug.length >= 3 && /^[a-z0-9-]+$/.test(slug);
});

const onWorkspaceNameChange = (e: Event) => {
    const val = (e.target as HTMLInputElement).value;
    workspaceName.value = val;
    step1Error.value = '';
    if (!isSubdomainManual.value) {
        subdomain.value = val
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .slice(0, 30);
    }
};

const onSubdomainInput = (e: Event) => {
    isSubdomainManual.value = true;
    step1Error.value = '';
    subdomain.value = (e.target as HTMLInputElement).value
        .toLowerCase()
        .replace(/[^a-z0-9-]+/g, '');
};

const validateStep1 = (): boolean => {
    step1Error.value = '';
    const name = workspaceName.value.trim();
    const slug = subdomain.value.trim();

    if (!name || name.length < 2) {
        step1Error.value = 'Please enter a valid workspace name (at least 2 characters).';
        return false;
    }

    if (!slug || slug.length < 3) {
        step1Error.value = 'Please enter a valid workspace subdomain (at least 3 characters).';
        return false;
    }

    if (!/^[a-z0-9-]+$/.test(slug)) {
        step1Error.value = 'The workspace URL may only contain lowercase letters, numbers, and dashes.';
        return false;
    }

    return true;
};

const goToNextStep = () => {
    if (validateStep1()) {
        currentStep.value = 2;
    }
};

const goToPrevStep = () => {
    currentStep.value = 1;
};

// If backend returns validation errors for step 1 while on step 2, auto-switch back to step 1
watch(
    () => page.props.errors as Record<string, string>,
    (errs) => {
        if (errs && (errs.workspace_name || errs.subdomain)) {
            currentStep.value = 1;
        }
    },
    { deep: true },
);
</script>

<template>
    <Head :title="isCentral ? ($t('auth.create_workspace') || 'Create your workspace') : $t('auth.sign_up')" />

    <div class="min-h-dvh bg-background text-foreground flex flex-col lg:grid lg:grid-cols-12">
        <!-- Left Column: Branding, Value Prop & Visual Architecture Guarantee (Desktop) -->
        <div class="hidden lg:flex lg:col-span-5 relative flex-col justify-between bg-slate-950 text-slate-100 p-10 xl:p-14 border-r border-slate-800/80 overflow-hidden">
            <!-- Engineered Grid Pattern Background -->
            <div
                class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:3rem_3rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"
                aria-hidden="true"
            />

            <!-- Top: Brand -->
            <div class="relative z-10">
                <Link href="/" class="inline-flex items-center gap-3 transition hover:opacity-90">
                    <div class="flex size-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900 text-indigo-400">
                        <Boxes class="size-5" />
                    </div>
                    <span class="text-lg font-semibold tracking-tight text-white">TenantForge</span>
                    <span class="rounded-full border border-slate-800 bg-slate-900/90 px-2.5 py-0.5 text-xs font-medium text-slate-300 tabular-nums">
                        v1.0
                    </span>
                </Link>
            </div>

            <!-- Middle: B2B Architecture Highlights -->
            <div class="relative z-10 my-auto py-10 space-y-8">
                <div class="space-y-3">
                    <div class="inline-flex items-center gap-2 rounded-full border border-slate-800 bg-slate-900 px-3 py-1 text-xs font-medium text-indigo-400">
                        <Sparkles class="size-3.5" />
                        <span>Production Multi-Tenancy</span>
                    </div>
                    <h1 class="text-2xl xl:text-3xl font-bold tracking-tight text-balance text-white">
                        Launch your dedicated B2B workspace in seconds.
                    </h1>
                    <p class="text-sm text-slate-400 leading-relaxed text-pretty">
                        Zero cross-tenant contamination with database-per-tenant isolation, automated queue provisioning, and multi-currency billing out of the box.
                    </p>
                </div>

                <!-- Feature Cards -->
                <div class="space-y-3.5">
                    <div
                        :class="[
                            isCentral && currentStep === 2
                                ? 'border-indigo-500/60 bg-indigo-950/20'
                                : 'border-slate-800/80 bg-slate-900/40',
                        ]"
                        class="flex items-start gap-3.5 rounded-xl border p-3.5 transition"
                    >
                        <div class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg border border-slate-800 bg-slate-900 text-indigo-400">
                            <Database class="size-4" />
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-white">Dedicated Database Isolation</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Separate database per tenant eliminates cross-tenant data exposure.</p>
                        </div>
                    </div>

                    <div
                        :class="[
                            isCentral && currentStep === 1
                                ? 'border-indigo-500/60 bg-indigo-950/20'
                                : 'border-slate-800/80 bg-slate-900/40',
                        ]"
                        class="flex items-start gap-3.5 rounded-xl border p-3.5 transition"
                    >
                        <div class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg border border-slate-800 bg-slate-900 text-indigo-400">
                            <Globe class="size-4" />
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-white">Subdomain Routing & Wildcards</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Automated tenant domain resolution and CNAME custom domain ready.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5 rounded-xl border border-slate-800/80 bg-slate-900/40 p-3.5">
                        <div class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg border border-slate-800 bg-slate-900 text-indigo-400">
                            <Zap class="size-4" />
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-white">Async Provisioning Pipeline</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Non-blocking background migrations and default seeding with live feedback.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Left: Trust Guarantee & Docs -->
            <div class="relative z-10 pt-4 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-500">
                <span>14-day free trial &middot; No credit card</span>
                <Link href="/login" class="text-slate-400 hover:text-white transition">Sign in &rarr;</Link>
            </div>
        </div>

        <!-- Right Column: Registration Wizard Form -->
        <div class="flex-1 lg:col-span-7 flex flex-col justify-center px-6 py-12 sm:px-10 lg:px-16 overflow-y-auto">
            <div class="w-full max-w-xl mx-auto space-y-8">
                <!-- Mobile Brand Header -->
                <div class="lg:hidden flex items-center justify-between mb-2">
                    <Link href="/" class="inline-flex items-center gap-2.5">
                        <div class="flex size-8 items-center justify-center rounded-lg border border-slate-800 bg-slate-900 text-indigo-400">
                            <Boxes class="size-4" />
                        </div>
                        <span class="font-semibold text-foreground">TenantForge</span>
                    </Link>
                    <Link :href="login()" class="text-xs text-muted-foreground hover:text-foreground">
                        {{ $t('auth.sign_in') }} &rarr;
                    </Link>
                </div>

                <!-- Page Title / Description -->
                <div class="space-y-2">
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-foreground text-balance">
                        {{ isCentral ? ($t('auth.create_workspace') || 'Create your workspace') : $t('auth.sign_up') }}
                    </h2>
                    <p class="text-sm text-muted-foreground text-pretty">
                        {{ isCentral ? ($t('auth.create_workspace_desc') || 'Start your 14-day free trial. Setup takes under a minute.') : ($t('auth.join_workspace_desc') || 'Enter your details below to join your workspace') }}
                    </p>
                </div>

                <!-- Stepper Progress (Central Only) -->
                <div v-if="isCentral" class="rounded-xl border border-border bg-card p-3 shadow-2xs">
                    <Stepper
                        v-model="currentStep"
                        class="flex w-full items-center justify-between gap-2"
                    >
                        <StepperItem
                            v-slot="{ state }"
                            :step="1"
                            class="flex flex-1 items-center gap-2"
                        >
                            <StepperTrigger
                                type="button"
                                class="flex flex-row items-center gap-3 p-1 rounded-lg text-left hover:bg-muted/40 transition cursor-pointer"
                            >
                                <StepperIndicator class="size-8 rounded-lg font-bold text-xs shrink-0">
                                    <Check v-if="state === 'completed'" class="size-4 text-emerald-500" />
                                    <span v-else>1</span>
                                </StepperIndicator>
                                <div class="hidden sm:flex flex-col">
                                    <StepperTitle class="text-xs font-semibold text-foreground">Workspace</StepperTitle>
                                    <StepperDescription class="text-[11px] text-muted-foreground">Name &amp; Subdomain</StepperDescription>
                                </div>
                            </StepperTrigger>
                            <StepperSeparator class="h-0.5 flex-1 rounded-full bg-border group-data-[state=completed]:bg-indigo-600" />
                        </StepperItem>

                        <StepperItem
                            v-slot="{ state }"
                            :step="2"
                            :disabled="!isStep1Valid"
                            class="flex items-center gap-2"
                        >
                            <StepperTrigger
                                type="button"
                                class="flex flex-row items-center gap-3 p-1 rounded-lg text-left hover:bg-muted/40 transition cursor-pointer disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <StepperIndicator class="size-8 rounded-lg font-bold text-xs shrink-0">
                                    <Check v-if="state === 'completed'" class="size-4 text-emerald-500" />
                                    <span v-else>2</span>
                                </StepperIndicator>
                                <div class="hidden sm:flex flex-col">
                                    <StepperTitle class="text-xs font-semibold text-foreground">Admin Account</StepperTitle>
                                    <StepperDescription class="text-[11px] text-muted-foreground">Credentials &amp; Security</StepperDescription>
                                </div>
                            </StepperTrigger>
                        </StepperItem>
                    </Stepper>
                </div>

                <!-- Form Card -->
                <div class="rounded-2xl border border-border bg-card p-6 sm:p-8 shadow-xs">
                    <Form
                        v-bind="store.form()"
                        :reset-on-success="['password', 'password_confirmation']"
                        v-slot="{ errors, processing }"
                        class="flex flex-col gap-6"
                    >
                        <!-- ============================================== -->
                        <!-- STEP 1: Workspace Setup (Central Domain Only) -->
                        <!-- ============================================== -->
                        <div v-show="!isCentral || currentStep === 1" class="space-y-4">
                            <template v-if="isCentral">
                                <div class="space-y-1 pb-1">
                                    <h3 class="text-base font-semibold text-foreground">Workspace Configuration</h3>
                                    <p class="text-xs text-muted-foreground text-pretty">
                                        Choose your workspace organization name and dedicated web address.
                                    </p>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="workspace_name">{{ $t('tenant.workspace_name') || 'Workspace Name' }}</Label>
                                    <Input
                                        id="workspace_name"
                                        type="text"
                                        required
                                        autofocus
                                        :tabindex="1"
                                        name="workspace_name"
                                        :value="workspaceName"
                                        @input="onWorkspaceNameChange"
                                        @keydown.enter.prevent="goToNextStep"
                                        placeholder="e.g., Acme Dynamics"
                                    />
                                    <InputError :message="errors.workspace_name" />
                                </div>

                                <div class="grid gap-2">
                                    <div class="flex items-center justify-between">
                                        <Label for="subdomain">{{ $t('auth.workspace_url') || 'Workspace Subdomain' }}</Label>
                                        <span v-if="subdomain" class="text-[11px] font-mono text-muted-foreground truncate max-w-[220px]">
                                            https://<strong class="text-foreground">{{ subdomain }}</strong>.{{ centralDomain }}
                                        </span>
                                    </div>
                                    <div class="flex h-9 w-full rounded-md border border-input bg-transparent shadow-xs transition-[color,box-shadow] focus-within:ring-1 focus-within:ring-ring focus-within:border-ring">
                                        <input
                                            id="subdomain"
                                            name="subdomain"
                                            type="text"
                                            required
                                            :tabindex="2"
                                            :value="subdomain"
                                            @input="onSubdomainInput"
                                            @keydown.enter.prevent="goToNextStep"
                                            placeholder="acme"
                                            class="flex-1 bg-transparent px-3 py-1 text-sm outline-none placeholder:text-muted-foreground"
                                        />
                                        <span class="inline-flex items-center rounded-r-md border-l border-input bg-muted/40 px-3 text-xs text-muted-foreground tabular-nums select-none">
                                            .{{ centralDomain }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-muted-foreground text-pretty">
                                        {{ $t('auth.workspace_url_desc') || 'Your team will access your workspace at this unique address.' }}
                                    </p>
                                    <InputError :message="errors.subdomain || step1Error" />
                                </div>

                                <!-- Step 1 CTA Button -->
                                <div class="pt-3">
                                    <Button
                                        type="button"
                                        @click="goToNextStep"
                                        class="w-full h-10 font-semibold cursor-pointer"
                                        :tabindex="3"
                                    >
                                        <span>Continue to Admin Credentials</span>
                                        <ArrowRight class="ml-1.5 size-4" />
                                    </Button>
                                </div>
                            </template>
                        </div>

                        <!-- ============================================== -->
                        <!-- STEP 2: Administrator Credentials             -->
                        <!-- ============================================== -->
                        <div v-show="!isCentral || currentStep === 2" class="space-y-4">
                            <!-- Workspace Summary Pill on Step 2 -->
                            <div v-if="isCentral" class="flex items-center justify-between rounded-xl border border-border bg-muted/30 px-3.5 py-2.5 text-xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <Building2 class="size-4 text-indigo-500 shrink-0" />
                                    <span class="font-medium text-foreground truncate">{{ workspaceName || 'Workspace' }}</span>
                                    <span class="font-mono text-muted-foreground text-[11px] truncate">({{ subdomain }}.{{ centralDomain }})</span>
                                </div>
                                <button
                                    type="button"
                                    @click="goToPrevStep"
                                    class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 cursor-pointer shrink-0 ml-2"
                                >
                                    Change
                                </button>
                            </div>

                            <div class="space-y-1 pb-1">
                                <h3 class="text-base font-semibold text-foreground">
                                    {{ isCentral ? ($t('auth.admin_account') || 'Administrator Credentials') : 'Account Details' }}
                                </h3>
                                <p class="text-xs text-muted-foreground text-pretty">
                                    {{ isCentral ? 'Create your owner login to manage the workspace and invite teammates.' : 'Enter your credentials to join your workspace.' }}
                                </p>
                            </div>

                            <div class="grid gap-2">
                                <Label for="name">{{ $t('common.name') || 'Full Name' }}</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    :autofocus="!isCentral || currentStep === 2"
                                    :tabindex="4"
                                    autocomplete="name"
                                    name="name"
                                    :placeholder="$t('common.name') || 'Full name'"
                                />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">{{ $t('auth.email') || 'Work Email' }}</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    :tabindex="5"
                                    autocomplete="email"
                                    name="email"
                                    placeholder="alice@example.com"
                                />
                                <InputError :message="errors.email" />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="password">{{ $t('auth.password') || 'Password' }}</Label>
                                    <PasswordInput
                                        id="password"
                                        required
                                        :tabindex="6"
                                        autocomplete="new-password"
                                        name="password"
                                        :placeholder="$t('auth.password') || 'Password'"
                                    />
                                    <InputError :message="errors.password" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="password_confirmation">{{ $t('auth.confirm_password') || 'Confirm Password' }}</Label>
                                    <PasswordInput
                                        id="password_confirmation"
                                        required
                                        :tabindex="7"
                                        autocomplete="new-password"
                                        name="password_confirmation"
                                        :placeholder="$t('auth.confirm_password') || 'Confirm Password'"
                                    />
                                    <InputError :message="errors.password_confirmation" />
                                </div>
                            </div>

                            <!-- Step 2 Navigation & Submission -->
                            <div class="pt-3 space-y-3">
                                <div class="flex items-center gap-3">
                                    <Button
                                        v-if="isCentral"
                                        type="button"
                                        variant="outline"
                                        @click="goToPrevStep"
                                        class="h-10 px-4 font-medium cursor-pointer"
                                    >
                                        <ArrowLeft class="mr-1.5 size-4" />
                                        <span>Back</span>
                                    </Button>

                                    <Button
                                        type="submit"
                                        class="flex-1 h-10 font-semibold cursor-pointer"
                                        :tabindex="8"
                                        :disabled="processing"
                                        data-test="register-user-button"
                                    >
                                        <Spinner v-if="processing" />
                                        <span>{{ isCentral ? ($t('auth.create_workspace_button') || 'Create Workspace & Launch') : $t('auth.sign_up') }}</span>
                                        <ArrowRight v-if="!processing" class="ml-1.5 size-4" />
                                    </Button>
                                </div>

                                <p class="text-[11px] text-center text-muted-foreground text-pretty">
                                    By creating an account, you agree to the Terms of Service and Privacy Policy.
                                </p>
                            </div>
                        </div>

                        <!-- Already Registered Link -->
                        <div class="border-t border-border pt-4 text-center text-sm text-muted-foreground">
                            {{ $t('auth.already_registered') || 'Already have an account?' }}
                            <TextLink
                                :href="login()"
                                class="font-medium text-foreground underline underline-offset-4 hover:text-primary ml-1"
                                :tabindex="9"
                            >
                                {{ $t('auth.sign_in') || 'Sign In' }}
                            </TextLink>
                        </div>
                    </Form>
                </div>
            </div>
        </div>
    </div>
</template>
