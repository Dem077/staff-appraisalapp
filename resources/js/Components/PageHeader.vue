<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6 sm:mb-8">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ title }}</h1>
            <p v-if="description" class="mt-1.5 text-sm text-slate-500 dark:text-slate-400 max-w-2xl">{{ description }}</p>
        </div>
        <div v-if="$slots.actions" class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2 w-full sm:w-auto shrink-0">
            <slot name="actions" />
        </div>
    </div>
</template>
