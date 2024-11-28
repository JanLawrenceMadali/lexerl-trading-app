<script setup>
import { ArrowLeftRight, Box, Cog, LayoutDashboard, Logs } from 'lucide-vue-next'
import MenuTab from '@/Components/MenuTab.vue'
import SubmenuTab from '@/Components/SubmenuTab.vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
</script>

<template>
    <div class="flex flex-col w-full min-h-screen bg-muted/40">
        <aside class="fixed inset-y-0 left-0 z-10 flex-col hidden bg-[#1B1212] w-32 border-r sm:flex">
            <nav class="grid items-center gap-2">
                <div
                    class="grid py-2 text-lg font-semibold place-content-center group bg-slate-50 text-primary-foreground">
                    <Link :href="route('dashboard')" class="">
                    <ApplicationLogo class="w-20 h-auto transition-all duration-300 group-hover:scale-125" />
                    <span class="sr-only">Lexerl Trading App</span>
                    </Link>
                </div>
                <MenuTab label="Dashboard" :icon="LayoutDashboard" route="dashboard"
                    :custom-class="$page.component === 'Dashboard' ? 'bg-slate-100 text-slate-800' : 'text-muted-foreground'" />
                <SubmenuTab label="Transactions" :icon="ArrowLeftRight"
                    :menu-items="['Purchase In', 'Sales', 'Collectibles']"
                    :menu-routes="['purchase-in', 'sales', 'collectibles']" :custome-class="$page.component === 'Transactions/PurchaseIn/Index'
                        ? 'bg-slate-100 !text-slate-800' : $page.component === 'Transactions/Sales/Index'
                            ? 'bg-slate-100 !text-slate-800' : $page.component === 'Transactions/Collectibles/Index'
                                ? 'bg-slate-100 !text-slate-800' : 'text-muted-foreground'" />
                <MenuTab label="Inventory" :icon="Box" route="current_inventory"
                    :custom-class="$page.component === 'Reports/Inventory/Index' ? 'bg-slate-100 text-slate-800' : 'text-muted-foreground'" />
                <MenuTab label="Logs" :icon="Logs" route="activity_logs"
                    :custom-class="$page.component === 'Reports/ActivityLogs/Index' ? 'bg-slate-100 text-slate-800' : 'text-muted-foreground'" />
                <MenuTab label="Settings" :icon="Cog" route="users"
                    :custom-class="$page.component === 'Settings/Users/Index' ? 'bg-slate-100 text-slate-800' : 'text-muted-foreground'" />
            </nav>
        </aside>
        <main class="flex flex-col sm:gap-4 sm:py-4 sm:pl-32">
            <slot />
        </main>
    </div>
</template>
