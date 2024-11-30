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
                class="mx-auto grid w-full max-w-full items-start gap-6 md:grid-cols-[180px_1fr] lg:grid-cols-[200px_1fr]">
                <nav class="grid gap-4 text-sm text-muted-foreground">
                    <Link :href="route('users')"
                        :class="{ 'font-semibold text-primary': $page.component === 'Settings/Users/Index' }">
                    Manage Users
                    </Link>
                    <Link :href="route('units')"
                        :class="{ 'font-semibold text-primary': $page.component === 'Settings/Units/Index' }">
                    Manage Units
                    </Link>
                    <Link :href="route('suppliers')"
                        :class="{ 'font-semibold text-primary': $page.component === 'Settings/Suppliers/Index' }">
                    Manage Suppliers
                    </Link>
                    <Link :href="route('customers')"
                        :class="{ 'font-semibold text-primary': $page.component === 'Settings/Customers/Index' }">
                    Manage Customers
                    </Link>
                    <Link :href="route('categories')"
                        :class="{ 'font-semibold text-primary': $page.component === 'Settings/Category/Index' }">
                    Manage Categories
                    </Link>
                    <Link :href="route('subcategories')"
                        :class="{ 'font-semibold text-primary': $page.component === 'Settings/Subcategory/Index' }">
                    Manage Sub Categories
                    </Link>
                    <Link :href="route('backup')"
                        :class="{ 'font-semibold text-primary': $page.component === 'Settings/BackupDatabases/Index' }">
                    Manage Backup Database
                    </Link>
                </nav>
                <div class="grid gap-6">
                    <slot />
                </div>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
