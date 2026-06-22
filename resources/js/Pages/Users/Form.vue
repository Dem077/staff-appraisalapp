<script setup>
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    user: Object,
    roles: Object,
    userRoles: Array,
    linkedStaff: Object,
});

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    password: '',
    password_confirmation: '',
    role_ids: props.userRoles ?? [],
    staff_id: props.user?.staff_id ?? null,
    active: props.user?.active ?? true,
});

const staffSearchParams = computed(() => ({
    current_staff_id: form.staff_id ?? props.user?.staff_id ?? undefined,
}));

function submit() {
    if (props.user?.id) {
        form.put(`/users/${props.user.id}`);
    } else {
        form.post('/users');
    }
}
</script>

<template>
    <Head :title="user ? 'Edit User' : 'New User'" />
    <AppLayout>
        <div class="page-shell max-w-xl">
            <PageHeader :title="user ? 'Edit user' : 'Create user'" description="Manage admin account credentials and roles." />

            <Card>
                <form class="space-y-4" @submit.prevent="submit">
                    <TextInput v-model="form.name" label="Name" required />
                    <TextInput v-model="form.email" label="Email" type="email" required />
                    <TextInput
                        v-model="form.password"
                        label="Password"
                        type="password"
                        :required="!user"
                        :hint="user ? 'Leave blank to keep current password' : ''"
                    />
                    <TextInput v-model="form.password_confirmation" label="Confirm password" type="password" />
                    <label class="flex items-center gap-2.5 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input v-model="form.active" type="checkbox" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500/30" />
                        Active
                    </label>
                    <p v-if="form.errors.active" class="text-xs text-red-600 dark:text-red-400">{{ form.errors.active }}</p>
                    <p class="-mt-2 text-xs text-slate-500 dark:text-slate-400">Inactive users cannot sign in with their admin password.</p>
                    <SearchableSelect
                        v-model="form.staff_id"
                        label="Linked staff profile"
                        placeholder="Search by name, emp no, or email..."
                        empty-label="No linked staff"
                        search-url="/users/staff-link-search"
                        :search-params="staffSearchParams"
                        :selected-option="linkedStaff"
                        hint="Link this admin account to a staff record so one login can access both admin and staff features."
                    />
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Roles</label>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100">
                            <label
                                v-for="(name, id) in roles"
                                :key="id"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer"
                            >
                                <input v-model="form.role_ids" type="checkbox" :value="Number(id)" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500/30" />
                                {{ name }}
                            </label>
                        </div>
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2">
                        <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">Save user</Button>
                        <Button href="/users" variant="secondary" class="w-full sm:w-auto">Cancel</Button>
                    </div>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
