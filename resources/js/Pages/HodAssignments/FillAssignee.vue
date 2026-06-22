<script setup>
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import TextArea from '@/Components/TextArea.vue';
import FillLayout from '@/Layouts/FillLayout.vue';
import { useScoreValidation } from '@/composables/useScoreValidation';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    assignee: Object,
    hodAssignment: Object,
    ratingScale: Array,
    appraisalData: Array,
});

const form = useForm({ scores: [], assignee_comment: '' });
const scaleOpen = ref(false);
const { validationMessage, clearMissing, isMissing, validateScores, applyServerErrors } = useScoreValidation();

props.appraisalData.forEach((cat) => {
    cat.keyBehaviors.forEach((b) => {
        b.indicators.forEach((ind) => {
            form.scores.push({ question_id: ind.question_id, score: ind.score || '' });
        });
    });
});

function scoreIndex(questionId) {
    return form.scores.findIndex((s) => s.question_id === questionId);
}

const progress = computed(() => {
    if (!form.scores.length) return 0;
    const answered = form.scores.filter((s) => s.score !== '' && s.score != null).length;
    return Math.round((answered / form.scores.length) * 100);
});

function indicatorClasses(questionId) {
    if (isMissing(questionId)) {
        return 'border-red-400 dark:border-red-500 ring-2 ring-red-200 dark:ring-red-900/60';
    }

    return form.scores[scoreIndex(questionId)].score
        ? 'border-teal-200 dark:border-teal-700 shadow-sm ring-1 ring-teal-100/80 dark:ring-teal-800/80'
        : 'border-slate-200 dark:border-slate-700';
}

function handleSubmit() {
    if (! validateScores(form.scores, 'score')) {
        return;
    }

    form.post(`/hod-assignees/${props.assignee.id}/fill`, {
        preserveScroll: true,
        onError: (errors) => applyServerErrors(errors, form.scores),
    });
}
</script>

<template>
    <Head title="360 Feedback" />
    <FillLayout
        title="360° feedback"
        :subtitle="hodAssignment.form?.name"
        back-href="/hod-assignments"
        back-label="Back to HOD appraisals"
        :progress="progress"
    >
        <div class="pb-28 sm:pb-12">
            <Card class="mb-5 !p-4 sm:!p-5 border-slate-200 dark:border-slate-700/80 shadow-sm">
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-teal-50 text-teal-700 dark:bg-teal-950 dark:text-teal-300 capitalize mr-2">{{ assignee.type }}</span>
                    Reviewing <span class="font-semibold text-slate-900 dark:text-slate-100">{{ hodAssignment.hod?.name }}</span>
                </p>
            </Card>

            <Card class="mb-6 bg-white dark:bg-slate-800 border-brand-100/80 dark:border-brand-800/80 shadow-sm !p-4 sm:!p-5">
                <button type="button" class="w-full flex items-center justify-between text-sm font-semibold text-slate-900 dark:text-slate-100" @click="scaleOpen = !scaleOpen">
                    <span class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-teal-500" />
                        Rating scale (1–5)
                    </span>
                    <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 transition-transform" :class="scaleOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="space-y-2" :class="scaleOpen ? 'mt-4' : 'hidden sm:block sm:mt-4'">
                    <div v-for="item in ratingScale" :key="item.label" class="flex gap-3 rounded-xl bg-slate-50 dark:bg-slate-950 px-3 py-2.5 text-sm">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-bold text-teal-700 shrink-0 shadow-sm">{{ item.label }}</span>
                        <span class="text-slate-600 dark:text-slate-400 pt-1 leading-snug">{{ item.description }}</span>
                    </div>
                </div>
            </Card>

            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">{{ form.scores.filter((s) => s.score).length }} of {{ form.scores.length }} indicators rated</p>

            <p v-if="validationMessage" class="alert-error mb-4" role="alert">
                {{ validationMessage }}
            </p>

            <form class="space-y-5" novalidate @submit.prevent="handleSubmit">
                <Card v-for="(category, ci) in appraisalData" :key="category.name" :padding="false" class="overflow-hidden shadow-card border-slate-200 dark:border-slate-700/80">
                    <div class="px-4 sm:px-5 py-3.5 bg-gradient-to-r from-teal-600 to-brand-600 text-white flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-white dark:bg-slate-800/20 text-sm font-bold shrink-0">{{ ci + 1 }}</span>
                        <h3 class="font-semibold text-base">{{ category.name }}</h3>
                    </div>
                    <div class="p-4 sm:p-5 space-y-4">
                        <div v-for="behavior in category.keyBehaviors" :key="behavior.name">
                            <h4 v-if="behavior.name" class="text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-3">{{ behavior.name }}</h4>
                            <div
                                v-for="indicator in behavior.indicators"
                                :key="indicator.question_id"
                                :data-indicator-id="indicator.question_id"
                                class="rounded-2xl border bg-white dark:bg-slate-800 p-4 sm:p-5 mb-4 last:mb-0 transition-shadow"
                                :class="indicatorClasses(indicator.question_id)"
                            >
                                <p class="fill-indicator-text font-medium text-slate-800 dark:text-slate-200">{{ indicator.text }}</p>
                                <p class="text-xs font-medium text-slate-400 dark:text-slate-500 mt-4 mb-2">Your rating</p>
                                <div class="grid grid-cols-5 gap-2">
                                    <label v-for="n in 5" :key="n" class="min-w-0">
                                        <input
                                            v-model="form.scores[scoreIndex(indicator.question_id)].score"
                                            type="radio"
                                            :value="n"
                                            class="sr-only peer"
                                            @change="clearMissing(indicator.question_id)"
                                        />
                                        <span class="flex items-center justify-center h-12 sm:h-11 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 font-bold text-base cursor-pointer transition-all duration-150 select-none touch-manipulation peer-checked:border-teal-500 peer-checked:bg-teal-500 peer-checked:text-white peer-checked:shadow-md peer-checked:shadow-teal-500/25 hover:border-teal-300 active:scale-95">{{ n }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>

                <Card class="!p-4 sm:!p-6 border-slate-200 dark:border-slate-700/80 shadow-sm">
                    <TextArea v-model="form.assignee_comment" label="Your comments" :rows="4" placeholder="Share any additional feedback..." />
                </Card>

                <div class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800/95 backdrop-blur-md px-4 py-3 safe-bottom sm:static sm:border-0 sm:bg-transparent sm:backdrop-blur-none sm:px-0 sm:py-0">
                    <Button type="submit" size="lg" class="w-full shadow-elevated" :disabled="form.processing">
                        {{ form.processing ? 'Submitting...' : 'Submit feedback' }}
                    </Button>
                </div>
            </form>
        </div>
    </FillLayout>
</template>
