<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const navigation = computed(() => page.props.auth.navigation ?? []);
const appName = computed(() => page.props.appName ?? 'Staff Appraisal');
const mobileOpen = ref(false);

watch(mobileOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

function isActive(href) {
    const path = href.replace(/^https?:\/\/[^/]+/, '');
    return page.url === path || page.url.startsWith(path + '/');
}

function closeMobile() {
    mobileOpen.value = false;
}

const icons = {
    home: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    clipboard: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    users: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    list: 'M4 6h16M4 10h16M4 14h16M4 18h16',
    'user-group': 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    cog: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    shield: 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
};
</script>

<template>
    <div class="min-h-screen flex bg-slate-50 dark:bg-slate-950">
        <aside class="hidden lg:flex lg:flex-col w-[17.5rem] shrink-0 self-start sticky top-0 h-screen max-h-dvh overflow-hidden border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <div class="shrink-0 px-5 py-6 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-sm font-bold text-slate-900 dark:text-slate-100 truncate">{{ appName }}</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Performance management</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 min-h-0 p-3 overflow-y-auto">
                <div
                    v-for="(group, groupIndex) in navigation"
                    :key="group.label ?? `nav-group-${groupIndex}`"
                    :class="groupIndex > 0 ? 'mt-5' : ''"
                >
                    <p
                        v-if="group.label"
                        class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"
                    >
                        {{ group.label }}
                    </p>
                    <div class="space-y-0.5">
                        <Link
                            v-for="item in group.items"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
                            :class="isActive(item.href)
                                ? 'bg-brand-50 text-brand-800 shadow-sm ring-1 ring-brand-100 dark:bg-brand-950/80 dark:text-brand-300 dark:ring-brand-800'
                                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-slate-100'"
                        >
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-lg shrink-0 transition-colors"
                                :class="isActive(item.href) ? 'bg-brand-100 text-brand-700 dark:bg-brand-900 dark:text-brand-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="icons[item.icon] || icons.home" />
                                </svg>
                            </span>
                            <span class="truncate">{{ item.label }}</span>
                        </Link>
                    </div>
                </div>
            </nav>

            <div class="shrink-0 p-4 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                    <img :src="user.avatar" :alt="user.name" class="w-10 h-10 rounded-full ring-2 ring-white dark:ring-slate-700 shadow-sm object-cover" />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">{{ user.name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ user.emp_no || user.email }}</p>
                    </div>
                    <ThemeToggle />
                </div>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="mt-3 w-full flex items-center justify-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Sign out
                </Link>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="lg:hidden sticky top-0 z-40 flex items-center justify-between gap-3 px-4 py-3 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800" style="padding-top: max(0.75rem, env(safe-area-inset-top));">
                <button
                    type="button"
                    class="touch-target p-2 -ml-1 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 active:bg-slate-200 dark:active:bg-slate-700"
                    aria-label="Open menu"
                    @click="mobileOpen = true"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <span class="font-semibold text-sm text-slate-900 dark:text-slate-100 truncate">{{ appName }}</span>
                <div class="flex items-center gap-1">
                    <ThemeToggle />
                    <img :src="user.avatar" :alt="user.name" class="w-8 h-8 rounded-full ring-2 ring-white dark:ring-slate-700 shadow-sm object-cover" />
                </div>
            </header>

            <Teleport to="body">
                <Transition
                    enter-active-class="transition-opacity duration-200"
                    enter-from-class="opacity-0"
                    leave-active-class="transition-opacity duration-200"
                    leave-to-class="opacity-0"
                >
                    <div v-if="mobileOpen" class="lg:hidden fixed inset-0 z-50">
                        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeMobile" />
                        <aside class="absolute inset-y-0 left-0 w-72 max-w-[88vw] bg-white dark:bg-slate-900 flex flex-col shadow-elevated animate-slide-in-right">
                            <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100 dark:border-slate-800" style="padding-top: max(1rem, env(safe-area-inset-top));">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 dark:text-slate-100 truncate">{{ appName }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ user.name }}</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <ThemeToggle />
                                    <button type="button" class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" @click="closeMobile">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    </button>
                                </div>
                            </div>
                            <nav class="flex-1 p-3 overflow-y-auto">
                                <div
                                    v-for="(group, groupIndex) in navigation"
                                    :key="group.label ?? `mobile-nav-group-${groupIndex}`"
                                    :class="groupIndex > 0 ? 'mt-5' : ''"
                                >
                                    <p
                                        v-if="group.label"
                                        class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"
                                    >
                                        {{ group.label }}
                                    </p>
                                    <div class="space-y-0.5">
                                        <Link
                                            v-for="item in group.items"
                                            :key="item.href"
                                            :href="item.href"
                                            class="flex items-center gap-3 px-3 py-3.5 rounded-xl text-sm font-medium touch-target"
                                            :class="isActive(item.href) ? 'bg-brand-50 text-brand-800 dark:bg-brand-950 dark:text-brand-300' : 'text-slate-600 dark:text-slate-400 active:bg-slate-50 dark:active:bg-slate-800'"
                                            @click="closeMobile"
                                        >
                                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="icons[item.icon] || icons.home" />
                                            </svg>
                                            {{ item.label }}
                                        </Link>
                                    </div>
                                </div>
                            </nav>
                            <div class="p-4 border-t border-slate-100 dark:border-slate-800 safe-bottom">
                                <Link href="/logout" method="post" as="button" class="w-full text-sm font-medium text-slate-600 dark:text-slate-400 py-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 active:bg-slate-100 dark:active:bg-slate-700 touch-target" @click="closeMobile">
                                    Sign out
                                </Link>
                            </div>
                        </aside>
                    </div>
                </Transition>
            </Teleport>

            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto safe-bottom">
                <FlashMessage />
                <slot />
            </main>
        </div>
    </div>
</template>
