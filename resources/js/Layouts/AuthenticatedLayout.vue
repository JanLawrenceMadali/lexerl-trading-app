<script setup>
import { ArrowLeftRight, Cog, FileSearch, LayoutDashboard, Printer } from 'lucide-vue-next'
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
                <MenuTab label="Dashboard" :icon="LayoutDashboard" route="dashboard" />
                <MenuTab label="Search" :icon="FileSearch" route="search" />
                <SubmenuTab label="Transactions" :icon="ArrowLeftRight"
                    :menu-items="['Collectibles', 'Purchase In', 'Sales']"
                    :menu-routes="['users', 'purchase-in', 'sales']" />
                <SubmenuTab label="Reports" :icon="Printer" :menu-items="['Sales', 'Collectibles', 'Purchases']"
                    :menu-routes="['users', 'users', 'users']" />
                <MenuTab label="Settings" :icon="Cog" route="settings" />
            </nav>
        </aside>
        <main class="flex flex-col sm:gap-4 sm:py-4 sm:pl-32">
            <slot />
        </main>
    </div>
</template>