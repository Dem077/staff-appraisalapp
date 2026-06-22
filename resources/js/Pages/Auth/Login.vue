<script setup>
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import TextInput from '@/Components/TextInput.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const appName = usePage().props.appName ?? 'Staff Appraisal';

const form = useForm({
    identifier: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login');
}
</script>

<template>
    <Head title="Sign in" />

    <div class="min-h-screen flex">
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-600 to-teal-600 text-white p-12 flex-col justify-between">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white dark:bg-slate-800/30 blur-3xl" />
                <div class="absolute bottom-0 left-0 w-80 h-80 rounded-full bg-teal-300/20 blur-3xl" />
            </div>
            <div class="relative">
                <div class="w-12 h-12 rounded-2xl bg-white dark:bg-slate-800/15 backdrop-blur flex items-center justify-center ring-1 ring-white/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>
            <div class="relative max-w-md">
                <h2 class="text-3xl font-bold tracking-tight leading-tight">{{ appName }}</h2>
                <p class="mt-4 text-brand-100 text-lg leading-relaxed">
                    Manage appraisals, track progress, and complete performance reviews in one place.
                </p>
            </div>
            <p class="relative text-sm text-brand-200/80">Secure staff authentication</p>
        </div>

        <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-10 bg-slate-50 dark:bg-slate-950 safe-bottom relative">
            <div class="absolute top-4 right-4 sm:top-6 sm:right-6">
                <ThemeToggle />
            </div>
            <div class="w-full max-w-md animate-slide-up">
                <div class="lg:hidden mb-8 text-center">
                    <div class="inline-flex w-12 h-12 rounded-2xl bg-brand-600 text-white items-center justify-center mb-4 shadow-card">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ appName }}</h1>
                </div>

                <Card class="!p-8 shadow-card">
                    <div class="mb-6">
                        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Welcome back</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Sign in with your staff ID, email, or NID</p>
                    </div>

                    <form class="space-y-5" @submit.prevent="submit">
                        <TextInput
                            v-model="form.identifier"
                            label="Username"
                            placeholder="Staff ID, email, or NID"
                            required
                            :error="form.errors.identifier"
                        />
                        <TextInput
                            v-model="form.password"
                            label="Password"
                            type="password"
                            required
                            autocomplete="current-password"
                        />
                        <label class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-400 cursor-pointer select-none">
                            <input v-model="form.remember" type="checkbox" class="rounded border-slate-300 dark:border-slate-600 text-brand-600 focus:ring-brand-500/30 dark:bg-slate-800" />
                            Remember me on this device
                        </label>
                        <Button type="submit" size="lg" class="w-full" :disabled="form.processing">
                            <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ form.processing ? 'Signing in...' : 'Sign in' }}
                        </Button>
                    </form>
                </Card>
            </div>
        </div>
    </div>
</template>
