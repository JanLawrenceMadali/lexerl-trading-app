<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex flex-col items-center min-h-screen pt-6 bg-white sm:justify-center sm:pt-0 dark:bg-gray-900">
        <div>
            <Link href="/" class="grid place-items-center">
            <ApplicationLogo class="w-[400px] text-gray-500 fill-current h-auto" />
            </Link>
        </div>

        <div class="w-full px-6 py-4 mt-6 overflow-hidden sm:max-w-md dark:bg-gray-800">
            <slot />
        </div>
    </div>
</template>
