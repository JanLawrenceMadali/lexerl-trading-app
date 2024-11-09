<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>

<template>
    <AuthenticatedLayout>
        <main
            class="flex min-h-[calc(100vh_-_theme(spacing.16))] flex-1 flex-col gap-4 bg-muted/40 p-4 md:gap-8 md:p-10">
            <div class="grid w-full max-w-full gap-2 mx-auto">
                <h1 class="text-3xl font-semibold">
                    Settings
                </h1>
            </div>
            <div
                class="mx-auto grid w-full max-w-full items-start gap-6 md:grid-cols-[180px_1fr] lg:grid-cols-[250px_1fr]">
                <nav class="grid gap-4 text-sm text-muted-foreground">
                    <Link :href="route('settings.users')"
                        :class="{ 'font-semibold text-primary': $page.component === 'Settings/Users/Index' }">Manage users
                    </Link>
                    <Link :href="route('settings.suppliers')"
                        :class="{ 'font-semibold text-primary': $page.component === 'Settings/Suppliers/Index' }">Manage suppliers
                    </Link>
                    <a href="#">Support</a>
                    <a href="#">Organizations</a>
                    <a href="#">Advanced</a>
                </nav>
                <div class="grid gap-6">
                    <slot />
                </div>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
