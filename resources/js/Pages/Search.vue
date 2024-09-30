<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/Components/ui/dropdown-menu'
import { Sheet, SheetContent, SheetTrigger } from '@/Components/ui/sheet';
import { CircleUser, Home, LineChart, Menu, Package, Package2, ShoppingCart, Users } from 'lucide-vue-next';
import { Badge } from '@/Components/ui/badge';
import Header from '@/Components/Header.vue';
import { ref } from 'vue';
const items = ref([
    { label: 'Search', href: 'search' },
])
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Search" />
        <Header :items="items" />
    </AuthenticatedLayout>
</template>