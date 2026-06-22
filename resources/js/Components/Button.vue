<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    href: { type: String, default: null },
    type: { type: String, default: 'button' },
    variant: { type: String, default: 'primary' },
    size: { type: String, default: 'md' },
    disabled: { type: Boolean, default: false },
    method: { type: String, default: 'get' },
    target: { type: String, default: null },
    external: { type: Boolean, default: false },
});

const classes = computed(() => {
    const base = 'inline-flex items-center justify-center font-medium rounded-xl transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-slate-900 disabled:opacity-50 disabled:pointer-events-none';

    const sizes = {
        sm: 'text-xs px-3 py-2 gap-1.5',
        md: 'text-sm px-4 py-2.5 gap-2',
        lg: 'text-sm px-5 py-3 gap-2',
    };

    const variants = {
        primary: 'bg-brand-600 text-white hover:bg-brand-700 focus:ring-brand-500 shadow-sm dark:focus:ring-offset-slate-900',
        secondary: 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 focus:ring-slate-300 dark:focus:ring-offset-slate-900',
        ghost: 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100 focus:ring-slate-300 dark:focus:ring-offset-slate-900',
        danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 dark:focus:ring-offset-slate-900',
    };

    return [base, sizes[props.size] || sizes.md, variants[props.variant] || variants.primary].join(' ');
});
</script>

<template>
    <a
        v-if="href && external"
        :href="href"
        :target="target || undefined"
        :rel="target === '_blank' ? 'noopener noreferrer' : undefined"
        :class="classes"
    >
        <slot />
    </a>
    <Link v-else-if="href" :href="href" :method="method" :class="classes" :disabled="disabled" :target="target">
        <slot />
    </Link>
    <button v-else :type="type" :class="classes" :disabled="disabled">
        <slot />
    </button>
</template>
