<script setup>
import { ref } from 'vue';
import DataTable from './DataTable.vue';
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

const units = ref(props.units)
const suppliers = ref(props.suppliers)
const categories = ref(props.categories)
const inventories = ref(props.inventories)
const transactions = ref(props.transactions)
const subcategories = ref(props.subcategories)

</script>

<template>

    <Head title="Purchases In" />
    <AuthenticatedLayout>
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
