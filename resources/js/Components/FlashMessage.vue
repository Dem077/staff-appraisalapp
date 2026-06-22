<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage();
const visible = ref(false);
const message = ref('');
const type = ref('success');

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            message.value = flash.success;
            type.value = 'success';
            visible.value = true;
        } else if (flash?.error) {
            message.value = flash.error;
            type.value = 'error';
            visible.value = true;
        }
    },
    { deep: true, immediate: true },
);

watch(visible, (show) => {
    if (show) {
        setTimeout(() => {
            visible.value = false;
        }, 5000);
    }
});

const styles = computed(() =>
    type.value === 'error'
        ? 'bg-red-50 border-red-200 text-red-800 dark:bg-red-950 dark:border-red-800 dark:text-red-200'
        : 'bg-brand-50 border-brand-200 text-brand-900 dark:bg-brand-950 dark:border-brand-800 dark:text-brand-200',
);
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <div
            v-if="visible"
            class="fixed top-4 left-4 right-4 sm:left-auto sm:right-4 z-[60] max-w-sm sm:w-auto flex items-start gap-3 px-4 py-3 rounded-xl border shadow-elevated"
            style="top: max(1rem, env(safe-area-inset-top));"
            :class="styles"
        >
            <svg v-if="type === 'success'" class="w-5 h-5 shrink-0 mt-0.5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else class="w-5 h-5 shrink-0 mt-0.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium flex-1">{{ message }}</p>
            <button type="button" class="shrink-0 opacity-60 hover:opacity-100" @click="visible = false">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </Transition>
</template>
