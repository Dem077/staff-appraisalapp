<script setup>
import ActionChip from '@/Components/ActionChip.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import MetaItem from '@/Components/MetaItem.vue';
import PageHeader from '@/Components/PageHeader.vue';
import RecordCard from '@/Components/RecordCard.vue';
import ResponsiveList from '@/Components/ResponsiveList.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    assignments: Object,
    canAssign: Boolean,
});
</script>

<template>
    <Head title="HOD Appraisals" />
    <AppLayout>
        <div class="page-shell">
            <PageHeader title="HOD Appraisals" description="360° management team leader reviews.">
                <template v-if="canAssign" #actions>
                    <Button href="/hod-assignments/create" class="w-full sm:w-auto">Create HOD form</Button>
                </template>
            </PageHeader>

            <Card :padding="false" class="shadow-card overflow-hidden">
                <ResponsiveList
                    :items="assignments.data"
                    empty-title="No HOD appraisals"
                    empty-description="360° HOD reviews will show up here once they're created."
                >
                    <template #desktop>
                        <div class="overflow-x-auto">
                            <table class="min-w-full data-table">
                                <thead class="bg-slate-50/80 border-b border-slate-100 dark:border-slate-800">
                                    <tr>
                                        <th>Date</th>
                                        <th>Form</th>
                                        <th>HOD</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in assignments.data" :key="item.id">
                                        <td class="text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ item.assigned_date }}</td>
                                        <td class="font-medium text-slate-900 dark:text-slate-100">{{ item.form?.name }}</td>
                                        <td class="text-slate-700 dark:text-slate-300">{{ item.hod?.name }}</td>
                                        <td>
                                            <StatusBadge :status="item.status_color" :label="item.status_label" />
                                        </td>
                                        <td>
                                            <div class="flex flex-wrap justify-end gap-1.5">
                                                <ActionChip v-if="item.actions.fill_hod" :href="`/hod-assignments/${item.id}/fill-hod`" variant="warning" new-tab>Fill</ActionChip>
                                                <ActionChip v-if="item.actions.fill_assignee" :href="`/hod-assignees/${item.my_assignee_id}/fill`" variant="info" new-tab>360 Feedback</ActionChip>
                                                <ActionChip v-if="item.actions.results" :href="`/hod-assignments/${item.id}/results`">Results</ActionChip>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>

                    <template #mobile="{ item }">
                        <RecordCard :title="item.form?.name" :subtitle="item.hod?.name">
                            <template #badge>
                                <StatusBadge :status="item.status_color" :label="item.status_label" />
                            </template>
                            <template #meta>
                                <MetaItem label="Date">{{ item.assigned_date }}</MetaItem>
                            </template>
                            <template #actions>
                                <ActionChip v-if="item.actions.fill_hod" :href="`/hod-assignments/${item.id}/fill-hod`" variant="warning" new-tab>Fill</ActionChip>
                                <ActionChip v-if="item.actions.fill_assignee" :href="`/hod-assignees/${item.my_assignee_id}/fill`" variant="info" new-tab>360 Feedback</ActionChip>
                                <ActionChip v-if="item.actions.results" :href="`/hod-assignments/${item.id}/results`">Results</ActionChip>
                            </template>
                        </RecordCard>
                    </template>

                    <template v-if="canAssign" #emptyAction>
                        <Button href="/hod-assignments/create">Create first HOD form</Button>
                    </template>
                </ResponsiveList>
            </Card>
        </div>
    </AppLayout>
</template>
