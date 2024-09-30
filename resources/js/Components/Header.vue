<script setup>
import { CircleUser, Home, LineChart, Menu, Package, Package2, ShoppingCart, Users } from 'lucide-vue-next';
import { Sheet, SheetContent, SheetTrigger } from './ui/sheet';
import { Badge } from './ui/badge';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from './ui/dropdown-menu';

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
            <SheetContent side="left" class="flex flex-col">
                <nav class="grid gap-2 text-lg font-medium">
                    <a href="#" class="flex items-center gap-2 text-lg font-semibold">
                        <Package2 class="w-6 h-6" />
                        <span class="sr-only">Acme Inc</span>
                    </a>
                    <a href="#"
                        class="mx-[-0.65rem] flex items-center gap-4 rounded-xl px-3 py-2 text-muted-foreground hover:text-foreground">
                        <Home class="w-5 h-5" />
                        Dashboard
                    </a>
                    <a href="#"
                        class="mx-[-0.65rem] flex items-center gap-4 rounded-xl bg-muted px-3 py-2 text-foreground hover:text-foreground">
                        <ShoppingCart class="w-5 h-5" />
                        Orders
                        <Badge class="flex items-center justify-center w-6 h-6 ml-auto rounded-full shrink-0">
                            6
                        </Badge>
                    </a>
                    <a href="#"
                        class="mx-[-0.65rem] flex items-center gap-4 rounded-xl px-3 py-2 text-muted-foreground hover:text-foreground">
                        <Package class="w-5 h-5" />
                        Products
                    </a>
                    <a href="#"
                        class="mx-[-0.65rem] flex items-center gap-4 rounded-xl px-3 py-2 text-muted-foreground hover:text-foreground">
                        <Users class="w-5 h-5" />
                        Customers
                    </a>
                    <a href="#"
                        class="mx-[-0.65rem] flex items-center gap-4 rounded-xl px-3 py-2 text-muted-foreground hover:text-foreground">
                        <LineChart class="w-5 h-5" />
                        Analytics
                    </a>
                </nav>
            </SheetContent>
        </Sheet>

        <div class="flex items-center justify-between w-full">
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
                    <DropdownMenuItem>Settings</DropdownMenuItem>
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