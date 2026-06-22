<script setup>
defineProps({
    label: String,
    value: [String, Number],
    tone: { type: String, default: 'brand' },
    icon: { type: String, default: 'chart' },
});

const tones = {
    brand: 'from-brand-500 to-brand-600 text-white',
    amber: 'from-amber-500 to-orange-500 text-white',
    blue: 'from-blue-500 to-indigo-500 text-white',
    slate: 'from-slate-600 to-slate-700 text-white',
};
</script>

<template>
    <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/80 shadow-soft p-5 animate-slide-up">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ label }}</p>
                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ value }}</p>
            </div>
            <div class="shrink-0 w-11 h-11 rounded-xl bg-gradient-to-br flex items-center justify-center shadow-sm" :class="tones[tone] || tones.brand">
                <slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </slot>
            </div>
        </div>
    </div>
</template>
