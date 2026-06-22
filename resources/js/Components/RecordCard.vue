<script setup>
defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});
</script>

<template>
    <article class="rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-900 p-4 shadow-soft active:bg-slate-50/80 dark:active:bg-slate-800/80 transition-colors">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <h3 v-if="title" class="font-semibold text-slate-900 dark:text-slate-100 leading-snug">{{ title }}</h3>
                <p v-if="subtitle" class="text-sm text-slate-500 dark:text-slate-400 mt-0.5 truncate">{{ subtitle }}</p>
            </div>
            <div v-if="$slots.badge" class="shrink-0">
                <slot name="badge" />
            </div>
        </div>
        <dl v-if="$slots.meta" class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
            <slot name="meta" />
        </dl>
        <div v-if="$slots.actions" class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex flex-wrap gap-2">
            <slot name="actions" />
        </div>
    </article>
</template>
