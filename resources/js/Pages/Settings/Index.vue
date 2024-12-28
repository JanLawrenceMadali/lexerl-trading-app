<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

</script>

<template>
    <AuthenticatedLayout>
        <main class="flex flex-col flex-1 gap-4 px-4 bg-muted/40">
            <div class="grid items-start w-full max-w-full gap-4 mx-auto">
                <nav class="flex text-sm rounded text-muted-foreground bg-slate-200">
                    <Link :href="route('users')" :class="['w-full text-center p-2 m-2 font-medium rounded hover:bg-white transition delay-150 ease-in-out hover:text-primary',
                        { 'font-semibold text-primary bg-white': $page.component === 'Settings/Users/Index' }]">
                    Manage Users
                    </Link>
                    <Link :href="route('units')" :class="['w-full text-center p-2 m-2 font-medium rounded hover:bg-white transition delay-150 ease-in-out hover:text-primary',
                        { 'font-semibold text-primary bg-white': $page.component === 'Settings/Units/Index' }]">
                    Manage Units
                    </Link>
                    <Link :href="route('suppliers')" :class="['w-full text-center p-2 m-2 font-medium rounded hover:bg-white transition delay-150 ease-in-out hover:text-primary',
                        { 'font-semibold text-primary bg-white': $page.component === 'Settings/Suppliers/Index' }]">
                    Manage Suppliers
                    </Link>
                    <Link :href="route('customers')" :class="['w-full text-center p-2 m-2 font-medium rounded hover:bg-white transition delay-150 ease-in-out hover:text-primary',
                        { 'font-semibold text-primary bg-white': $page.component === 'Settings/Customers/Index' }]">
                    Manage Customers
                    </Link>
                    <Link :href="route('categories')" :class="['w-full text-center p-2 m-2 font-medium rounded hover:bg-white transition delay-150 ease-in-out hover:text-primary',
                        { 'font-semibold text-primary bg-white': $page.component === 'Settings/Category/Index' }]">
                    Manage Categories
                    </Link>
                    <Link :href="route('subcategories')" :class="['w-full text-center p-2 m-2 font-medium rounded hover:bg-white transition delay-150 ease-in-out hover:text-primary',
                        { 'font-semibold text-primary bg-white': $page.component === 'Settings/Subcategory/Index' }]">
                    Manage Sub Categories
                    </Link>
                    <Link :href="route('backup')" :class="['w-full text-center p-2 m-2 font-medium rounded hover:bg-white transition delay-150 ease-in-out hover:text-primary',
                        { 'font-semibold text-primary bg-white': $page.component === 'Settings/BackupDatabases/Index' }]">
                    Manage Backup Database
                    </Link>
                </nav>
                <div class="grid w-full gap-6">
                    <slot />
                </div>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
