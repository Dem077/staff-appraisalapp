<script setup>
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    forms: Object,
    staffOptions: Object,
});

const form = useForm({
    appraisal_form_id: '',
    staff_ids: [],
    supervisor_id: '',
});

function submit() {
    form.post('/assignments');
}
</script>

<template>
    <Head title="Assign Appraisal" />
    <AppLayout>
        <div class="page-shell max-w-2xl">
            <PageHeader title="Assign Appraisal Form" description="Select a form, staff members, and their supervisor." />

            <Card>
                <form class="space-y-6" @submit.prevent="submit">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Appraisal form</label>
                        <select v-model="form.appraisal_form_id" required class="input-field">
                            <option value="">Select form...</option>
                            <option v-for="(name, id) in forms" :key="id" :value="id">{{ name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Staff members</label>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Select one or more staff to assign this form to.</p>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-800 max-h-64 overflow-y-auto">
                            <template v-for="(members, dept) in staffOptions" :key="dept">
                                <div class="px-4 py-2 bg-slate-50 dark:bg-slate-950">
                                    <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">{{ dept }}</p>
                                </div>
                                <label
                                    v-for="(label, id) in members"
                                    :key="id"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors"
                                >
                                    <input v-model="form.staff_ids" type="checkbox" :value="Number(id)" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500/30" />
                                    {{ label }}
                                </label>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Supervisor</label>
                        <select v-model="form.supervisor_id" required class="input-field">
                            <option value="">Select supervisor...</option>
                            <template v-for="(members, dept) in staffOptions" :key="dept">
                                <optgroup :label="dept">
                                    <option v-for="(label, id) in members" :key="id" :value="id">{{ label }}</option>
                                </optgroup>
                            </template>
                        </select>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2">
                        <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
                            {{ form.processing ? 'Creating...' : 'Create assignments' }}
                        </Button>
                        <Button href="/assignments" variant="secondary" class="w-full sm:w-auto">Cancel</Button>
                    </div>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
