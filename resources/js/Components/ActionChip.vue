<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: { type: String, required: true },
    variant: { type: String, default: 'default' },
    newTab: { type: Boolean, default: false },
});

const variants = {
    default: 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700',
    primary: 'bg-brand-50 text-brand-700 hover:bg-brand-100 dark:bg-brand-950 dark:text-brand-300 dark:hover:bg-brand-900',
    warning: 'bg-amber-50 text-amber-800 hover:bg-amber-100 dark:bg-amber-950 dark:text-amber-300 dark:hover:bg-amber-900',
    info: 'bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-950 dark:text-blue-300 dark:hover:bg-blue-900',
};
</script>

<template>
    <Link
        :href="href"
        :target="newTab ? '_blank' : undefined"
        :rel="newTab ? 'noopener noreferrer' : undefined"
        class="inline-flex items-center justify-center px-3 py-2 sm:px-2.5 sm:py-1 rounded-lg text-xs font-semibold transition-colors min-h-[36px] sm:min-h-0"
        :class="variants[variant] || variants.default"
    >
        <slot />
    </Link>
</template>
