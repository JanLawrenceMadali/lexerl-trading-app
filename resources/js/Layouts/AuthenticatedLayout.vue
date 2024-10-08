<script setup>
import { ArrowLeftRight, Cog, FileSearch, LayoutDashboard, Printer, Settings, } from 'lucide-vue-next'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger, } from '@/Components/ui/tooltip'
import MenuTab from '@/Components/MenuTab.vue'
import SubmenuTab from '@/Components/SubmenuTab.vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
</script>

<template>
    <div class="flex flex-col w-full min-h-screen bg-muted/40">
        <aside class="fixed inset-y-0 left-0 z-10 flex-col hidden w-32 border-r bg-background sm:flex">
            <nav class="flex flex-col items-center gap-4 px-2 sm:py-5">
                <Link :href="route('dashboard')"
                    class="flex items-center justify-center gap-2 text-lg font-semibold rounded-full group h-9 w-9 shrink-0 bg-slate-200 text-primary-foreground md:size-16 md:text-base">
                <ApplicationLogo class="w-12 h-auto transition-all duration-300 group-hover:scale-125" />
                <span class="sr-only">Lexerl Trading App</span>
                </Link>
                <MenuTab label="Dashboard" :icon="LayoutDashboard" route="dashboard" />
                <MenuTab label="Search" :icon="FileSearch" route="search" />
                <SubmenuTab label="Transactions" :icon="ArrowLeftRight"
                    :menu-items="['Collectibles', 'Purchase In', 'Sales']"
                    :menu-routes="['users', 'purchase-in', 'users']" />
                <SubmenuTab label="Reports" :icon="Printer" :menu-items="['Sales', 'Collectibles', 'Purchases']"
                    :menu-routes="['users', 'users', 'users']" />
                <MenuTab label="Settings" :icon="Cog" route="settings" />
            </nav>
            <nav class="flex flex-col items-center gap-4 px-2 mt-auto sm:py-5">
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <a href="#"
                                class="flex items-center justify-center transition-colors rounded-lg h-9 w-9 text-muted-foreground hover:text-foreground md:h-8 md:w-8">
                                <Settings class="w-5 h-5" />
                                <span class="sr-only">Settings</span>
                            </a>
                        </TooltipTrigger>
                        <TooltipContent side="right">
                            Settings
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            </nav>
        </aside>
        <main class="flex flex-col sm:gap-4 sm:py-4 sm:pl-32">
            <slot />
        </main>
    </div>
</template>