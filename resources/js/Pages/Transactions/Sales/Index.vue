<script setup>
import { CardHeader } from '@/Components/ui/card';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import Header from '@/Components/Header.vue';
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

const dues = props.dues
const units = props.units
const sales = props.sales
const products = props.products
const customers = props.customers
const categories = props.categories
const transactions = props.transactions
const subcategories = props.subcategories

const items = ref([
    { label: 'Transactions', href: '/' },
    { label: 'Sales', href: 'sales' },
])

</script>

<template>

    <Head title="Sales" />
    <AuthenticatedLayout>
        <Header :items="items" />
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
