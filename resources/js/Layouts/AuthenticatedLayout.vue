<script setup>
import { ArrowLeftRight, Box, Cog, LayoutDashboard, Logs, Settings } from 'lucide-vue-next'
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
            <nav class="flex items-center justify-between gap-4 px-8">
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
                    <MenuTab label="Dashboard" route="dashboard"
                        :custom-class="$page.component === 'Dashboard' ? 'bg-slate-100 text-slate-800' : 'text-muted-foreground'">
                        <LayoutDashboard
                            :class="['size-8 group-hover:fill-[#1B1212] group-hover:text-[#1B1212]', $page.component === 'Dashboard' ? 'fill-[#1B1212] text-[#1B1212]' : 'fill-slate-100 text-slate-100']" />
                    </MenuTab>
                    <SubmenuTab label="Transactions" :icon="ArrowLeftRight"
                        :menu-items="['Purchase In', 'Sales', 'Collectibles']"
                        :menu-routes="['purchase-in', 'sales', 'collectibles']" :custome-class="$page.component === 'Transactions/PurchaseIn/Index' ? 'bg-slate-100 !text-slate-800'
                            : $page.component === 'Transactions/Sales/Index' ? 'bg-slate-100 !text-slate-800'
                                : $page.component === 'Transactions/Collectibles/Index' ? 'bg-slate-100 !text-slate-800'
                                    : 'text-muted-foreground'" />
                    <MenuTab label="Inventory" route="current_inventory"
                        :custom-class="$page.component === 'Reports/Inventory/Index' ? 'bg-slate-100 text-slate-800' : 'text-muted-foreground'">
                        <Box class="size-8" />
                    </MenuTab>
                    <MenuTab label="Logs" route="activity_logs"
                        :custom-class="$page.component === 'Reports/ActivityLogs/Index' ? 'bg-slate-100 text-slate-800' : 'text-muted-foreground'">
                        <Logs class="size-8" />
                    </MenuTab>
                    <MenuTab label="Settings" route="settings" :custom-class="$page.component === 'Settings/Users/Index' ? 'bg-slate-100 text-slate-800'
                        : $page.component === 'Settings/Units/Index' ? 'bg-slate-100 text-slate-800'
                            : $page.component === 'Settings/Suppliers/Index' ? 'bg-slate-100 text-slate-800'
                                : $page.component === 'Settings/Customers/Index' ? 'bg-slate-100 text-slate-800'
                                    : $page.component === 'Settings/Categories/Index' ? 'bg-slate-100 text-slate-800'
                                        : $page.component === 'Settings/Category/Index' ? 'bg-slate-100 text-slate-800'
                                            : $page.component === 'Settings/Subcategory/Index' ? 'bg-slate-100 text-slate-800'
                                                : $page.component === 'Settings/BackupDatabases/Index' ? 'bg-slate-100 text-slate-800'
                                                    : 'text-muted-foreground'">
                        <svg :class="['size-8 group-hover:fill-[#1B1212] group-hover:text-[#1B1212]', $page.component === 'Settings/Users/Index' ? 'fill-[#1B1212] text-[#1B1212]' : 'fill-slate-100 text-slate-100']"
                            viewBox="0 0 41 41" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M15.1146 40.0833L14.3312 33.8167C13.9069 33.6535 13.5071 33.4576 13.1317 33.2292C12.7564 33.0007 12.3892 32.7559 12.0302 32.4948L6.20414 34.9427L0.818726 25.6406L5.86143 21.8219C5.8288 21.5934 5.81248 21.3731 5.81248 21.1609V19.8391C5.81248 19.6269 5.8288 19.4066 5.86143 19.1781L0.818726 15.3594L6.20414 6.05728L12.0302 8.5052C12.3892 8.24409 12.7646 7.9993 13.1562 7.77082C13.5479 7.54235 13.9396 7.34652 14.3312 7.18332L15.1146 0.916656H25.8854L26.6687 7.18332C27.093 7.34652 27.4929 7.54235 27.8682 7.77082C28.2436 7.9993 28.6107 8.24409 28.9698 8.5052L34.7958 6.05728L40.1812 15.3594L35.1385 19.1781C35.1712 19.4066 35.1875 19.6269 35.1875 19.8391V21.1609C35.1875 21.3731 35.1548 21.5934 35.0896 21.8219L40.1323 25.6406L34.7469 34.9427L28.9698 32.4948C28.6107 32.7559 28.2354 33.0007 27.8437 33.2292C27.4521 33.4576 27.0604 33.6535 26.6687 33.8167L25.8854 40.0833H15.1146ZM20.5979 27.3542C22.4909 27.3542 24.1066 26.6851 25.4448 25.3469C26.783 24.0087 27.4521 22.393 27.4521 20.5C27.4521 18.6069 26.783 16.9913 25.4448 15.6531C24.1066 14.3149 22.4909 13.6458 20.5979 13.6458C18.6722 13.6458 17.0484 14.3149 15.7265 15.6531C14.4047 16.9913 13.7437 18.6069 13.7437 20.5C13.7437 22.393 14.4047 24.0087 15.7265 25.3469C17.0484 26.6851 18.6722 27.3542 20.5979 27.3542Z" />
                        </svg>

                    </MenuTab>
                </nav>
            </aside>
            <main class="flex flex-col w-full gap-4 py-6 pt-32 pl-36">
                <slot />
            </main>
        </div>
    </div>
</template>
