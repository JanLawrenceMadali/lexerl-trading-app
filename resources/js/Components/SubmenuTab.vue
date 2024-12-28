<script setup>
import { ref } from 'vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuTrigger } from './ui/dropdown-menu';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    label: { type: String, required: true },
    icon: { type: [Object, Function], required: true },
    route: { type: String, required: false, default: null },
    menuItems: { type: Array, required: true },
    menuRoutes: { type: [Array, String], required: false },
    customeClass: { type: String, required: false, default: null }
});

const getRoute = (index) => {
    if (Array.isArray(props.menuRoutes)) {
        return props.menuRoutes[index];
    }
    return props.menuRoutes;
};

const isTriggered = ref(false);
</script>

<template>
    <DropdownMenu v-model:open="isTriggered">
        <DropdownMenuTrigger as-child>
            <div
                :class="[customeClass, { 'bg-slate-50 text-slate-900': isTriggered },
                    'grid gap-3 cursor-pointer place-items-center hover:bg-slate-100 rounded-2xl p-3 hover:text-primary text-slate-100']">
                <component :is="icon" class="size-8" />
                <span class="text-sm">{{ label }}</span>
            </div>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-56" side="right">
            <DropdownMenuGroup>
                <DropdownMenuItem v-for="(item, index) in menuItems" :key="item">
                    <Link :href="getRoute(index)" class="w-full text-base">
                    {{ item }}
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuGroup>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
