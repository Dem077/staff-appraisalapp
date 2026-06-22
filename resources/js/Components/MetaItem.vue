<script setup>
defineProps({
    label: { type: String, required: true },
});
</script>

<template>
    <div>
        <dt class="text-xs text-slate-400 dark:text-slate-400">{{ label }}</dt>
        <dd class="font-medium text-slate-700 dark:text-slate-300 mt-0.5"><slot /></dd>
    </div>
</template>
