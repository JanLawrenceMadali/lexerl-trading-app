<script setup>
import { ref } from "vue";
import Datatable from './Datatable.vue';
import Header from '@/Components/Header.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    inventories: Object
})

const items = ref([
    { label: 'Reports', href: '#' },
    { label: 'Inventory Logs', href: 'inventory_logs' },
])
</script>

<template>
    <Head title="Reports" />
    <AuthenticatedLayout>
        <Header :items="items" />
        <div class="px-4 py-4 sm:py-0 md:gap-8 md:px-6">
            <Card>
                <CardHeader>
                    <CardTitle>Inventory Logs</CardTitle>
                    <CardDescription class="flex items-center justify-between">
                        Manage your purchases and view their information.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Datatable :inventories="inventories" />
                </CardContent>
                <CardFooter>
                </CardFooter>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
