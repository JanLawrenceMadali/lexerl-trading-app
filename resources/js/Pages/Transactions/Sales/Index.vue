<script setup>
import { CardHeader } from '@/Components/ui/card';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from './DataTable.vue';

const props = defineProps({
    dues: Object,
    units: Object,
    sales: Object,
    products: Object,
    customers: Object,
    categories: Object,
    inventories: Object,
    transactions: Object,
    subcategories: Object,
})

</script>

<template>

    <Head title="Sales" />
    <AuthenticatedLayout>
        <div class="px-4 py-4 sm:py-0 md:gap-8 md:px-6">
            <Card>
                <CardHeader>
                    <CardTitle>Sales</CardTitle>
                    <CardDescription class="flex items-center justify-between">
                        Manage your sales and view their information.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <DataTable :sales="sales" :categories="categories" :subcategories="subcategories"
                        :customers="customers" :transactions="transactions" :units="units" :dues="dues"
                        :inventories="inventories" :products="products" />
                </CardContent>
                <CardFooter>
                </CardFooter>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
