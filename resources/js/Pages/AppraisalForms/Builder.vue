<script setup>
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import LibraryImporter from '@/Components/LibraryImporter.vue';
import PageHeader from '@/Components/PageHeader.vue';
import TextArea from '@/Components/TextArea.vue';
import TextInput from '@/Components/TextInput.vue';
import ThaanaInput from '@/Components/ThaanaInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed, ref } from 'vue';

const props = defineProps({
    form: { type: Object, default: null },
    library: { type: Object, default: () => ({ categories: [] }) },
    libraryOnly: { type: Boolean, default: false },
    types: Object,
    levels: Object,
});

const showLibraryImport = ref(false);

const expandedCategories = ref(props.form?.categories?.map((_, i) => i) ?? [0]);

const data = useForm({
    name: props.form?.name ?? '',
    type: props.form?.type ?? '',
    level: props.form?.level ?? '',
    description: props.form?.description ?? '',
    is_active: props.form?.is_active ?? true,
    categories: props.form?.categories?.length
        ? JSON.parse(JSON.stringify(props.form.categories))
        : [emptyCategory()],
});

const totals = computed(() => {
    let behaviors = 0;
    let indicators = 0;
    data.categories.forEach((cat) => {
        behaviors += cat.behaviors?.length ?? 0;
        cat.behaviors?.forEach((b) => {
            indicators += b.indicators?.length ?? 0;
        });
    });
    return { categories: data.categories.length, behaviors, indicators };
});

const linkedCategoryIds = computed(() =>
    data.categories.filter((c) => c.linked && c.id).map((c) => c.id),
);

function emptyCategory() {
    if (props.libraryOnly) {
        return { name: '', level: 'level_1', form_type: 'mid-year', behaviors: [emptyBehavior()] };
    }
    return { name: '', behaviors: [emptyBehavior()] };
}

function levelLabel(value) {
    return props.levels?.[value] ?? value;
}

function typeLabel(value) {
    return props.types?.[value] ?? value;
}

function emptyBehavior() {
    return { name: '', indicators: [emptyIndicator()] };
}

function emptyIndicator() {
    return { behavioral_indicators: '', dhivehi_behavioral_indicators: '' };
}

function addCategory() {
    data.categories.push(emptyCategory());
    expandedCategories.value.push(data.categories.length - 1);
}

function removeCategory(index) {
    if (data.categories.length === 1) return;
    data.categories.splice(index, 1);
}

function moveCategory(index, direction) {
    const target = index + direction;
    if (target < 0 || target >= data.categories.length) return;
    const [item] = data.categories.splice(index, 1);
    data.categories.splice(target, 0, item);
}

function addBehavior(categoryIndex) {
    data.categories[categoryIndex].behaviors.push(emptyBehavior());
}

function removeBehavior(categoryIndex, behaviorIndex) {
    const behaviors = data.categories[categoryIndex].behaviors;
    if (behaviors.length === 1) return;
    behaviors.splice(behaviorIndex, 1);
}

function addIndicator(categoryIndex, behaviorIndex) {
    data.categories[categoryIndex].behaviors[behaviorIndex].indicators.push(emptyIndicator());
}

function removeIndicator(categoryIndex, behaviorIndex, indicatorIndex) {
    const indicators = data.categories[categoryIndex].behaviors[behaviorIndex].indicators;
    const indicator = indicators[indicatorIndex];
    if (indicator?.locked) return;
    if (indicators.length === 1) return;
    indicators.splice(indicatorIndex, 1);
}

function toggleCategory(index) {
    const pos = expandedCategories.value.indexOf(index);
    if (pos === -1) expandedCategories.value.push(index);
    else expandedCategories.value.splice(pos, 1);
}

function isExpanded(index) {
    return expandedCategories.value.includes(index);
}

function submit() {
    if (props.libraryOnly) {
        data.transform((payload) => ({ categories: payload.categories }))
            .put('/appraisal-library', { preserveScroll: true });
        return;
    }
    if (props.form?.id) {
        data.put(`/appraisal-forms/${props.form.id}`);
    } else {
        data.post('/appraisal-forms');
    }
}

function cloneIndicator(indicator) {
    const copy = JSON.parse(JSON.stringify(indicator));
    delete copy.id;
    delete copy.locked;
    return copy;
}

function cloneBehavior(behavior) {
    return {
        name: behavior.name,
        indicators: (behavior.indicators ?? []).map(cloneIndicator),
    };
}

function cloneCategory(category) {
    return {
        name: category.name,
        behaviors: (category.behaviors ?? []).map(cloneBehavior),
    };
}

function handleLibraryImport({ categoryIds, behaviorIds, indicatorIds, copyMode, targetCategoryIndex }) {
    const libCats = (props.library?.categories ?? []).filter((c) => {
        if (data.level && c.level !== data.level) return false;
        if (data.type && c.form_type !== data.type) return false;
        return true;
    });

    categoryIds.forEach((catId) => {
        const cat = libCats.find((c) => c.id === catId);
        if (!cat) return;
        if (copyMode === 'copy') {
            data.categories.push(cloneCategory(cat));
        } else {
            data.categories.push(JSON.parse(JSON.stringify({ ...cat, linked: true })));
        }
        expandedCategories.value.push(data.categories.length - 1);
    });

    if (!behaviorIds.length && !indicatorIds.length) return;

    const targetIdx = Math.max(0, Math.min(targetCategoryIndex, data.categories.length - 1));
    const target = data.categories[targetIdx];
    if (!target.behaviors) target.behaviors = [];

    behaviorIds.forEach((bId) => {
        for (const cat of libCats) {
            const behavior = cat.behaviors?.find((b) => b.id === bId);
            if (behavior) {
                target.behaviors.push(cloneBehavior(behavior));
                break;
            }
        }
    });

    indicatorIds.forEach((iId) => {
        for (const cat of libCats) {
            for (const behavior of cat.behaviors ?? []) {
                const indicator = behavior.indicators?.find((i) => i.id === iId);
                if (!indicator) continue;

                let behaviorTarget = target.behaviors.find((b) => b.name === behavior.name);
                if (!behaviorTarget) {
                    behaviorTarget = { name: behavior.name, indicators: [] };
                    target.behaviors.push(behaviorTarget);
                }
                behaviorTarget.indicators.push(cloneIndicator(indicator));
                break;
            }
        }
    });
}
</script>

<template>
    <Head :title="libraryOnly ? 'Content Library' : (form ? 'Edit Form' : 'New Form')" />
    <AppLayout>
        <div class="page-shell max-w-4xl">
            <PageHeader
                :title="libraryOnly ? 'Content library' : (form ? 'Form builder' : 'Create appraisal form')"
                :description="libraryOnly
                    ? 'Build reusable categories, behaviors, and indicators. Import them into any appraisal form.'
                    : 'Build the full questionnaire in one place — categories, behaviors, and rating indicators.'"
            >
                <template #actions>
                    <div class="flex flex-wrap gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 rounded-xl px-3 py-2">
                        <span>{{ totals.categories }} categories</span>
                        <span>·</span>
                        <span>{{ totals.behaviors }} behaviors</span>
                        <span>·</span>
                        <span>{{ totals.indicators }} indicators</span>
                    </div>
                </template>
            </PageHeader>

            <form class="space-y-5" @submit.prevent="submit">
                <Card v-if="!libraryOnly" class="space-y-4">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Form details</h2>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Name (Thaana)</label>
                        <ThaanaInput v-model="data.name" required />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Type</label>
                            <select v-model="data.type" required class="input-field">
                                <option value="">Select type...</option>
                                <option v-for="(label, val) in types" :key="val" :value="val">{{ label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Level</label>
                            <select v-model="data.level" required class="input-field">
                                <option value="">Select level...</option>
                                <option v-for="(label, val) in levels" :key="val" :value="val">{{ label }}</option>
                            </select>
                        </div>
                    </div>
                    <TextArea v-model="data.description" label="Description" :rows="2" />
                    <label class="flex items-center gap-2.5 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input v-model="data.is_active" type="checkbox" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-brand-600 focus:ring-brand-500/30" />
                        Active — available for new assignments
                    </label>
                </Card>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ libraryOnly ? 'Library items' : 'Questionnaire structure' }}</h2>
                    <div class="flex flex-wrap gap-2">
                        <Button v-if="!libraryOnly" type="button" variant="secondary" size="sm" @click="showLibraryImport = true">Import from library</Button>
                        <Button type="button" variant="secondary" size="sm" @click="addCategory">+ Add category</Button>
                    </div>
                </div>

                <p v-if="data.errors.categories" class="alert-error">
                    {{ data.errors.categories }}
                </p>

                <div class="space-y-4">
                    <Card
                        v-for="(category, ci) in data.categories"
                        :key="category.id ?? `new-cat-${ci}`"
                        :padding="false"
                        class="overflow-hidden"
                    >
                        <div
                            class="flex items-center gap-2 px-4 py-3 bg-slate-50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 cursor-pointer select-none"
                            @click="toggleCategory(ci)"
                        >
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-100 text-brand-700 dark:bg-brand-900 dark:text-brand-300 text-xs font-bold shrink-0">
                                {{ ci + 1 }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100 truncate">{{ category.name || 'Untitled category' }}</p>
                                    <span v-if="category.linked" class="text-[10px] font-medium badge-blue px-2 py-0.5 rounded shrink-0">Shared library</span>
                                    <template v-if="libraryOnly && category.level">
                                        <span class="text-[10px] font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded shrink-0">{{ levelLabel(category.level) }}</span>
                                    </template>
                                    <template v-if="libraryOnly && category.form_type">
                                        <span class="text-[10px] font-medium badge-brand px-2 py-0.5 rounded shrink-0">{{ typeLabel(category.form_type) }}</span>
                                    </template>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ category.behaviors?.length ?? 0 }} behaviors</p>
                            </div>
                            <div class="flex items-center gap-1 shrink-0" @click.stop>
                                <button type="button" class="p-2 rounded-lg text-slate-400 dark:text-slate-500 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-600 dark:hover:text-slate-300" title="Move up" @click="moveCategory(ci, -1)">↑</button>
                                <button type="button" class="p-2 rounded-lg text-slate-400 dark:text-slate-500 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-600 dark:hover:text-slate-300" title="Move down" @click="moveCategory(ci, 1)">↓</button>
                                <button
                                    v-if="data.categories.length > 1"
                                    type="button"
                                    class="p-2 rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-950 hover:text-red-600 dark:hover:text-red-400"
                                    title="Remove category"
                                    @click="removeCategory(ci)"
                                >×</button>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 transition-transform shrink-0" :class="isExpanded(ci) ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>

                        <div v-show="isExpanded(ci)" class="p-4 sm:p-5 space-y-5">
                            <div v-if="libraryOnly" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Form type</label>
                                    <select v-model="category.form_type" required class="input-field">
                                        <option v-for="(label, val) in types" :key="val" :value="val">{{ label }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Level</label>
                                    <select v-model="category.level" required class="input-field">
                                        <option v-for="(label, val) in levels" :key="val" :value="val">{{ label }}</option>
                                    </select>
                                </div>
                            </div>
                            <TextInput v-model="category.name" label="Category name" placeholder="e.g. Core Competencies" required />

                            <div
                                v-for="(behavior, bi) in category.behaviors"
                                :key="behavior.id ?? `new-b-${ci}-${bi}`"
                                class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden"
                            >
                                <div class="flex items-center gap-2 px-3 py-2.5 bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-700">
                                    <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Behavior {{ bi + 1 }}</span>
                                    <button
                                        v-if="category.behaviors.length > 1"
                                        type="button"
                                        class="ml-auto text-xs text-red-500 hover:text-red-700"
                                        @click="removeBehavior(ci, bi)"
                                    >Remove</button>
                                </div>
                                <div class="p-3 sm:p-4 space-y-3">
                                    <TextInput v-model="behavior.name" label="Behavior name" placeholder="e.g. Communication" required />

                                    <div class="space-y-3">
                                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Rating indicators</p>
                                        <div
                                            v-for="(indicator, ii) in behavior.indicators"
                                            :key="indicator.id ?? `new-i-${ci}-${bi}-${ii}`"
                                            class="rounded-lg border border-slate-100 dark:border-slate-700 p-3 bg-slate-50/50 dark:bg-slate-800/30 space-y-3"
                                        >
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-xs font-medium text-slate-400 dark:text-slate-500">Indicator {{ ii + 1 }}</span>
                                                <span v-if="indicator.locked" class="text-[10px] font-medium badge-amber px-2 py-0.5 rounded">In use</span>
                                                <button
                                                    v-else-if="behavior.indicators.length > 1"
                                                    type="button"
                                                    class="text-xs text-red-500 hover:text-red-700"
                                                    @click="removeIndicator(ci, bi, ii)"
                                                >Remove</button>
                                            </div>
                                            <TextArea
                                                v-model="indicator.behavioral_indicators"
                                                label="Indicator (English)"
                                                :rows="3"
                                                input-class="indicator-textarea"
                                                required
                                            />
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Indicator (Dhivehi)</label>
                                                <ThaanaInput v-model="indicator.dhivehi_behavioral_indicators" multiline :rows="3" input-class="indicator-textarea" />
                                            </div>
                                        </div>
                                        <Button type="button" variant="ghost" size="sm" @click="addIndicator(ci, bi)">+ Add indicator</Button>
                                    </div>
                                </div>
                            </div>

                            <Button type="button" variant="secondary" size="sm" @click="addBehavior(ci)">+ Add behavior</Button>
                        </div>
                    </Card>
                </div>

                <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2 sticky-submit bg-slate-50/95 dark:bg-slate-900/95 backdrop-blur-sm -mx-4 px-4 sm:mx-0 sm:px-0 py-3 sm:py-0 sm:bg-transparent sm:dark:bg-transparent sm:backdrop-blur-none">
                    <Button type="submit" size="lg" class="w-full sm:w-auto" :disabled="data.processing">
                        {{ data.processing ? 'Saving...' : (libraryOnly ? 'Save library' : (form ? 'Save form' : 'Create form')) }}
                    </Button>
                    <Button :href="libraryOnly ? '/appraisal-forms' : '/appraisal-forms'" variant="secondary" class="w-full sm:w-auto">
                        {{ libraryOnly ? 'Back to forms' : 'Cancel' }}
                    </Button>
                </div>
            </form>

            <LibraryImporter
                v-if="!libraryOnly"
                v-model="showLibraryImport"
                :library="library"
                :form-type="data.type"
                :form-level="data.level"
                :types="types"
                :levels="levels"
                :existing-category-ids="linkedCategoryIds"
                @import="handleLibraryImport"
            />
        </div>
    </AppLayout>
</template>
