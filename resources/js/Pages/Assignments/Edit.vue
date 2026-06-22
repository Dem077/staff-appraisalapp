<script setup>
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    assignment: Object,
    entries: Array,
    canSendToStaff: Boolean,
    canDelete: Boolean,
});

const form = useForm({
    entries: props.entries.map((e) => ({ id: e.id, hidden: e.hidden })),
});

function saveEntries() {
    form.put(`/assignments/${props.assignment.id}/entries`);
}

function sendToStaff() {
    router.post(`/assignments/${props.assignment.id}/send-to-staff`);
}

function syncEntries() {
    router.post(`/assignments/${props.assignment.id}/sync-entries`);
}

function destroy() {
    if (confirm('Delete this assignment? This cannot be undone.')) {
        router.delete(`/assignments/${props.assignment.id}`);
    }
}
</script>

<template>
    <Head title="Edit Questionnaire" />
    <AppLayout>
        <div class="page-shell max-w-4xl">
            <PageHeader :title="assignment.form?.name ?? 'Edit Questionnaire'" :description="`Reviewing indicators for ${assignment.staff?.name}`">
                <template #actions>
                    <StatusBadge :status="assignment.status_color" :label="assignment.status_label" />
                </template>
            </PageHeader>

            <div class="flex flex-col sm:flex-row flex-wrap gap-2 mb-6">
                <Button variant="secondary" class="w-full sm:w-auto" @click="syncEntries">Sync indicators</Button>
                <Button v-if="canSendToStaff" class="w-full sm:w-auto" @click="sendToStaff">Complete & send to staff</Button>
                <Button v-if="canDelete" variant="danger" class="w-full sm:w-auto" @click="destroy">Delete</Button>
            </div>

            <Card :padding="false" class="shadow-card overflow-hidden">
                <form @submit.prevent="saveEntries">
                    <div v-for="(entry, index) in entries" :key="entry.id" class="p-4 flex items-start gap-4 border-b border-slate-100 dark:border-slate-800 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <label class="flex items-center gap-2 shrink-0 mt-1 cursor-pointer">
                            <input v-model="form.entries[index].hidden" type="checkbox" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500/30" />
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">N/A</span>
                        </label>
                        <div class="min-w-0 flex-1" :class="form.entries[index].hidden ? 'opacity-40' : ''">
                            <p class="text-xs text-slate-400 dark:text-slate-500">{{ entry.category }} › {{ entry.key_behavior }}</p>
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200 mt-0.5">{{ entry.indicator }}</p>
                            <p v-if="entry.dhivehi" class="font-thaana text-sm text-slate-600 dark:text-slate-400 mt-1">{{ entry.dhivehi }}</p>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800">
                        <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">Save changes</Button>
                    </div>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
