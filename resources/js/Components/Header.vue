<script setup>
import { ArrowLeftRight, CircleUser, Cog, FileSearch, LayoutDashboard, Menu, Printer } from 'lucide-vue-next';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from './ui/sheet';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from './ui/dropdown-menu';
import ApplicationLogo from './ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import MenuTab from './MenuTab.vue';
import SubmenuTab from './SubmenuTab.vue';

const props = defineProps({
    items: {
        type: Array,
        required: true,
        validator: (value) => {
            return value.every(item =>
                typeof item.label === 'string' &&
                (item.href === undefined || typeof item.href === 'string')
            );
        }
    }
})
</script>

<template>

    <header
        class="sticky top-0 z-30 flex items-center gap-4 px-4 border-b h-14 bg-background sm:static sm:h-auto sm:border-0 sm:bg-transparent sm:px-6">
        <Sheet>
            <SheetTrigger as-child>
                <Button variant="outline" size="icon" class="shrink-0 md:hidden">
                    <Menu class="w-5 h-5" />
                    <span class="sr-only">Toggle navigation menu</span>
                </Button>
            </SheetTrigger>
            <SheetContent side="left" class="flex flex-col w-[200px]">
                <SheetHeader>
                    <SheetTitle></SheetTitle>
                    <SheetDescription></SheetDescription>
                </SheetHeader>
                <nav class="flex flex-col items-center gap-4 px-2 sm:py-5">
                    <Link :href="route('dashboard')"
                        class="flex items-center justify-center gap-2 text-lg font-semibold rounded-full group size-16 shrink-0 bg-slate-200 text-primary-foreground md:size-16 md:text-base">
                    <ApplicationLogo class="w-12 h-auto transition-all duration-300 group-hover:scale-125" />
                    <span class="sr-only">Lexerl Trading App</span>
                    </Link>
                    <MenuTab label="Dashboard" :icon="LayoutDashboard" route="dashboard" />
                    <MenuTab label="Search" :icon="FileSearch" route="search" />
                    <SubmenuTab label="Transactions" :icon="ArrowLeftRight"
                        :menu-items="['Purchase In', 'Sales', 'Collectibles']"
                        :menu-routes="['purchase-in', 'users', 'users']" />
                    <SubmenuTab label="Reports" :icon="Printer" :menu-items="['Sales', 'Collectibles', 'Purchases']"
                        :menu-routes="['users', 'users', 'users']" />
                    <MenuTab label="Settings" :icon="Cog" route="settings" />
                </nav>
            </SheetContent>
        </Sheet>

        <div class="flex items-center justify-end w-full md:justify-between">
            <Breadcrumb class="hidden md:flex">
                <BreadcrumbList>
                    <template v-for="(item, index) in items" :key="index">
                        <BreadcrumbItem>
                            <BreadcrumbLink v-if="item.href" as-child>
                                <Link :href="item.href">{{ item.label }}</Link>
                            </BreadcrumbLink>
                            <BreadcrumbPage v-else>{{ item.label }}</BreadcrumbPage>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator v-if="index < items.length - 1" />
                    </template>
                </BreadcrumbList>
            </Breadcrumb>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="secondary" size="icon" class="rounded-full">
                        <CircleUser class="w-5 h-5" />
                        <span class="sr-only">Toggle user menu</span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuLabel>
                        <span class="grid">
                            <span>{{ $page.props.auth.user.username }}</span>
                            <span class="text-xs font-normal">
                                {{ $page.props.auth.user.role_id === 1 ? 'Super Admin' :
                                    $page.props.auth.user.role_id === 2 ? 'Admin' : 'Employee' }}
                            </span>
                        </span>
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem>
                        <Link :href="route('users')" class="w-full text-start">
                        Settings
                        </Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem>
                        <Link :href="route('logout')" method="post" as="button" type="button" class="w-full text-start">
                        Logout</Link>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
