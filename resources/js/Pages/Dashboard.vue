<script setup>
import Card from '@/Components/Card.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({ stats: Object });

const user = computed(() => usePage().props.auth.user);
const navigation = computed(() => usePage().props.auth.navigation ?? []);

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 17) return 'Good afternoon';
    return 'Good evening';
});

const quickLinks = computed(() =>
    navigation.value
        .flatMap((group) => group.items ?? [])
        .filter((item) => item.icon !== 'home'),
);
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <div class="page-shell">
            <PageHeader
                :title="`${greeting}, ${user?.name?.split(' ')[0] || 'there'}`"
                description="Here's what's happening with your appraisals today."
            />

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <StatCard label="Pending assignments" :value="stats.pending_assignments" tone="amber" />
                <StatCard label="Pending HOD reviews" :value="stats.pending_hod" tone="blue" />
                <StatCard label="Completed" :value="stats.completed" tone="brand" />
            </div>

            <div v-if="quickLinks.length" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Link
                    v-for="item in quickLinks"
                    :key="item.href"
                    :href="item.href"
                    class="group"
                >
                    <Card hover class="h-full">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-slate-900 dark:text-slate-100 group-hover:text-brand-700 dark:group-hover:text-brand-400 transition-colors">
                                    {{ item.label }}
                                </h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
                                    Open {{ item.label.toLowerCase() }} and continue where you left off.
                                </p>
                            </div>
                            <span class="shrink-0 w-9 h-9 rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400 flex items-center justify-center group-hover:bg-brand-100 dark:group-hover:bg-brand-900 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </div>
                    </Card>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
