<script setup>
defineProps({
    tabs: {
        type: Array,
        required: true,
    },
    active: {
        type: String,
        default: 'all',
    },
});

const emit = defineEmits(['select']);
</script>

<template>
    <div class="border-b border-slate-200 dark:border-slate-800">
        <div class="flex gap-1 overflow-x-auto px-4 sm:px-6 scrollbar-none">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                class="shrink-0 inline-flex items-center gap-2 px-3 py-3 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap"
                :class="active === tab.key
                    ? 'border-brand-600 text-brand-700 dark:border-brand-400 dark:text-brand-300'
                    : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-600'"
                @click="emit('select', tab.key)"
            >
                {{ tab.label }}
                <span
                    v-if="tab.count !== undefined"
                    class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[11px] font-semibold"
                    :class="active === tab.key
                        ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/80 dark:text-brand-300'
                        : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'"
                >
                    {{ tab.count }}
                </span>
            </button>
        </div>
    </div>
</template>
