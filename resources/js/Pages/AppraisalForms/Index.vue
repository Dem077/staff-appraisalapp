<script setup>
import ActionChip from '@/Components/ActionChip.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import MetaItem from '@/Components/MetaItem.vue';
import PageHeader from '@/Components/PageHeader.vue';
import RecordCard from '@/Components/RecordCard.vue';
import ResponsiveList from '@/Components/ResponsiveList.vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ forms: Object });
</script>

<template>
    <Head title="Appraisal Forms" />
    <AppLayout>
        <div class="page-shell">
            <PageHeader title="Appraisal Forms" description="Build and manage complete appraisal questionnaires.">
                <template #actions>
                    <Button href="/appraisal-forms/create" class="w-full sm:w-auto">New form</Button>
                </template>
            </PageHeader>

            <Card :padding="false" class="shadow-card overflow-hidden">
                <ResponsiveList :items="forms.data" empty-title="No forms yet" empty-description="Create your first appraisal form with the form builder.">
                    <template #desktop>
                        <div class="overflow-x-auto">
                            <table class="min-w-full data-table">
                                <thead class="bg-slate-50/80 border-b border-slate-100 dark:border-slate-800">
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Level</th>
                                        <th>Structure</th>
                                        <th>Active</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="form in forms.data" :key="form.id">
                                        <td class="font-medium font-thaana text-slate-900 dark:text-slate-100">{{ form.name }}</td>
                                        <td class="text-slate-600 dark:text-slate-400">{{ form.type }}</td>
                                        <td class="text-slate-600 dark:text-slate-400">{{ form.level }}</td>
                                        <td class="text-slate-500 dark:text-slate-400 text-xs">
                                            {{ form.categories_count }} categories · {{ form.indicators_count }} indicators
                                        </td>
                                        <td>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium" :class="form.is_active ? 'badge-emerald' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'">
                                                {{ form.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <ActionChip :href="`/appraisal-forms/${form.id}/edit`" variant="primary">Edit</ActionChip>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>

                    <template #mobile="{ item: form }">
                        <RecordCard :title="form.name" :subtitle="`${form.type} · ${form.level}`">
                            <template #badge>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium" :class="form.is_active ? 'badge-emerald' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'">
                                    {{ form.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </template>
                            <template #meta>
                                <MetaItem label="Structure">{{ form.categories_count }} cat · {{ form.indicators_count }} indicators</MetaItem>
                            </template>
                            <template #actions>
                                <ActionChip :href="`/appraisal-forms/${form.id}/edit`" variant="primary">Edit</ActionChip>
                            </template>
                        </RecordCard>
                    </template>
                </ResponsiveList>
            </Card>
        </div>
    </AppLayout>
</template>
