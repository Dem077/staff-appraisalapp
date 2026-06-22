<script setup>
import ActionChip from '@/Components/ActionChip.vue';
import Card from '@/Components/Card.vue';
import MetaItem from '@/Components/MetaItem.vue';
import PageHeader from '@/Components/PageHeader.vue';
import RecordCard from '@/Components/RecordCard.vue';
import ResponsiveList from '@/Components/ResponsiveList.vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    staff: Object,
    filters: Object,
});

function search(e) {
    router.get('/staff', { search: e.target.value }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Staff" />
    <AppLayout>
        <div class="page-shell">
            <PageHeader title="Staff" description="View and manage staff members synced from the attendance system." />

            <div class="mb-4 sm:mb-6">
                <div class="relative max-w-md">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 dark:text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="search"
                        :value="filters.search"
                        placeholder="Search staff..."
                        class="input-field pl-9 w-full"
                        @input="search"
                    />
                </div>
            </div>

            <Card :padding="false" class="shadow-card overflow-hidden md:block">
                <ResponsiveList :items="staff.data" empty-title="No staff found" empty-description="Try adjusting your search or check the attendance sync.">
                    <template #desktop>
                        <div class="overflow-x-auto">
                            <table class="min-w-full data-table">
                                <thead class="bg-slate-50/80 border-b border-slate-100 dark:border-slate-800">
                                    <tr>
                                        <th>Name</th>
                                        <th>Emp No</th>
                                        <th>Designation</th>
                                        <th>Linked user</th>
                                        <th>Active</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="s in staff.data" :key="s.id">
                                        <td class="font-medium text-slate-900 dark:text-slate-100">{{ s.name }}</td>
                                        <td class="text-slate-600 dark:text-slate-400">{{ s.emp_no }}</td>
                                        <td class="text-slate-600 dark:text-slate-400">{{ s.designation }}</td>
                                        <td class="text-slate-600 dark:text-slate-400">
                                            <span v-if="s.linked_user">{{ s.linked_user.name }}</span>
                                            <span v-else class="text-slate-400 dark:text-slate-500">—</span>
                                        </td>
                                        <td>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium" :class="s.active ? 'badge-emerald' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'">
                                                {{ s.active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <ActionChip :href="`/staff/${s.id}/edit`" variant="primary">Edit</ActionChip>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>

                    <template #mobile="{ item: s }">
                        <RecordCard :title="s.name" :subtitle="s.designation">
                            <template #badge>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium" :class="s.active ? 'badge-emerald' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'">
                                    {{ s.active ? 'Active' : 'Inactive' }}
                                </span>
                            </template>
                            <template #meta>
                                <MetaItem label="Emp No">{{ s.emp_no }}</MetaItem>
                                <MetaItem v-if="s.linked_user" label="Linked user">{{ s.linked_user.name }}</MetaItem>
                            </template>
                            <template #actions>
                                <ActionChip :href="`/staff/${s.id}/edit`" variant="primary">Edit</ActionChip>
                            </template>
                        </RecordCard>
                    </template>
                </ResponsiveList>
            </Card>
        </div>
    </AppLayout>
</template>
