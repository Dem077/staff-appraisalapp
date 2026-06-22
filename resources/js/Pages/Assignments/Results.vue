<script setup>
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import TextArea from '@/Components/TextArea.vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    assignment: Object,
    summary: Object,
    entries: Array,
    canAddHrComment: Boolean,
    canDownloadPdf: Boolean,
});

const hrForm = useForm({ hr_comment: '' });

const groupedEntries = computed(() => {
    const groups = new Map();
    (props.entries ?? []).forEach((entry) => {
        const key = entry.category || 'General';
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push(entry);
    });
    return [...groups.entries()].map(([name, items]) => ({ name, items }));
});

const pdfUrl = computed(() => `/assignments/${props.assignment.id}/pdf`);
const pdfDownloadUrl = computed(() => `${pdfUrl.value}?download=1`);

function submitHrComment() {
    hrForm.post(`/assignments/${props.assignment.id}/hr-comment`);
}

function scoreTone(score) {
    if (!score) return 'slate';
    if (score >= 4) return 'brand';
    if (score >= 3) return 'amber';
    return 'red';
}

const scoreChipClass = {
    brand: 'bg-brand-50 text-brand-700 ring-brand-200 dark:bg-brand-950 dark:text-brand-300 dark:ring-brand-800',
    blue: 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-950 dark:text-blue-300 dark:ring-blue-800',
    amber: 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:ring-amber-800',
    red: 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-950 dark:text-red-300 dark:ring-red-800',
    slate: 'bg-slate-100 text-slate-500 ring-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700',
};

function chipClass(tone) {
    return scoreChipClass[tone] ?? scoreChipClass.slate;
}
</script>

<template>
    <Head title="Appraisal Results" />
    <AppLayout>
        <div class="page-shell max-w-5xl">
            <PageHeader
                :title="assignment.form?.name ?? 'Appraisal Results'"
                description="Final scores, indicator breakdown, and review comments."
            >
                <template #actions>
                    <StatusBadge :status="assignment.status_color" :label="assignment.status_label" />
                    <Button
                        v-if="canDownloadPdf"
                        :href="pdfUrl"
                        variant="secondary"
                        external
                        target="_blank"
                        class="w-full sm:w-auto"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        View PDF
                    </Button>
                    <Button
                        v-if="canDownloadPdf"
                        :href="pdfDownloadUrl"
                        variant="ghost"
                        external
                        class="w-full sm:w-auto"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download PDF
                    </Button>
                </template>
            </PageHeader>

            <Card v-if="canAddHrComment" id="hr-review" class="mb-6 border-brand-200/80 dark:border-brand-800/80 ring-1 ring-brand-100 dark:ring-brand-900/50">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">HR final review</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Add your comment to complete this appraisal.</p>
                </div>
                <form @submit.prevent="submitHrComment">
                    <TextArea v-model="hrForm.hr_comment" label="HR comment" :rows="4" placeholder="Add your final HR review comment..." required />
                    <Button type="submit" class="mt-4 w-full sm:w-auto" :disabled="hrForm.processing">
                        {{ hrForm.processing ? 'Saving...' : 'Complete appraisal' }}
                    </Button>
                </form>
            </Card>

            <Card class="mb-6 !p-4 sm:!p-5 border-slate-200/80 dark:border-slate-700/80">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Employee</p>
                        <p class="font-semibold text-slate-900 dark:text-slate-100 mt-1">{{ assignment.staff?.name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ assignment.staff?.emp_no }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Supervisor</p>
                        <p class="font-semibold text-slate-900 dark:text-slate-100 mt-1">{{ assignment.supervisor?.name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Assigned</p>
                        <p class="font-medium text-slate-800 dark:text-slate-200 mt-1">{{ assignment.assigned_date || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Reference</p>
                        <p class="font-medium text-slate-800 dark:text-slate-200 mt-1">#{{ assignment.id }}</p>
                    </div>
                </div>
            </Card>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                <Card class="!p-5 sm:!p-6 overflow-hidden relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-brand-500/5 to-transparent pointer-events-none" />
                    <div class="relative flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Staff average</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                                {{ summary.staff_average }}<span class="text-lg text-slate-400 dark:text-slate-500 font-semibold">/5</span>
                            </p>
                            <p class="mt-1 text-sm font-semibold text-brand-600 dark:text-brand-400">{{ summary.staff_percentage }}% overall</p>
                        </div>
                        <div class="shrink-0 w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-500 to-teal-600 text-white flex flex-col items-center justify-center shadow-sm">
                            <span class="text-lg font-bold leading-none">{{ summary.staff_percentage }}%</span>
                            <span class="text-[10px] uppercase tracking-wide opacity-80 mt-0.5">Staff</span>
                        </div>
                    </div>
                    <div class="relative mt-4 h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-brand-500 to-teal-500 transition-all"
                            :style="{ width: `${summary.staff_percentage}%` }"
                        />
                    </div>
                </Card>

                <Card class="!p-5 sm:!p-6 overflow-hidden relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent pointer-events-none" />
                    <div class="relative flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Supervisor average</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                                {{ summary.supervisor_average }}<span class="text-lg text-slate-400 dark:text-slate-500 font-semibold">/5</span>
                            </p>
                            <p class="mt-1 text-sm font-semibold text-blue-600 dark:text-blue-400">{{ summary.supervisor_percentage }}% overall</p>
                        </div>
                        <div class="shrink-0 w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex flex-col items-center justify-center shadow-sm">
                            <span class="text-lg font-bold leading-none">{{ summary.supervisor_percentage }}%</span>
                            <span class="text-[10px] uppercase tracking-wide opacity-80 mt-0.5">Supervisor</span>
                        </div>
                    </div>
                    <div class="relative mt-4 h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all"
                            :style="{ width: `${summary.supervisor_percentage}%` }"
                        />
                    </div>
                </Card>
            </div>

            <div class="space-y-5 mb-6">
                <Card
                    v-for="(group, gi) in groupedEntries"
                    :key="group.name"
                    :padding="false"
                    class="overflow-hidden shadow-card border-slate-200/80 dark:border-slate-700/80"
                >
                    <div class="px-4 sm:px-5 py-3.5 bg-gradient-to-r from-brand-600 to-teal-600 text-white flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-white/20 text-sm font-bold shrink-0">{{ gi + 1 }}</span>
                        <h3 class="font-semibold text-base">{{ group.name }}</h3>
                        <span class="ml-auto text-xs text-white/80">{{ group.items.length }} indicators</span>
                    </div>

                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full data-table">
                            <thead class="border-b border-slate-100 dark:border-slate-800">
                                <tr>
                                    <th>Indicator</th>
                                    <th class="text-center w-24">Staff</th>
                                    <th class="text-center w-24">Supervisor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(entry, i) in group.items" :key="`${group.name}-${i}`">
                                    <td>
                                        <p v-if="entry.key_behavior" class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500 mb-1">{{ entry.key_behavior }}</p>
                                        <p class="font-medium text-slate-800 dark:text-slate-200 leading-relaxed">{{ entry.indicator }}</p>
                                        <p v-if="entry.dhivehi" class="font-thaana text-sm text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">{{ entry.dhivehi }}</p>
                                    </td>
                                    <td class="text-center">
                                        <span class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2 rounded-xl font-bold text-sm ring-1" :class="chipClass(scoreTone(entry.staff_score))">
                                            {{ entry.staff_score ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2 rounded-xl font-bold text-sm ring-1" :class="chipClass(scoreTone(entry.supervisor_score))">
                                            {{ entry.supervisor_score ?? '—' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
                        <div v-for="(entry, i) in group.items" :key="`m-${group.name}-${i}`" class="p-4 space-y-3">
                            <div>
                                <p v-if="entry.key_behavior" class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500 mb-1">{{ entry.key_behavior }}</p>
                                <p class="font-medium text-slate-800 dark:text-slate-200 text-sm leading-relaxed">{{ entry.indicator }}</p>
                                <p v-if="entry.dhivehi" class="font-thaana text-sm text-slate-500 dark:text-slate-400 mt-1.5">{{ entry.dhivehi }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3 text-center">
                                    <p class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold">Staff</p>
                                    <p class="text-xl font-bold mt-1" :class="entry.staff_score ? 'text-brand-700 dark:text-brand-400' : 'text-slate-400'">{{ entry.staff_score ?? '—' }}</p>
                                </div>
                                <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3 text-center">
                                    <p class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold">Supervisor</p>
                                    <p class="text-xl font-bold mt-1" :class="entry.supervisor_score ? 'text-blue-700 dark:text-blue-400' : 'text-slate-400'">{{ entry.supervisor_score ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <div v-if="assignment.staff_comment || assignment.supervisor_comment || assignment.hr_comment" class="space-y-4 mb-6">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Comments</h2>
                <Card v-if="assignment.staff_comment" class="!p-4 sm:!p-5 border-l-4 border-l-brand-500">
                    <p class="text-xs font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-400">Staff</p>
                    <p class="text-sm text-slate-700 dark:text-slate-300 mt-2 leading-relaxed whitespace-pre-wrap">{{ assignment.staff_comment }}</p>
                </Card>
                <Card v-if="assignment.supervisor_comment" class="!p-4 sm:!p-5 border-l-4 border-l-blue-500">
                    <p class="text-xs font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-400">Supervisor</p>
                    <p class="text-sm text-slate-700 dark:text-slate-300 mt-2 leading-relaxed whitespace-pre-wrap">{{ assignment.supervisor_comment }}</p>
                </Card>
                <Card v-if="assignment.hr_comment" class="!p-4 sm:!p-5 border-l-4 border-l-teal-500">
                    <p class="text-xs font-semibold uppercase tracking-wider text-teal-700 dark:text-teal-400">HR</p>
                    <p class="text-sm text-slate-700 dark:text-slate-300 mt-2 leading-relaxed whitespace-pre-wrap">{{ assignment.hr_comment }}</p>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
