<script setup>
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import TextArea from '@/Components/TextArea.vue';
import { useScoreValidation } from '@/composables/useScoreValidation';
import { ref } from 'vue';

const props = defineProps({
    staff: Object,
    ratingScale: Array,
    appraisalData: Array,
    mode: { type: String, default: 'staff' },
    form: Object,
    commentLabel: String,
    commentField: String,
});

const emit = defineEmits(['submit']);

const scaleOpen = ref(false);
const { validationMessage, clearMissing, isMissing, validateScores, applyServerErrors } = useScoreValidation();

const scoreField = props.mode === 'staff' ? 'self_score' : 'supervisor_score';

function scoreIndex(scores, questionId) {
    return scores.findIndex((s) => s.question_id === questionId);
}

const accent = (mode) => (mode === 'staff'
    ? 'peer-checked:border-brand-500 peer-checked:bg-brand-500 peer-checked:text-white peer-checked:shadow-md peer-checked:shadow-brand-500/25 hover:border-brand-300 active:scale-95'
    : 'peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:text-white peer-checked:shadow-md peer-checked:shadow-blue-500/25 hover:border-blue-300 active:scale-95');

function indicatorScore(scores, questionId) {
    const score = scores[scoreIndex(scores, questionId)];
    if (!score) return null;
    return props.mode === 'staff' ? score.self_score : score.supervisor_score;
}

function indicatorClasses(questionId) {
    if (isMissing(questionId)) {
        return 'border-red-400 dark:border-red-500 ring-2 ring-red-200 dark:ring-red-900/60';
    }

    return indicatorScore(props.form.scores, questionId)
        ? 'border-brand-200 dark:border-brand-700 shadow-sm ring-1 ring-brand-100/80 dark:ring-brand-800/80'
        : 'border-slate-200 dark:border-slate-700';
}

function handleScoreInput(questionId) {
    clearMissing(questionId);
}

function handleSubmit() {
    if (! validateScores(props.form.scores, scoreField)) {
        return;
    }

    emit('submit');
}

function handleServerErrors(errors) {
    applyServerErrors(errors, props.form.scores);
}

defineExpose({ handleServerErrors });
</script>

<template>
    <div class="pb-28 sm:pb-12">
        <Card class="mb-5 sm:mb-6 !p-4 sm:!p-5 border-slate-200 dark:border-slate-700/80 shadow-sm">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-sm">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-100 to-teal-100 dark:from-brand-900 dark:to-teal-900 flex items-center justify-center shrink-0">
                        <span class="text-sm font-bold text-brand-700 dark:text-brand-300">{{ staff.name?.charAt(0) }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-900 dark:text-slate-100 truncate">{{ staff.name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ staff.emp_no }} · {{ staff.designation }}</p>
                    </div>
                </div>
                <div v-if="staff.department" class="text-xs text-slate-500 dark:text-slate-400 sm:ml-auto">
                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ staff.department }}</span>
                </div>
            </div>
        </Card>

        <Card class="mb-6 sm:mb-8 bg-white dark:bg-slate-800 border-brand-100/80 dark:border-brand-800/80 shadow-sm !p-4 sm:!p-5">
            <button
                type="button"
                class="w-full flex items-center justify-between text-sm font-semibold text-slate-900 dark:text-slate-100"
                @click="scaleOpen = !scaleOpen"
            >
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand-500" />
                    Rating scale (1–5)
                </span>
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 transition-transform shrink-0" :class="scaleOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div
                class="grid gap-2 sm:grid-cols-1"
                :class="scaleOpen ? 'mt-4' : 'hidden sm:grid sm:mt-4'"
            >
                <div
                    v-for="item in ratingScale"
                    :key="item.label"
                    class="flex gap-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 px-3 py-2.5 text-sm"
                >
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-bold text-brand-700 shrink-0 shadow-sm">{{ item.label }}</span>
                    <span class="text-slate-600 dark:text-slate-400 pt-1 leading-snug">{{ item.description }}</span>
                </div>
            </div>
        </Card>

        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 sm:mb-5 text-center sm:text-left">
            Rate each indicator using the scale above
        </p>

        <p v-if="validationMessage" class="alert-error mb-4 sm:mb-5" role="alert">
            {{ validationMessage }}
        </p>

        <form class="space-y-5 sm:space-y-6" novalidate @submit.prevent="handleSubmit">
            <Card
                v-for="(category, ci) in appraisalData"
                :key="category.name"
                :padding="false"
                class="overflow-hidden shadow-card border-slate-200 dark:border-slate-700/80"
            >
                <div class="px-4 sm:px-5 py-3.5 sm:py-4 bg-gradient-to-r from-brand-600 to-teal-600 text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-white dark:bg-slate-800/20 text-sm font-bold shrink-0">{{ ci + 1 }}</span>
                    <h3 class="font-semibold text-base leading-snug">{{ category.name }}</h3>
                </div>
                <div class="p-4 sm:p-5 space-y-6">
                    <div v-for="behavior in category.keyBehaviors" :key="behavior.name">
                        <h4 class="text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-3">{{ behavior.name }}</h4>
                        <div class="space-y-4">
                            <div
                                v-for="indicator in behavior.indicators"
                                :key="indicator.question_id"
                                :data-indicator-id="indicator.question_id"
                                class="rounded-2xl border bg-white dark:bg-slate-800 p-4 sm:p-5 transition-shadow"
                                :class="indicatorClasses(indicator.question_id)"
                            >
                                <p class="fill-indicator-text font-medium text-slate-800 dark:text-slate-200">{{ indicator.text }}</p>
                                <p v-if="indicator.dhivehi_text" class="fill-indicator-text font-thaana text-slate-600 dark:text-slate-400 mt-2.5">{{ indicator.dhivehi_text }}</p>

                                <template v-if="mode === 'staff'">
                                    <p class="text-xs font-medium text-slate-400 dark:text-slate-500 mt-4 mb-2">Your rating</p>
                                    <div class="grid grid-cols-5 gap-2">
                                        <label v-for="n in 5" :key="n" class="min-w-0">
                                            <input
                                                v-model="form.scores[scoreIndex(form.scores, indicator.question_id)].self_score"
                                                type="radio"
                                                :value="n"
                                                class="sr-only peer"
                                                @change="handleScoreInput(indicator.question_id)"
                                            />
                                            <span
                                                class="flex items-center justify-center h-12 sm:h-11 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 font-bold text-base cursor-pointer transition-all duration-150 select-none touch-manipulation"
                                                :class="accent('staff')"
                                            >{{ n }}</span>
                                        </label>
                                    </div>
                                </template>

                                <template v-else>
                                    <p v-if="indicator.self_score" class="inline-flex mt-4 text-xs font-semibold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg">
                                        Staff rated: {{ indicator.self_score }}/5
                                    </p>
                                    <p class="text-xs font-medium text-slate-400 dark:text-slate-500 mt-4 mb-2">Supervisor rating</p>
                                    <div class="grid grid-cols-5 gap-2">
                                        <label v-for="n in 5" :key="n" class="min-w-0">
                                            <input
                                                v-model="form.scores[scoreIndex(form.scores, indicator.question_id)].supervisor_score"
                                                type="radio"
                                                :value="n"
                                                class="sr-only peer"
                                                @change="handleScoreInput(indicator.question_id)"
                                            />
                                            <span
                                                class="flex items-center justify-center h-12 sm:h-11 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 font-bold text-base cursor-pointer transition-all duration-150 select-none touch-manipulation"
                                                :class="accent('supervisor')"
                                            >{{ n }}</span>
                                        </label>
                                    </div>
                                    <input
                                        v-model="form.scores[scoreIndex(form.scores, indicator.question_id)].supervisor_comment"
                                        type="text"
                                        placeholder="Optional comment for this indicator"
                                        class="input-field mt-3"
                                    />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>

            <Card class="!p-4 sm:!p-6 border-slate-200 dark:border-slate-700/80 shadow-sm">
                <TextArea v-model="form[commentField]" :label="commentLabel" :rows="4" placeholder="Share any additional comments..." />
            </Card>

            <div class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200/80 dark:border-slate-700 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md px-4 py-3 safe-bottom sm:static sm:border-0 sm:bg-transparent sm:dark:bg-transparent sm:backdrop-blur-none sm:px-0 sm:py-0">
                <Button type="submit" size="lg" class="w-full shadow-elevated max-w-3xl mx-auto" :disabled="form.processing">
                    {{ form.processing ? 'Submitting...' : 'Submit appraisal' }}
                </Button>
            </div>
        </form>
    </div>
</template>
