<script setup>
defineProps({
    padding: { type: Boolean, default: true },
    hover: { type: Boolean, default: false },
});
</script>

<template>
    <div
        class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-soft overflow-hidden"
        :class="[
            padding ? 'p-4 sm:p-6' : '',
            hover ? 'transition-all duration-200 hover:shadow-card hover:border-brand-200/60 dark:hover:border-brand-700/60' : '',
        ]"
    >
        <slot />
    </div>
</template>
