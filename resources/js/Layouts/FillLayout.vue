<script setup>
import { Link } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    backHref: { type: String, default: '/assignments' },
    backLabel: { type: String, default: 'Back to assignments' },
    progress: { type: Number, default: null },
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-slate-100 via-white to-slate-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
        <header
            class="sticky top-0 z-40 border-b border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md"
            style="padding-top: max(0.75rem, env(safe-area-inset-top));"
        >
            <div class="max-w-3xl mx-auto px-4 sm:px-6 py-3">
                <div class="flex items-center gap-3">
                    <Link
                        :href="backHref"
                        class="flex items-center justify-center w-10 h-10 -ml-1 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-slate-200 transition-colors shrink-0"
                        :title="backLabel"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100 truncate">{{ title }}</h1>
                        <p v-if="subtitle" class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 truncate">{{ subtitle }}</p>
                    </div>
                    <ThemeToggle />
                    <div v-if="progress !== null" class="shrink-0 text-right">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Progress</p>
                        <p class="text-sm font-bold text-brand-700 dark:text-brand-400">{{ progress }}%</p>
                    </div>
                </div>
                <div v-if="progress !== null" class="mt-3 h-1.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                    <div
                        class="h-full rounded-full bg-gradient-to-r from-brand-500 to-teal-500 transition-all duration-300"
                        :style="{ width: `${progress}%` }"
                    />
                </div>
            </div>
        </header>

        <main class="px-4 sm:px-6 py-6 sm:py-8 safe-bottom">
            <div class="max-w-3xl mx-auto">
                <FlashMessage />
                <slot />
            </div>
        </main>
    </div>
</template>
