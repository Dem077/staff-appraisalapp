<script setup>
import AppraisalFillForm from '@/Components/AppraisalFillForm.vue';
import FillLayout from '@/Layouts/FillLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    assignment: Object,
    hod: Object,
    ratingScale: Array,
    appraisalData: Array,
});

const form = useForm({ scores: [], hod_comment: '' });

props.appraisalData.forEach((cat) => {
    cat.keyBehaviors.forEach((b) => {
        b.indicators.forEach((ind) => {
            form.scores.push({ question_id: ind.question_id, self_score: ind.self_score || '' });
        });
    });
});

const progress = computed(() => {
    if (!form.scores.length) return 0;
    const answered = form.scores.filter((s) => s.self_score !== '' && s.self_score != null).length;
    return Math.round((answered / form.scores.length) * 100);
});

const fillForm = ref(null);

function submit() {
    form.post(`/hod-assignments/${props.assignment.id}/fill-hod`, {
        preserveScroll: true,
        onError: (errors) => fillForm.value?.handleServerErrors(errors),
    });
}
</script>

<template>
    <Head title="HOD Self-Appraisal" />
    <FillLayout
        title="HOD self-appraisal"
        :subtitle="assignment.form?.name"
        back-href="/hod-assignments"
        back-label="Back to HOD appraisals"
        :progress="progress"
    >
        <AppraisalFillForm
            ref="fillForm"
            :staff="hod"
            :rating-scale="ratingScale"
            :appraisal-data="appraisalData"
            mode="staff"
            :form="form"
            comment-label="HOD comments"
            comment-field="hod_comment"
            @submit="submit"
        />
    </FillLayout>
</template>
