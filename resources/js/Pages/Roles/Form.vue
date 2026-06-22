<script setup>
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeader from '@/Components/PageHeader.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    role: Object,
    rolePermissions: Array,
    permissionGroups: Object,
    protected: Boolean,
});

const form = useForm({
    name: props.role?.name ?? '',
    guard_name: props.role?.guard_name ?? 'web',
    permission_ids: props.rolePermissions ?? [],
});

watch(() => form.guard_name, () => {
    if (! props.role) {
        form.permission_ids = [];
    }
});

const expandedGroups = ref(new Set());

const activeGroups = computed(() => props.permissionGroups[form.guard_name] ?? []);

function toggleGroup(key) {
    if (expandedGroups.value.has(key)) {
        expandedGroups.value.delete(key);
    } else {
        expandedGroups.value.add(key);
    }
}

function isGroupExpanded(key) {
    return expandedGroups.value.has(key);
}

function groupSelectedCount(group) {
    return group.permissions.filter((p) => form.permission_ids.includes(p.id)).length;
}

function toggleGroupPermissions(group, checked) {
    const ids = group.permissions.map((p) => p.id);
    if (checked) {
        form.permission_ids = [...new Set([...form.permission_ids, ...ids])];
    } else {
        form.permission_ids = form.permission_ids.filter((id) => !ids.includes(id));
    }
}

function submit() {
    if (props.role?.id) {
        form.put(`/roles/${props.role.id}`);
    } else {
        form.post('/roles');
    }
}
</script>

<template>
    <Head :title="role ? 'Edit Role' : 'New Role'" />
    <AppLayout>
        <div class="page-shell max-w-3xl">
            <PageHeader
                :title="role ? 'Edit role' : 'Create role'"
                description="Choose a guard and assign permissions for this role."
            />

            <Card>
                <form class="space-y-6" @submit.prevent="submit">
                    <TextInput
                        v-model="form.name"
                        label="Role name"
                        required
                        :hint="protected ? 'Protected system role — name cannot be changed' : 'Use lowercase letters, numbers, and underscores'"
                        :disabled="protected"
                    />

                    <div v-if="!role">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Guard</label>
                        <select v-model="form.guard_name" class="input-field">
                            <option value="web">Admin (web)</option>
                            <option value="staff">Staff</option>
                        </select>
                        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">Admin roles apply to local users. Staff roles apply to staff logins.</p>
                    </div>
                    <div v-else>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Guard</label>
                        <p class="text-sm text-slate-600 dark:text-slate-400 capitalize">{{ form.guard_name === 'web' ? 'Admin (web)' : 'Staff' }}</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Permissions</label>
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ form.permission_ids.length }} selected</span>
                        </div>

                        <div v-if="!activeGroups.length" class="text-sm text-slate-500 dark:text-slate-400 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 px-4 py-8 text-center">
                            No permissions found for this guard.
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="group in activeGroups"
                                :key="group.key"
                                class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden"
                            >
                                <button
                                    type="button"
                                    class="w-full flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-800/50 text-left"
                                    @click="toggleGroup(group.key)"
                                >
                                    <svg class="w-4 h-4 text-slate-400 transition-transform shrink-0" :class="isGroupExpanded(group.key) ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    <span class="flex-1 font-medium text-slate-900 dark:text-slate-100 text-sm">{{ group.label }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ groupSelectedCount(group) }}/{{ group.permissions.length }}</span>
                                </button>

                                <div v-show="isGroupExpanded(group.key)" class="px-4 py-3 border-t border-slate-100 dark:border-slate-700 space-y-2">
                                    <label class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400 pb-1 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-brand-600 focus:ring-brand-500/30"
                                            :checked="groupSelectedCount(group) === group.permissions.length"
                                            :indeterminate="groupSelectedCount(group) > 0 && groupSelectedCount(group) < group.permissions.length"
                                            @change="toggleGroupPermissions(group, $event.target.checked)"
                                        />
                                        Select all in this group
                                    </label>
                                    <label
                                        v-for="permission in group.permissions"
                                        :key="permission.id"
                                        class="flex items-center gap-3 py-1.5 text-sm text-slate-700 dark:text-slate-300 cursor-pointer"
                                    >
                                        <input
                                            v-model="form.permission_ids"
                                            type="checkbox"
                                            :value="permission.id"
                                            class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-brand-600 focus:ring-brand-500/30"
                                        />
                                        <span>{{ permission.label }}</span>
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 ml-auto hidden sm:inline">{{ permission.name }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2">
                        <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
                            {{ form.processing ? 'Saving...' : 'Save role' }}
                        </Button>
                        <Button href="/roles" variant="secondary" class="w-full sm:w-auto">Cancel</Button>
                    </div>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
