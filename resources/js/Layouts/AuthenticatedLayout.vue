<script setup>
import { ArrowLeftRight, Box, Cog, LayoutDashboard, Logs } from 'lucide-vue-next'
import MenuTab from '@/Components/MenuTab.vue'
import SubmenuTab from '@/Components/SubmenuTab.vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { Link } from '@inertiajs/vue3';
import CustomAvatar from '@/Components/CustomAvatar.vue';
import BellNotification from '@/Components/BellNotification.vue';
</script>

<template>
    <div class="flex flex-col w-full min-h-screen">
        <header class="fixed inset-x-0 z-10 bg-white border">
            <nav class="flex items-center justify-between gap-4 px-4">
                <div class="flex items-center gap-4">
                    <div class="text-lg font-semibold group text-primary-foreground">
                        <Link :href="route('dashboard')" class="">
                        <ApplicationLogo class="w-48 h-auto py-4" />
                        <span class="sr-only">Lexerl Trading App</span>
                        </Link>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <BellNotification />
                    <CustomAvatar />
                </div>
            </nav>
        </header>
        <div class="flex">
            <aside class="flex-col hidden w-36 bg-[#1B1212] fixed inset-y-0 sm:flex">
                <nav class="grid items-center gap-4 px-3 mt-[115px]">
                    <MenuTab label="Dashboard" :icon="LayoutDashboard" route="dashboard"
                        :custom-class="$page.component === 'Dashboard' ? 'bg-slate-100 text-slate-800' : 'text-muted-foreground'" />
                    <SubmenuTab label="Transactions" :icon="ArrowLeftRight"
                        :menu-items="['Purchase In', 'Sales', 'Collectibles']"
                        :menu-routes="['purchase-in', 'sales', 'collectibles']" :custome-class="$page.component === 'Transactions/PurchaseIn/Index' ? 'bg-slate-100 !text-slate-800'
                            : $page.component === 'Transactions/Sales/Index' ? 'bg-slate-100 !text-slate-800'
                                : $page.component === 'Transactions/Collectibles/Index' ? 'bg-slate-100 !text-slate-800'
                                    : 'text-muted-foreground'" />
                    <MenuTab label="Inventory" :icon="Box" route="current_inventory"
                        :custom-class="$page.component === 'Reports/Inventory/Index' ? 'bg-slate-100 text-slate-800' : 'text-muted-foreground'" />
                    <MenuTab label="Logs" :icon="Logs" route="activity_logs"
                        :custom-class="$page.component === 'Reports/ActivityLogs/Index' ? 'bg-slate-100 text-slate-800' : 'text-muted-foreground'" />
                    <MenuTab label="Settings" :icon="Cog" route="settings" :custom-class="$page.component === 'Settings/Users/Index' ? 'bg-slate-100 text-slate-800'
                        : $page.component === 'Settings/Units/Index' ? 'bg-slate-100 text-slate-800'
                            : $page.component === 'Settings/Suppliers/Index' ? 'bg-slate-100 text-slate-800'
                                : $page.component === 'Settings/Customers/Index' ? 'bg-slate-100 text-slate-800'
                                    : $page.component === 'Settings/Categories/Index' ? 'bg-slate-100 text-slate-800'
                                        : $page.component === 'Settings/Category/Index' ? 'bg-slate-100 text-slate-800'
                                            : $page.component === 'Settings/Subcategory/Index' ? 'bg-slate-100 text-slate-800'
                                                : $page.component === 'Settings/BackupDatabases/Index' ? 'bg-slate-100 text-slate-800'
                                                    : 'text-muted-foreground'" />
                </nav>
            </aside>
            <main class="flex flex-col w-full gap-4 py-6 pt-32 pl-36">
                <slot />
            </main>
        </div>
    </div>
</template>
