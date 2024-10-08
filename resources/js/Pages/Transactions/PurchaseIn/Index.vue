<script setup>
import { usePage } from '@inertiajs/vue3';
import { CardHeader } from '@/Components/ui/card';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import Header from '@/Components/Header.vue';
import DataTable from './DataTable.vue';
import { Toaster } from '@/Components/ui/toast';

const props = defineProps({
    purchases: Object
})

const page = usePage()
const units = page.props.units
const purchases = page.props.purchases
const suppliers = page.props.suppliers
const categories = page.props.categories
const transactions = page.props.transactions
const subcategories = page.props.subcategories

const items = ref([
    { label: 'Transactions', href: '#' },
    { label: 'Purchases In', href: 'purchase-in' },
])

</script>

<template>

    <Head title="Purchase In" />
    <Toaster />
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
                    <DataTable :purchases="purchases" :categories="categories" :subcategories="subcategories"
                        :suppliers="suppliers" :transactions="transactions" :units="units" />
                </CardContent>
                <CardFooter>
                </CardFooter>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>