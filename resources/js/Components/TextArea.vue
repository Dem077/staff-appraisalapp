<script setup>
defineProps({
    modelValue: { type: String, default: '' },
    label: { type: String, default: '' },
    error: { type: String, default: '' },
    rows: { type: Number, default: 3 },
    placeholder: { type: String, default: '' },
    inputClass: { type: String, default: '' },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div>
        <label v-if="label" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ label }}</label>
        <textarea
            :value="modelValue"
            :rows="rows"
            :placeholder="placeholder"
            class="input-field resize-y min-h-[88px]"
            :class="[inputClass, error ? 'border-red-300 dark:border-red-700' : '']"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <p v-if="error" class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ error }}</p>
    </div>
</template>
