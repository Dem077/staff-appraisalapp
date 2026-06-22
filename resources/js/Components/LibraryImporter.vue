<script setup>
import Button from '@/Components/Button.vue';
import { computed, ref } from 'vue';

const props = defineProps({
    library: { type: Object, required: true },
    existingCategoryIds: { type: Array, default: () => [] },
    formType: { type: String, default: '' },
    formLevel: { type: String, default: '' },
    types: { type: Object, default: () => ({}) },
    levels: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['import', 'close']);

const open = defineModel({ type: Boolean, default: false });
const search = ref('');
const copyMode = ref('link');
const selectedCategories = ref([]);
const selectedBehaviors = ref([]);
const selectedIndicators = ref([]);
const targetCategoryIndex = ref(0);

const categories = computed(() => props.library?.categories ?? []);

const scopedCategories = computed(() => {
    let cats = categories.value;
    if (props.formLevel) {
        cats = cats.filter((c) => c.level === props.formLevel);
    }
    if (props.formType) {
        cats = cats.filter((c) => c.form_type === props.formType);
    }
    return cats;
});

const needsFormScope = computed(() => !props.formType || !props.formLevel);

const filteredCategories = computed(() => {
    const q = search.value.trim().toLowerCase();
    const base = scopedCategories.value;
    if (!q) return base;

    return base
        .map((category) => {
            const behaviors = (category.behaviors ?? [])
                .map((behavior) => {
                    const indicators = (behavior.indicators ?? []).filter((indicator) =>
                        indicator.behavioral_indicators?.toLowerCase().includes(q)
                        || indicator.dhivehi_behavioral_indicators?.toLowerCase().includes(q),
                    );
                    const behaviorMatch = behavior.name?.toLowerCase().includes(q) || indicators.length;
                    if (!behaviorMatch) return null;
                    return { ...behavior, indicators: indicators.length ? indicators : behavior.indicators };
                })
                .filter(Boolean);

            const categoryMatch = category.name?.toLowerCase().includes(q) || behaviors.length;
            if (!categoryMatch) return null;
            return { ...category, behaviors };
        })
        .filter(Boolean);
});

function toggleCategory(id) {
    const idx = selectedCategories.value.indexOf(id);
    if (idx === -1) selectedCategories.value.push(id);
    else selectedCategories.value.splice(idx, 1);
}

function toggleBehavior(id) {
    const idx = selectedBehaviors.value.indexOf(id);
    if (idx === -1) selectedBehaviors.value.push(id);
    else selectedBehaviors.value.splice(idx, 1);
}

function toggleIndicator(id) {
    const idx = selectedIndicators.value.indexOf(id);
    if (idx === -1) selectedIndicators.value.push(id);
    else selectedIndicators.value.splice(idx, 1);
}

function isCategoryDisabled(category) {
    return props.existingCategoryIds.includes(category.id);
}

function confirmImport() {
    emit('import', {
        categoryIds: [...selectedCategories.value],
        behaviorIds: [...selectedBehaviors.value],
        indicatorIds: [...selectedIndicators.value],
        copyMode: copyMode.value,
        targetCategoryIndex: targetCategoryIndex.value,
    });
    selectedCategories.value = [];
    selectedBehaviors.value = [];
    selectedIndicators.value = [];
    open.value = false;
}
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="open = false" />
            <div class="relative w-full sm:max-w-2xl max-h-[90vh] bg-white dark:bg-slate-800 rounded-t-2xl sm:rounded-2xl shadow-elevated flex flex-col overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 shrink-0">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Import from library</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Reuse shared categories, behaviors, or individual indicators.</p>
                    <p v-if="needsFormScope" class="text-sm badge-amber border border-amber-100 dark:border-amber-800 rounded-lg px-3 py-2 mt-3">
                        Select form type and level above before importing — only matching library items are shown.
                    </p>
                    <p v-else class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                        Showing items for {{ types[formType] ?? formType }} · {{ levels[formLevel] ?? formLevel }}
                    </p>
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search library..."
                        class="input-field mt-3"
                    />
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <label class="flex items-center gap-2 cursor-pointer text-slate-700 dark:text-slate-300">
                            <input v-model="copyMode" type="radio" value="link" class="text-brand-600" />
                            Link categories (shared edits)
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-slate-700 dark:text-slate-300">
                            <input v-model="copyMode" type="radio" value="copy" class="text-brand-600" />
                            Copy as new items
                        </label>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    <p v-if="needsFormScope" class="text-sm text-slate-500 dark:text-slate-400 text-center py-8">
                        Choose a form type and level in Form details to filter the library.
                    </p>
                    <p v-else-if="!filteredCategories.length" class="text-sm text-slate-500 dark:text-slate-400 text-center py-8">
                        No library items match this form type and level. Add matching content under Content Library first.
                    </p>

                    <div v-for="category in filteredCategories" :key="category.library_id ?? category.id" class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <label
                            class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-950 cursor-pointer"
                            :class="isCategoryDisabled(category) ? 'opacity-50' : ''"
                        >
                            <input
                                type="checkbox"
                                :disabled="isCategoryDisabled(category)"
                                :checked="selectedCategories.includes(category.id)"
                                class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-brand-600"
                                @change="toggleCategory(category.id)"
                            />
                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ category.name }}</span>
                            <span v-if="isCategoryDisabled(category)" class="text-xs text-slate-400 dark:text-slate-500 ml-auto">Already linked</span>
                        </label>
                        <div class="divide-y divide-slate-100 dark:divide-slate-700">
                            <div v-for="behavior in category.behaviors" :key="behavior.library_id ?? behavior.id" class="px-4 py-2">
                                <label class="flex items-center gap-3 cursor-pointer py-1">
                                    <input
                                        type="checkbox"
                                        :checked="selectedBehaviors.includes(behavior.id)"
                                        class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-brand-600"
                                        @change="toggleBehavior(behavior.id)"
                                    />
                                    <span class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ behavior.name }}</span>
                                </label>
                                <div class="ml-8 space-y-1 pb-2">
                                    <label
                                        v-for="indicator in behavior.indicators"
                                        :key="indicator.library_id ?? indicator.id"
                                        class="flex items-start gap-2 cursor-pointer py-1"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="selectedIndicators.includes(indicator.id)"
                                            class="rounded border-slate-300 text-brand-600 mt-1 shrink-0"
                                            @change="toggleIndicator(indicator.id)"
                                        />
                                        <span class="text-sm text-slate-600 dark:text-slate-400 line-clamp-2">{{ indicator.behavioral_indicators }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row gap-3 shrink-0 safe-bottom">
                    <div v-if="selectedBehaviors.length || selectedIndicators.length" class="text-xs text-slate-500 dark:text-slate-400 sm:flex-1 sm:self-center">
                        Behaviors/indicators copy into category #{{ targetCategoryIndex + 1 }}
                        <input v-model.number="targetCategoryIndex" type="number" min="0" class="input-field w-16 ml-1 inline-block py-1" />
                    </div>
                    <div class="flex gap-2 sm:ml-auto">
                        <Button variant="secondary" @click="open = false">Cancel</Button>
                        <Button @click="confirmImport">Import selected</Button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
