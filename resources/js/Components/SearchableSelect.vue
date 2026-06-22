<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: { type: [Number, String, null], default: null },
    label: { type: String, default: '' },
    hint: { type: String, default: '' },
    error: { type: String, default: '' },
    placeholder: { type: String, default: 'Search...' },
    emptyLabel: { type: String, default: 'No selection' },
    allowEmpty: { type: Boolean, default: true },
    options: { type: Array, default: () => [] },
    searchUrl: { type: String, default: '' },
    searchParams: { type: Object, default: () => ({}) },
    selectedOption: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue']);

const root = ref(null);
const open = ref(false);
const query = ref('');
const loading = ref(false);
const remoteOptions = ref([]);
let debounceTimer = null;

const resolvedSelection = computed(() => {
    if (!props.modelValue) {
        return null;
    }

    const match = [...props.options, ...remoteOptions.value].find(
        (option) => Number(option.id) === Number(props.modelValue),
    );

    if (match) {
        return match;
    }

    if (props.selectedOption && Number(props.selectedOption.id) === Number(props.modelValue)) {
        return props.selectedOption;
    }

    return { id: props.modelValue, label: `Selected (#${props.modelValue})` };
});

const filteredOptions = computed(() => {
    const source = props.searchUrl ? remoteOptions.value : props.options;
    const term = query.value.trim().toLowerCase();

    if (!term || props.searchUrl) {
        return source;
    }

    return source.filter((option) => option.label.toLowerCase().includes(term));
});

async function fetchRemoteOptions() {
    if (!props.searchUrl) {
        return;
    }

    loading.value = true;

    try {
        const { data } = await axios.get(props.searchUrl, {
            params: {
                q: query.value,
                ...props.searchParams,
            },
        });

        remoteOptions.value = data.options ?? [];
    } finally {
        loading.value = false;
    }
}

function scheduleFetch() {
    if (!props.searchUrl) {
        return;
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fetchRemoteOptions, 250);
}

function openDropdown() {
    open.value = true;
    query.value = '';

    if (props.searchUrl) {
        fetchRemoteOptions();
    }
}

function closeDropdown() {
    open.value = false;
    query.value = '';
}

function selectOption(option) {
    emit('update:modelValue', option ? option.id : null);
    closeDropdown();
}

function onDocumentClick(event) {
    if (!root.value?.contains(event.target)) {
        closeDropdown();
    }
}

watch(query, scheduleFetch);

onMounted(() => document.addEventListener('click', onDocumentClick));
onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick);
    clearTimeout(debounceTimer);
});
</script>

<template>
    <div ref="root" class="relative">
        <label v-if="label" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
            {{ label }}
        </label>

        <button
            type="button"
            class="input-field w-full flex items-center justify-between gap-3 text-left"
            :class="error ? 'border-red-300 dark:border-red-700' : ''"
            @click.stop="open ? closeDropdown() : openDropdown()"
        >
            <span class="truncate" :class="resolvedSelection ? 'text-slate-900 dark:text-slate-100' : 'text-slate-400 dark:text-slate-500'">
                {{ resolvedSelection?.label ?? emptyLabel }}
            </span>
            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div
            v-if="open"
            class="absolute z-20 mt-1.5 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg overflow-hidden"
            @click.stop
        >
            <div class="p-2 border-b border-slate-100 dark:border-slate-800">
                <input
                    v-model="query"
                    type="search"
                    :placeholder="placeholder"
                    class="input-field w-full"
                    autofocus
                />
            </div>

            <div class="max-h-56 overflow-y-auto">
                <button
                    v-if="allowEmpty"
                    type="button"
                    class="w-full px-4 py-2.5 text-left text-sm text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800"
                    @click="selectOption(null)"
                >
                    {{ emptyLabel }}
                </button>

                <p v-if="loading" class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">Searching...</p>
                <p v-else-if="filteredOptions.length === 0" class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                    No matches found.
                </p>

                <button
                    v-for="option in filteredOptions"
                    :key="option.id"
                    type="button"
                    class="w-full px-4 py-2.5 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-800"
                    :class="Number(modelValue) === Number(option.id)
                        ? 'bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-300 font-medium'
                        : 'text-slate-700 dark:text-slate-300'"
                    @click="selectOption(option)"
                >
                    {{ option.label }}
                </button>
            </div>
        </div>

        <p v-if="error" class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ error }}</p>
        <p v-else-if="hint" class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ hint }}</p>
    </div>
</template>
