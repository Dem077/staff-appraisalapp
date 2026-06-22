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

defineProps({ users: Object });
</script>

<template>
    <Head title="Users" />
    <AppLayout>
        <div class="page-shell">
            <PageHeader title="Admin Users" description="Manage local admin accounts and their roles.">
                <template #actions>
                    <Button href="/users/create" class="w-full sm:w-auto">New user</Button>
                </template>
            </PageHeader>

            <Card :padding="false" class="shadow-card overflow-hidden">
                <ResponsiveList :items="users.data" empty-title="No admin users" empty-description="Create an admin user to manage the system.">
                    <template #desktop>
                        <div class="overflow-x-auto">
                            <table class="min-w-full data-table">
                                <thead class="bg-slate-50/80 border-b border-slate-100 dark:border-slate-800">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Linked staff</th>
                                        <th>Roles</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="u in users.data" :key="u.id">
                                        <td class="font-medium text-slate-900 dark:text-slate-100">{{ u.name }}</td>
                                        <td class="text-slate-600 dark:text-slate-400">{{ u.email }}</td>
                                        <td class="text-slate-600 dark:text-slate-400">
                                            <span v-if="u.linked_staff">{{ u.linked_staff.emp_no }} — {{ u.linked_staff.name }}</span>
                                            <span v-else class="text-slate-400 dark:text-slate-500">—</span>
                                        </td>
                                        <td>
                                            <div class="flex flex-wrap gap-1">
                                                <span v-for="role in u.roles" :key="role" class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium badge-brand">
                                                    {{ role }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <ActionChip :href="`/users/${u.id}/edit`" variant="primary">Edit</ActionChip>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>

                    <template #mobile="{ item: u }">
                        <RecordCard :title="u.name" :subtitle="u.email">
                            <template #meta>
                                <MetaItem v-if="u.linked_staff" label="Linked staff">{{ u.linked_staff.emp_no }} — {{ u.linked_staff.name }}</MetaItem>
                                <div class="col-span-2 flex flex-wrap gap-1">
                                    <span v-for="role in u.roles" :key="role" class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium badge-brand">
                                        {{ role }}
                                    </span>
                                </div>
                            </template>
                            <template #actions>
                                <ActionChip :href="`/users/${u.id}/edit`" variant="primary">Edit</ActionChip>
                            </template>
                        </RecordCard>
                    </template>
                </ResponsiveList>
            </Card>
        </div>
    </AppLayout>
</template>
