<script setup lang="ts">
import { computed, ref } from 'vue';
import { CloudUpload, Eye, Image as ImageIcon, Link as LinkIcon, Trash2, X } from 'lucide-vue-next';

interface Props {
    label?: string;
    helperText?: string;
    placeholder?: string;
    accept?: string;
    maxSizeMb?: number;
    previewVariant?: 'light' | 'dark' | 'checkerboard' | 'auto';
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    label: '',
    helperText: '',
    placeholder: 'https://... or /images/logo.png',
    accept: 'image/png,image/jpeg,image/svg+xml,image/webp,image/gif',
    maxSizeMb: 5,
    previewVariant: 'auto',
    disabled: false,
});

const model = defineModel<string | null>({ default: '' });
const isDragging = ref(false);
const showUrlInput = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const errorMessage = ref<string | null>(null);

const hasValue = computed(() => !!model.value && model.value.trim().length > 0);

const triggerFileInput = () => {
    if (!props.disabled && fileInput.value) {
        fileInput.value.click();
    }
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        processFile(target.files[0]);
    }
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    if (props.disabled) return;

    if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
        processFile(event.dataTransfer.files[0]);
    }
};

const processFile = (file: File) => {
    errorMessage.value = null;

    if (file.size > props.maxSizeMb * 1024 * 1024) {
        errorMessage.value = `File exceeds maximum size of ${props.maxSizeMb}MB`;
        return;
    }

    // Convert to Data URL for instant live preview and storage payload
    const reader = new FileReader();
    reader.onload = (e) => {
        if (e.target?.result) {
            model.value = e.target.result as string;
        }
    };
    reader.readAsDataURL(file);
};

const clearValue = () => {
    model.value = '';
    errorMessage.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const previewBgClass = computed(() => {
    switch (props.previewVariant) {
        case 'dark':
            return 'bg-neutral-950 border-neutral-800 text-white';
        case 'light':
            return 'bg-white border-slate-200 text-slate-900';
        case 'checkerboard':
            return 'bg-neutral-100 dark:bg-neutral-900 border-border';
        default:
            return 'bg-muted/40 border-border';
    }
});
</script>

<template>
    <div class="space-y-2">
        <div v-if="label || $slots.label" class="flex items-center justify-between">
            <label class="block text-xs font-semibold text-foreground">
                <slot name="label">{{ label }}</slot>
            </label>
            <button
                v-if="!disabled"
                type="button"
                @click="showUrlInput = !showUrlInput"
                class="inline-flex items-center gap-1 text-[11px] text-muted-foreground hover:text-indigo-600 dark:hover:text-indigo-400 transition cursor-pointer"
            >
                <LinkIcon class="w-3 h-3" />
                <span>{{ showUrlInput ? 'Switch to Upload' : 'Enter URL / Path' }}</span>
            </button>
        </div>

        <!-- Hidden Native File Input -->
        <input
            ref="fileInput"
            type="file"
            :accept="accept"
            :disabled="disabled"
            @change="handleFileSelect"
            class="hidden"
        />

        <!-- URL / Path Mode -->
        <div v-if="showUrlInput" class="space-y-2">
            <div class="flex items-center gap-2">
                <input
                    v-model="model"
                    type="text"
                    :disabled="disabled"
                    :placeholder="placeholder"
                    class="w-full px-3 py-2 border rounded-lg text-sm bg-background text-foreground font-mono focus:ring-2 focus:ring-indigo-500 transition disabled:opacity-50"
                />
                <button
                    v-if="hasValue"
                    type="button"
                    @click="clearValue"
                    class="p-2 text-muted-foreground hover:text-red-500 rounded-lg border hover:bg-red-50 dark:hover:bg-red-950/40 transition cursor-pointer"
                    title="Clear"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- Dropzone / Upload Area -->
        <div v-else>
            <div
                v-if="!hasValue"
                @click="triggerFileInput"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
                :class="[
                    'flex flex-col items-center justify-center p-5 border-2 border-dashed rounded-xl cursor-pointer transition text-center group',
                    isDragging
                        ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/30'
                        : 'border-border/80 hover:border-indigo-400 hover:bg-neutral-50/60 dark:hover:bg-neutral-900/40 bg-muted/20'
                ]"
            >
                <div class="p-2.5 rounded-full bg-muted/80 text-muted-foreground group-hover:text-indigo-600 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-950/60 transition mb-2">
                    <CloudUpload class="w-5 h-5" />
                </div>
                <p class="text-xs font-semibold text-foreground">
                    Click to browse <span class="font-normal text-muted-foreground">or drag and drop file</span>
                </p>
                <p class="text-[11px] text-muted-foreground mt-0.5">
                    PNG, SVG, WEBP, or JPG (max {{ maxSizeMb }}MB)
                </p>
            </div>

            <!-- Uploaded File Preview Card -->
            <div
                v-else
                class="flex items-center justify-between gap-3 p-3 rounded-xl border"
                :class="previewBgClass"
            >
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="h-10 w-16 sm:w-20 shrink-0 rounded-lg border p-1 flex items-center justify-center overflow-hidden bg-white/10">
                        <img
                            :src="model!"
                            alt="Preview"
                            class="max-h-full max-w-full object-contain"
                            @error="(e) => (e.target as HTMLElement).style.display = 'none'"
                        />
                    </div>
                    <div class="truncate text-left">
                        <p class="text-xs font-medium truncate">
                            {{ model?.startsWith('data:') ? 'Custom Uploaded File' : model }}
                        </p>
                        <p class="text-[10px] text-muted-foreground">Image asset ready</p>
                    </div>
                </div>

                <div class="flex items-center gap-1 shrink-0">
                    <button
                        type="button"
                        @click="triggerFileInput"
                        class="p-1.5 text-xs text-muted-foreground hover:text-foreground rounded-md hover:bg-muted/80 transition cursor-pointer"
                        title="Replace file"
                    >
                        Replace
                    </button>
                    <button
                        type="button"
                        @click="clearValue"
                        class="p-1.5 text-muted-foreground hover:text-red-500 rounded-md hover:bg-red-50 dark:hover:bg-red-950/40 transition cursor-pointer"
                        title="Delete image"
                    >
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Helper Text or Error -->
        <p v-if="errorMessage" class="text-xs text-red-500 font-medium">
            {{ errorMessage }}
        </p>
        <p v-else-if="helperText" class="text-[11px] text-muted-foreground">
            {{ helperText }}
        </p>
    </div>
</template>
