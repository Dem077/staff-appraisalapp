<script setup>
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import TextArea from '@/Components/TextArea.vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    assignment: Object,
    hodScore: Number,
    assignees: Array,
    canAddHrComment: Boolean,
});

const hrForm = useForm({ hr_comment: '' });

function submitHr() {
    hrForm.post(`/hod-assignments/${props.assignment.id}/hr-comment`);
}

function scoreTone(score) {
    if (!score) return 'slate';
    if (score >= 4) return 'brand';
    if (score >= 3) return 'amber';
    return 'red';
}

const chipClass = {
    brand: 'bg-brand-50 text-brand-700 ring-brand-200 dark:bg-brand-950 dark:text-brand-300 dark:ring-brand-800',
    amber: 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:ring-amber-800',
    red: 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-950 dark:text-red-300 dark:ring-red-800',
    slate: 'bg-slate-100 text-slate-500 ring-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700',
};
</script>

<template>
    <Head title="HOD Results" />
    <AppLayout>
        <div class="page-shell max-w-4xl">
            <PageHeader title="HOD appraisal results" description="360° feedback scores and final HR review.">
                <template #actions>
                    <StatusBadge :status="assignment.status_color" :label="assignment.status_label" />
                </template>
            </PageHeader>

            <Card v-if="canAddHrComment" id="hr-review" class="mb-6 border-brand-200/80 dark:border-brand-800/80 ring-1 ring-brand-100 dark:ring-brand-900/50">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">HR final review</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Add your comment to complete this HOD appraisal.</p>
                </div>
                <form @submit.prevent="submitHr">
                    <TextArea v-model="hrForm.hr_comment" label="HR comment" :rows="4" placeholder="Add your final HR review comment..." required />
                    <Button type="submit" class="mt-4 w-full sm:w-auto" :disabled="hrForm.processing">
                        {{ hrForm.processing ? 'Saving...' : 'Complete appraisal' }}
                    </Button>
                </form>
            </Card>

            <Card class="mb-6 !p-5 sm:!p-6 overflow-hidden relative">
                <div class="absolute inset-0 bg-gradient-to-br from-brand-500/5 to-transparent pointer-events-none" />
                <div class="relative flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">HOD self score</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            {{ hodScore }}<span class="text-lg text-slate-400 dark:text-slate-500 font-semibold">/5</span>
                        </p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ assignment.hod?.name }}</p>
                    </div>
                    <div class="shrink-0 w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-500 to-teal-600 text-white flex flex-col items-center justify-center shadow-sm">
                        <span class="text-xl font-bold leading-none">{{ hodScore }}</span>
                        <span class="text-[10px] uppercase tracking-wide opacity-80 mt-0.5">Self</span>
                    </div>
                </div>
                <div class="relative mt-4 h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-teal-500" :style="{ width: `${(hodScore / 5) * 100}%` }" />
                </div>
            </Card>

            <Card :padding="false" class="shadow-card overflow-hidden mb-6 border-slate-200/80 dark:border-slate-700/80">
                <div class="px-4 sm:px-5 py-3.5 bg-gradient-to-r from-teal-600 to-brand-600 text-white">
                    <h3 class="font-semibold">360° feedback</h3>
                    <p class="text-xs text-white/80 mt-0.5">{{ assignees.length }} assignee reviews</p>
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full data-table">
                        <thead class="border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th>Assignee</th>
                                <th>Type</th>
                                <th class="text-right">Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="a in assignees" :key="a.id">
                                <td class="font-medium text-slate-900 dark:text-slate-100">{{ a.name }}</td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 capitalize">
                                        {{ a.type }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <span class="inline-flex items-center justify-center min-w-[2.5rem] h-9 px-2 rounded-xl font-bold text-sm ring-1" :class="chipClass[scoreTone(a.score)]">
                                        {{ a.score }}/5
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
                    <div v-for="a in assignees" :key="`m-${a.id}`" class="p-4 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 dark:text-slate-100 truncate">{{ a.name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 capitalize mt-0.5">{{ a.type }}</p>
                        </div>
                        <span class="shrink-0 inline-flex items-center justify-center min-w-[2.75rem] h-10 px-2 rounded-xl font-bold text-sm ring-1" :class="chipClass[scoreTone(a.score)]">
                            {{ a.score }}/5
                        </span>
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
