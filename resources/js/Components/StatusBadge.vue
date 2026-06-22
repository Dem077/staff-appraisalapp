<script setup>
defineProps({
    status: { type: String, required: true },
    label: { type: String, default: '' },
});

const colors = {
    warning: 'bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-950 dark:text-amber-300 dark:ring-amber-500/30',
    gray: 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 ring-slate-500/15 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-500/30',
    primary: 'bg-blue-50 text-blue-800 ring-blue-600/20 dark:bg-blue-950 dark:text-blue-300 dark:ring-blue-500/30',
    success: 'bg-brand-50 text-brand-800 ring-brand-600/20 dark:bg-brand-950 dark:text-brand-300 dark:ring-brand-500/30',
};
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
        :class="colors[status] || colors.gray"
    >
        {{ label || status }}
    </span>
</template>
