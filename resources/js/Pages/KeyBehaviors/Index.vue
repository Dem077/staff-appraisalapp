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

defineProps({ behaviors: Object });
</script>

<template>
    <Head title="Key Behaviors" />
    <AppLayout>
        <div class="page-shell">
            <PageHeader title="Key Behaviors" description="Define behavioral indicators used in appraisal forms.">
                <template #actions>
                    <Button href="/key-behaviors/create" class="w-full sm:w-auto">New behavior</Button>
                </template>
            </PageHeader>

            <Card :padding="false" class="shadow-card overflow-hidden">
                <ResponsiveList :items="behaviors.data" empty-title="No key behaviors" empty-description="Add behavioral indicators to use in your appraisal forms.">
                    <template #desktop>
                        <div class="overflow-x-auto">
                            <table class="min-w-full data-table">
                                <thead class="bg-slate-50/80 border-b border-slate-100 dark:border-slate-800">
                                    <tr>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Questions</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="b in behaviors.data" :key="b.id">
                                        <td class="font-medium text-slate-900 dark:text-slate-100">{{ b.name }}</td>
                                        <td class="text-slate-600 dark:text-slate-400">{{ b.category }}</td>
                                        <td class="text-slate-600 dark:text-slate-400">{{ b.question_count }}</td>
                                        <td class="text-right">
                                            <ActionChip :href="`/key-behaviors/${b.id}/edit`" variant="primary">Edit</ActionChip>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>

                    <template #mobile="{ item: b }">
                        <RecordCard :title="b.name" :subtitle="b.category">
                            <template #meta>
                                <MetaItem label="Questions">{{ b.question_count }}</MetaItem>
                            </template>
                            <template #actions>
                                <ActionChip :href="`/key-behaviors/${b.id}/edit`" variant="primary">Edit</ActionChip>
                            </template>
                        </RecordCard>
                    </template>
                </ResponsiveList>
            </Card>
        </div>
    </AppLayout>
</template>
