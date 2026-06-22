<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({ type: String, default: '' });

const props = defineProps({
    multiline: { type: Boolean, default: false },
    rows: { type: Number, default: 3 },
    inputClass: { type: String, default: '' },
});

const input = ref(null);

const keyMap = {
    q: 'ް', w: 'އ', e: 'ެ', r: 'ރ', t: 'ތ', y: 'ޔ', u: 'ު', i: 'ި', o: 'ޮ', p: 'ޕ',
    a: 'ަ', s: 'ސ', d: 'ދ', f: 'ފ', g: 'ގ', h: 'ހ', j: 'ޖ', k: 'ކ', l: 'ލ',
    z: 'ޒ', x: '×', c: 'ޗ', v: 'ވ', b: 'ބ', n: 'ނ', m: 'މ',
    Q: 'ޤ', W: 'ޢ', E: 'ޭ', R: 'ޜ', T: 'ޓ', Y: 'ޠ', U: 'ޫ', I: 'ީ', O: 'ޯ', P: '÷',
    A: 'ާ', S: 'ށ', D: 'ޑ', F: 'ﷲ', G: 'ޣ', H: 'ޙ', J: 'ޛ', K: 'ޚ', L: 'ޅ',
    Z: 'ޡ', X: 'ޘ', C: 'ޝ', V: 'ޥ', B: 'ޞ', N: 'ޏ', M: 'ޟ',
    ',': '،', ';': '؛', '?': '؟', '<': '>', '>': '<', '[': ']', ']': '[', '(': ')', ')': '(', '{': '}', '}': '{',
};

function getChar(char) {
    return keyMap[char] || char;
}

function attachThaanaKeyboard(el) {
    let latinChar = '';
    let char = '';
    let oldValue = '';

    const onSelectionChange = () => {
        if (document.activeElement === el) {
            el.dataset.start = el.selectionStart;
            el.dataset.end = el.selectionEnd;
        }
    };

    el.addEventListener('beforeinput', (e) => {
        if (['insertCompositionText', 'insertText'].includes(e.inputType)) {
            latinChar = e.data.charAt(e.data.length - 1);
            char = getChar(latinChar);
            oldValue = el.value;
        }
    });

    el.addEventListener('input', (e) => {
        if (!['insertCompositionText', 'insertText'].includes(e.inputType)) return;
        if (char === latinChar) return;

        const cursorStart = el.selectionStart;
        const cursorEnd = el.selectionEnd;
        el.value = oldValue.split(latinChar).join('');

        const selectionStart = Number(el.dataset.start);
        const selectionEnd = Number(el.dataset.end);
        if (selectionEnd - selectionStart > 0) {
            el.value = el.value.substring(0, selectionStart) + el.value.substring(selectionEnd);
        }

        let newValue = el.value.substring(0, cursorStart - 1);
        newValue += char;
        newValue += el.value.substring(cursorStart - 1);
        el.value = newValue;
        el.selectionStart = cursorStart;
        el.selectionEnd = cursorEnd;
        model.value = newValue;
    });

    document.addEventListener('selectionchange', onSelectionChange);
}

onMounted(() => {
    if (input.value) {
        attachThaanaKeyboard(input.value);
    }
});

const fieldClass = 'input-field font-thaana text-right resize-y min-h-[88px]';
</script>

<template>
    <textarea
        v-if="multiline"
        ref="input"
        v-model="model"
        :rows="rows"
        :class="[fieldClass, inputClass]"
        dir="rtl"
    />
    <input
        v-else
        ref="input"
        v-model="model"
        type="text"
        class="input-field font-thaana text-right"
        dir="rtl"
    />
</template>

<style scoped>
.font-thaana {
    font-family: 'Faruma', sans-serif;
}
</style>
