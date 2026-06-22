<script setup>
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeader from '@/Components/PageHeader.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    staff: Object,
    roles: Object,
    staffRoles: Array,
    linkedUserId: Number,
    userOptions: Array,
});

const form = useForm({
    name: props.staff.name,
    nid: props.staff.nid,
    email: props.staff.email,
    emp_no: props.staff.emp_no,
    designation: props.staff.designation,
    mobile: props.staff.mobile,
    active: props.staff.active,
    role_ids: props.staffRoles ?? [],
    user_id: props.linkedUserId ?? null,
});

function submit() {
    form.put(`/staff/${props.staff.id}`);
}
</script>

<template>
    <Head title="Edit Staff" />
    <AppLayout>
        <div class="page-shell max-w-xl">
            <PageHeader title="Edit staff" :description="`Updating profile for ${staff.name}`" />

            <Card>
                <form class="space-y-4" @submit.prevent="submit">
                    <TextInput v-model="form.name" label="Name" required />
                    <TextInput v-model="form.emp_no" label="Employee number" required />
                    <TextInput v-model="form.email" label="Email" type="email" />
                    <TextInput v-model="form.designation" label="Designation" />
                    <label class="flex items-center gap-2.5 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input v-model="form.active" type="checkbox" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500/30" />
                        Active
                    </label>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Linked admin user</label>
                        <select v-model="form.user_id" class="input-field w-full">
                            <option :value="null">No linked user</option>
                            <option v-for="option in userOptions" :key="option.id" :value="option.id">
                                {{ option.label }}
                            </option>
                        </select>
                        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                            Link an admin account so this staff member can also access admin features when they sign in.
                        </p>
                    </div>
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
                        <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">Save changes</Button>
                        <Button href="/staff" variant="secondary" class="w-full sm:w-auto">Cancel</Button>
                    </div>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
