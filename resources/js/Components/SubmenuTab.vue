<script setup>
import { Menubar, MenubarContent, MenubarItem, MenubarMenu, MenubarTrigger } from './ui/menubar';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    label: { type: String, required: true },
    icon: { type: [Object, Function], required: true },
    route: { type: String, required: false, default: null },
    menuItems: { type: Array, required: true },
    menuRoutes: { type: [Array, String], required: false }
});

const getRoute = (index) => {
    if (Array.isArray(props.menuRoutes)) {
        return props.menuRoutes[index];
    }
    return props.menuRoutes;
};
</script>

<template>
    <Menubar class="grid mb-12 bg-transparent border-none">
        <MenubarMenu>
            <MenubarTrigger class="grid gap-3 cursor-pointer place-items-center hover:bg-slate-100 hover:text-slate-800 text-slate-100">
                <component :is="icon" class="size-8" />
                {{ label }}
            </MenubarTrigger>
            <MenubarContent side="right" class="min-w-32">
                <MenubarItem v-for="(item, index) in menuItems" :key="index">
                    <Link :href="getRoute(index)" class="w-full text-base">
                    {{ item }}
                    </Link>
                </MenubarItem>
            </MenubarContent>
        </MenubarMenu>
    </Menubar>
</template>
