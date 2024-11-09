<script setup>
import { ref } from 'vue';
import DataTable from './DataTable.vue';
import Header from '@/Components/Header.vue';
import { CardHeader } from '@/Components/ui/card';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    units: Object,
    suppliers: Object,
    categories: Object,
    inventories: Object,
    transactions: Object,
    subcategories: Object,

})

const units = props.units
const suppliers = props.suppliers
const categories = props.categories
const inventories = props.inventories
const transactions = props.transactions
const subcategories = props.subcategories

const items = ref([
    { label: 'Transactions', href: '#' },
    { label: 'Purchases In', href: 'purchase-in' },
])

</script>

<template>

    <Head title="Purchases In" />
    <AuthenticatedLayout>
        <Header :items="items" />
        <div class="px-4 py-4 sm:py-0 md:gap-8 md:px-6">
            <Card>
                <CardHeader>
                    <CardTitle>Purchases In</CardTitle>
                    <CardDescription class="flex items-center justify-between">
                        Manage your purchases and view their information.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <DataTable :inventories="inventories" :categories="categories" :subcategories="subcategories"
                        :suppliers="suppliers" :transactions="transactions" :units="units" />
                </CardContent>
                <CardFooter>
                </CardFooter>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
