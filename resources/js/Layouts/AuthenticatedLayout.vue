<script setup>
import {
    ArrowLeftRight,
    CircleUser, Cog, Copy, CreditCard, File, FileSearch, Home, LayoutDashboard, LineChart, ListFilter, MoreVertical, Package, Package2, PanelLeft, Printer, Search, Settings, ShoppingCart, Truck, Users2
} from 'lucide-vue-next'

import { Badge } from '@/Components/ui/badge'
import { Button } from '@/Components/ui/button'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/Components/ui/dropdown-menu'
import { Input } from '@/Components/ui/input'
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator, } from '@/Components/ui/breadcrumb'
import { Sheet, SheetContent, SheetTrigger } from '@/Components/ui/sheet'
import { Pagination, PaginationList, PaginationNext, PaginationPrev, } from '@/Components/ui/pagination'
import { Progress } from '@/Components/ui/progress'
import { Separator } from '@/Components/ui/separator'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/Components/ui/table'
import { Tabs, TabsContent, TabsList, TabsTrigger, } from '@/Components/ui/tabs'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger, } from '@/Components/ui/tooltip'
import { Checkbox } from '@/Components/ui/checkbox'
import MenuTab from '@/Components/MenuTab.vue'
import SubmenuTab from '@/Components/SubmenuTab.vue'
</script>

<template>
    <div class="flex flex-col w-full min-h-screen bg-muted/40">
        <aside class="fixed inset-y-0 left-0 z-10 flex-col hidden w-32 border-r bg-background sm:flex">
            <nav class="flex flex-col items-center gap-4 px-2 sm:py-5">
                <a href="#"
                    class="flex items-center justify-center gap-2 text-lg font-semibold rounded-full group h-9 w-9 shrink-0 bg-primary text-primary-foreground md:h-8 md:w-8 md:text-base">
                    <Package2 class="w-4 h-4 transition-all group-hover:scale-110" />
                    <span class="sr-only">Acme Inc</span>
                </a>
                <MenuTab label="Dashboard" :icon="LayoutDashboard" route="dashboard" />
                <MenuTab label="Search" :icon="FileSearch" route="search" />
                <SubmenuTab label="Transactions" :icon="ArrowLeftRight" :menu-items="['Purchase In', 'Sales', 'Collectibles']"
                    :menu-routes="['purchaseIn', 'users', 'users']" />
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