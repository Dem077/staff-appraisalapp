<script setup>
import ActionChip from '@/Components/ActionChip.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeader from '@/Components/PageHeader.vue';
import RecordCard from '@/Components/RecordCard.vue';
import ResponsiveList from '@/Components/ResponsiveList.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

defineProps({ roles: Array });

const page = usePage();
const canCreate = computed(() => page.props.auth.user?.permissions?.includes('create_role'));
const canUpdate = computed(() => page.props.auth.user?.permissions?.includes('update_role'));
const canDelete = computed(() => page.props.auth.user?.permissions?.includes('delete_role'));

function guardLabel(guard) {
    return guard === 'web' ? 'Admin' : 'Staff';
}

function deleteRole(role) {
    if (! confirm(`Delete role "${role.name}"?`)) return;
    router.delete(`/roles/${role.id}`);
}
</script>

<template>
    <Head title="Roles & Permissions" />
    <AppLayout>
        <div class="page-shell">
            <PageHeader title="Roles & Permissions" description="Define roles and control what each role can access.">
                <template #actions>
                    <Button v-if="canCreate" href="/roles/create" class="w-full sm:w-auto">New role</Button>
                </template>
            </PageHeader>

            <Card :padding="false" class="shadow-card overflow-hidden">
                <ResponsiveList :items="roles" empty-title="No roles" empty-description="Create a role to manage permissions.">
                    <template #desktop>
                        <div class="overflow-x-auto">
                            <table class="min-w-full data-table">
                                <thead>
                                    <tr>
                                        <th>Role</th>
                                        <th>Guard</th>
                                        <th>Permissions</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="role in roles" :key="role.id">
                                        <td class="font-medium text-slate-900 dark:text-slate-100">
                                            {{ role.name }}
                                            <span v-if="role.protected" class="ml-2 text-[10px] uppercase tracking-wide badge-amber px-1.5 py-0.5 rounded">Protected</span>
                                        </td>
                                        <td>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                                {{ guardLabel(role.guard_name) }}
                                            </span>
                                        </td>
                                        <td class="text-slate-600 dark:text-slate-400">{{ role.permissions_count }}</td>
                                        <td class="text-right">
                                            <div class="flex justify-end gap-2">
                                                <ActionChip v-if="canUpdate" :href="`/roles/${role.id}/edit`" variant="primary">Edit</ActionChip>
                                                <button
                                                    v-if="canDelete && !role.protected"
                                                    type="button"
                                                    class="inline-flex items-center justify-center px-3 py-2 sm:px-2.5 sm:py-1 rounded-lg text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950 transition-colors min-h-[36px] sm:min-h-0"
                                                    @click="deleteRole(role)"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>

                    <template #mobile="{ item: role }">
                        <RecordCard :title="role.name" :subtitle="`${guardLabel(role.guard_name)} guard · ${role.permissions_count} permissions`">
                            <template #badge>
                                <span v-if="role.protected" class="text-[10px] uppercase tracking-wide badge-amber px-1.5 py-0.5 rounded">Protected</span>
                            </template>
                            <template #actions>
                                <ActionChip v-if="canUpdate" :href="`/roles/${role.id}/edit`" variant="primary">Edit</ActionChip>
                                <button
                                    v-if="canDelete && !role.protected"
                                    type="button"
                                    class="inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950"
                                    @click="deleteRole(role)"
                                >
                                    Delete
                                </button>
                            </template>
                        </RecordCard>
                    </template>
                </ResponsiveList>
            </Card>
        </div>
    </AppLayout>
</template>
