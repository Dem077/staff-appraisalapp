<script setup>
import AppraisalFillForm from '@/Components/AppraisalFillForm.vue';
import FillLayout from '@/Layouts/FillLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    assignment: Object,
    staff: Object,
    ratingScale: Array,
    appraisalData: Array,
});

const form = useForm({
    scores: [],
    employee_comments: '',
});

function initScores() {
    const scores = [];
    props.appraisalData.forEach((category) => {
        category.keyBehaviors.forEach((behavior) => {
            behavior.indicators.forEach((indicator) => {
                scores.push({
                    question_id: indicator.question_id,
                    self_score: indicator.self_score || '',
                });
            });
        });
    });
    form.scores = scores;
}

initScores();

const progress = computed(() => {
    if (!form.scores.length) return 0;
    const answered = form.scores.filter((s) => s.self_score !== '' && s.self_score != null).length;
    return Math.round((answered / form.scores.length) * 100);
});

const fillForm = ref(null);

function submit() {
    form.post(`/assignments/${props.assignment.id}/fill-staff`, {
        preserveScroll: true,
        onError: (errors) => fillForm.value?.handleServerErrors(errors),
    });
}
</script>

<template>
    <Head title="Staff Self-Appraisal" />
    <FillLayout
        title="Employee self-appraisal"
        :subtitle="assignment.form?.name"
        back-href="/assignments"
        back-label="Back to assignments"
        :progress="progress"
    >
        <AppraisalFillForm
            ref="fillForm"
            :staff="staff"
            :rating-scale="ratingScale"
            :appraisal-data="appraisalData"
            mode="staff"
            :form="form"
            comment-label="Employee comments"
            comment-field="employee_comments"
            @submit="submit"
        />
    </FillLayout>
</template>
