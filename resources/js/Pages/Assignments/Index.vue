<script setup>
import ActionChip from '@/Components/ActionChip.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import MetaItem from '@/Components/MetaItem.vue';
import PageHeader from '@/Components/PageHeader.vue';
import RecordCard from '@/Components/RecordCard.vue';
import ResponsiveList from '@/Components/ResponsiveList.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import TabNav from '@/Components/TabNav.vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    assignments: Object,
    canAssign: Boolean,
    tabs: Object,
    activeTab: String,
});

const tabList = computed(() => Object.values(props.tabs ?? {}));

const emptyCopy = computed(() => {
    const messages = {
        all: {
            title: 'No assignments yet',
            description: "When appraisals are assigned to you or your team, they'll appear here.",
        },
        questionnaire_edit: {
            title: 'No questionnaire setup pending',
            description: 'Assignments waiting for the supervisor to prepare the questionnaire will show here.',
        },
        staff_fill: {
            title: 'No user fill pending',
            description: 'Assignments waiting for the staff member to complete their self-appraisal will show here.',
        },
        supervisor_review: {
            title: 'No supervisor reviews pending',
            description: 'Assignments waiting for supervisor review will show here.',
        },
        hr_review: {
            title: 'No HR reviews pending',
            description: 'Assignments waiting for HR comment will appear in this tab.',
        },
        completed: {
            title: 'No completed assignments',
            description: 'Finished appraisals will be listed here once they are marked complete.',
        },
    };

    return messages[props.activeTab] ?? messages.all;
});

function selectTab(tab) {
    router.get('/assignments', { tab }, { preserveState: true, preserveScroll: true, replace: true });
}
</script>

<template>
    <Head title="Appraisal Assignments" />
    <AppLayout>
        <div class="page-shell">
            <PageHeader title="Appraisal Assignments" description="Track and manage team leader and member appraisals.">
                <template v-if="canAssign" #actions>
                    <Button href="/assignments/create" class="w-full sm:w-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Assign form
                    </Button>
                </template>
            </PageHeader>

            <Card :padding="false" class="shadow-card overflow-hidden md:p-0">
                <TabNav :tabs="tabList" :active="activeTab" @select="selectTab" />

                <ResponsiveList
                    :items="assignments.data"
                    :empty-title="emptyCopy.title"
                    :empty-description="emptyCopy.description"
                >
                    <template #desktop>
                        <div class="overflow-x-auto">
                            <table class="min-w-full data-table">
                                <thead class="bg-slate-50/80 border-b border-slate-100 dark:border-slate-800">
                                    <tr>
                                        <th>Date</th>
                                        <th>Form</th>
                                        <th>Staff</th>
                                        <th>Supervisor</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in assignments.data" :key="item.id">
                                        <td class="text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ item.assigned_date }}</td>
                                        <td class="font-medium text-slate-900 dark:text-slate-100">{{ item.form?.name }}</td>
                                        <td class="text-slate-700 dark:text-slate-300">{{ item.staff?.name }}</td>
                                        <td class="text-slate-600 dark:text-slate-400">{{ item.supervisor?.name }}</td>
                                        <td>
                                            <StatusBadge :status="item.status_color" :label="item.status_label" />
                                        </td>
                                        <td>
                                            <div class="flex flex-wrap justify-end gap-1.5">
                                                <ActionChip v-if="item.actions.edit" :href="`/assignments/${item.id}/edit`" variant="primary">Edit</ActionChip>
                                                <ActionChip v-if="item.actions.fill_staff" :href="`/assignments/${item.id}/fill-staff`" variant="warning" new-tab>Fill</ActionChip>
                                                <ActionChip v-if="item.actions.fill_supervisor" :href="`/assignments/${item.id}/fill-supervisor`" variant="info" new-tab>Review</ActionChip>
                                                <ActionChip v-if="item.actions.results" :href="`/assignments/${item.id}/results`">Results</ActionChip>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>

                    <template #mobile="{ item }">
                        <RecordCard :title="item.form?.name" :subtitle="item.staff?.name">
                            <template #badge>
                                <StatusBadge :status="item.status_color" :label="item.status_label" />
                            </template>
                            <template #meta>
                                <MetaItem label="Date">{{ item.assigned_date }}</MetaItem>
                                <MetaItem label="Supervisor">{{ item.supervisor?.name }}</MetaItem>
                            </template>
                            <template #actions>
                                <ActionChip v-if="item.actions.edit" :href="`/assignments/${item.id}/edit`" variant="primary">Edit</ActionChip>
                                <ActionChip v-if="item.actions.fill_staff" :href="`/assignments/${item.id}/fill-staff`" variant="warning" new-tab>Fill</ActionChip>
                                <ActionChip v-if="item.actions.fill_supervisor" :href="`/assignments/${item.id}/fill-supervisor`" variant="info" new-tab>Review</ActionChip>
                                <ActionChip v-if="item.actions.results" :href="`/assignments/${item.id}/results`">Results</ActionChip>
                            </template>
                        </RecordCard>
                    </template>

                    <template v-if="canAssign && activeTab === 'all'" #emptyAction>
                        <Button href="/assignments/create">Assign first form</Button>
                    </template>
                </ResponsiveList>
            </Card>
        </div>
    </AppLayout>
</template>
