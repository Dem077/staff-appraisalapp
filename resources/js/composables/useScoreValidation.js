import { nextTick, ref } from 'vue';

export function scrollToIndicator(questionId) {
    nextTick(() => {
        const el = document.querySelector(`[data-indicator-id="${questionId}"]`);
        if (!el) {
            return;
        }

        const headerOffset = 96;
        const top = el.getBoundingClientRect().top + window.scrollY - headerOffset;
        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
    });
}

export function scrollToScoreError(errors, scores) {
    const key = Object.keys(errors).find((name) => name.startsWith('scores'));
    if (!key) {
        return null;
    }

    const match = key.match(/scores\.(\d+)/);
    if (!match) {
        return null;
    }

    const questionId = scores[Number(match[1])]?.question_id;
    if (!questionId) {
        return null;
    }

    scrollToIndicator(questionId);

    return {
        questionId,
        message: errors[key] || 'Please complete the highlighted indicator.',
    };
}

export function useScoreValidation() {
    const validationMessage = ref('');
    const missingIds = ref(new Set());

    function clearValidation() {
        validationMessage.value = '';
        missingIds.value = new Set();
    }

    function markMissing(questionIds) {
        missingIds.value = new Set(questionIds);
    }

    function clearMissing(questionId) {
        if (!missingIds.value.has(questionId)) {
            return;
        }

        const next = new Set(missingIds.value);
        next.delete(questionId);
        missingIds.value = next;

        if (next.size === 0) {
            validationMessage.value = '';
        }
    }

    function isMissing(questionId) {
        return missingIds.value.has(questionId);
    }

    function validateScores(scores, scoreField) {
        clearValidation();

        const missing = scores.filter((score) => score[scoreField] === '' || score[scoreField] == null);
        if (!missing.length) {
            return true;
        }

        const ids = missing.map((score) => score.question_id);
        markMissing(ids);
        validationMessage.value = ids.length === 1
            ? 'Please rate the highlighted indicator before submitting.'
            : `Please rate all indicators. ${ids.length} still need a score.`;
        scrollToIndicator(ids[0]);

        return false;
    }

    function applyServerErrors(errors, scores) {
        const result = scrollToScoreError(errors, scores);
        if (!result) {
            return;
        }

        markMissing([result.questionId]);
        validationMessage.value = result.message;
    }

    return {
        validationMessage,
        clearValidation,
        clearMissing,
        isMissing,
        validateScores,
        applyServerErrors,
    };
}
