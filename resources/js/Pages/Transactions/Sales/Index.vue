<script setup>
import { usePage } from '@inertiajs/vue3';
import { CardHeader } from '@/Components/ui/card';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import Header from '@/Components/Header.vue';
import DataTable from './DataTable.vue';

const props = defineProps({
    sales: Object
})

const page = usePage()
const units = page.props.units
const dues = page.props.dues
const sales = page.props.sales
const customers = page.props.customers
const categories = page.props.categories
const transactions = page.props.transactions
const subcategories = page.props.subcategories

const items = ref([
    { label: 'Transactions', href: '#' },
    { label: 'Sales', href: 'sales' },
])

</script>

<template>

    <Head title="Purchase In" />
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
                        :customers="customers" :transactions="transactions" :units="units" :dues="dues" />
                </CardContent>
                <CardFooter>
                </CardFooter>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>