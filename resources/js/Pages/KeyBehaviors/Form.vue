<script setup>
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeader from '@/Components/PageHeader.vue';
import TextArea from '@/Components/TextArea.vue';
import TextInput from '@/Components/TextInput.vue';
import ThaanaInput from '@/Components/ThaanaInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    behavior: Object,
    categories: Object,
});

const form = useForm({
    name: props.behavior?.name ?? '',
    appraisal_form_category_id: props.behavior?.appraisal_form_category_id ?? '',
    questions: props.behavior?.questions ?? [{ behavioral_indicators: '', dhivehi_behavioral_indicators: '' }],
});

function addQuestion() {
    form.questions.push({ behavioral_indicators: '', dhivehi_behavioral_indicators: '' });
}

function submit() {
    if (props.behavior?.id) {
        form.put(`/key-behaviors/${props.behavior.id}`);
    } else {
        form.post('/key-behaviors');
    }
}
</script>

<template>
    <Head :title="behavior ? 'Edit Key Behavior' : 'New Key Behavior'" />
    <AppLayout>
        <div class="page-shell max-w-3xl">
            <PageHeader :title="behavior ? 'Edit key behavior' : 'Create key behavior'" description="Define behavioral indicators and their Dhivehi translations." />

            <form class="space-y-5" @submit.prevent="submit">
                <Card class="space-y-4">
                    <TextInput v-model="form.name" label="Name" required />
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Category</label>
                        <select v-model="form.appraisal_form_category_id" required class="input-field">
                            <option value="">Select category...</option>
                            <option v-for="(name, id) in categories" :key="id" :value="id">{{ name }}</option>
                        </select>
                    </div>
                </Card>

                <Card v-for="(q, i) in form.questions" :key="i" class="space-y-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Question {{ i + 1 }}</p>
                    <TextArea v-model="q.behavioral_indicators" label="Behavioral indicator (English)" :rows="3" input-class="indicator-textarea" required />
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Behavioral indicator (Dhivehi)</label>
                        <ThaanaInput v-model="q.dhivehi_behavioral_indicators" multiline :rows="3" input-class="indicator-textarea" />
                    </div>
                </Card>

                <Button type="button" variant="ghost" @click="addQuestion">+ Add question</Button>

                <div class="flex flex-col-reverse sm:flex-row gap-3">
                    <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">Save behavior</Button>
                    <Button href="/key-behaviors" variant="secondary" class="w-full sm:w-auto">Cancel</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
