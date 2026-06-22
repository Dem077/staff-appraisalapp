<script setup>
import EmptyState from '@/Components/EmptyState.vue';

defineProps({
    items: { type: Array, default: () => [] },
    emptyTitle: { type: String, default: 'Nothing here yet' },
    emptyDescription: { type: String, default: '' },
});
</script>

<template>
    <div>
        <div v-if="items.length" class="hidden md:block">
            <slot name="desktop" />
        </div>

        <div v-if="items.length" class="md:hidden space-y-3 p-4">
            <template v-for="(item, index) in items" :key="item.id ?? index">
                <slot name="mobile" :item="item" />
            </template>
        </div>

        <EmptyState
            v-if="!items.length"
            :title="emptyTitle"
            :description="emptyDescription"
        >
            <template v-if="$slots.emptyAction" #action>
                <slot name="emptyAction" />
            </template>
        </EmptyState>
    </div>
</template>
