<script setup>
import { usePage } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';
import Create from './Dialog/Create.vue';
import { CardHeader } from '@/Components/ui/card';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import Header from '@/Components/Header.vue';

const props = defineProps({
    purchases: {
        type: Array,
        required: true
    }
})

const page = usePage()
const transactions = page.props.transaction
const purchases = page.props.purchases
const categories = page.props.categories
const subcategories = page.props.subcategories

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};

const formatDate = (dateString) => {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }).format(new Date(dateString));
};

const items = ref([
    { label: 'Transaction', href: '#' },
    { label: 'Purchase In', href: 'purchaseIn' },
])

</script>

<template>
    <Head title="Purchase In"/>
    <AuthenticatedLayout>
        <Header :items="items" />
        <div class="px-4 md:gap-8 md:px-6">
            <Card>
                <CardHeader>
                    <CardTitle>Purchase In</CardTitle>
                    <CardDescription class="flex items-center justify-between">
                        Manage your purchases and view their information.
                    </CardDescription>
                    <Create :purchases="purchases" :categories="categories" :subcategories="subcategories"
                        :transactions="transactions" />
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Transaction Type</TableHead>
                                <TableHead>Transaction Number</TableHead>
                                <TableHead class="hidden md:table-cell">Category</TableHead>
                                <TableHead class="hidden md:table-cell">SubCategory</TableHead>
                                <TableHead class="hidden md:table-cell">Supplier Name</TableHead>
                                <TableHead class="hidden md:table-cell">Cost</TableHead>
                                <TableHead class="hidden md:table-cell">Notes</TableHead>
                                <TableHead class="hidden md:table-cell">
                                    Created at
                                </TableHead>
                                <TableHead>
                                    <span class="sr-only">Actions</span>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody v-for="(purchase, index) in purchases" :key="index">
                            <TableRow>
                                <TableCell>
                                    {{ purchase.transaction.transaction_type }}
                                </TableCell>
                                <TableCell>
                                    {{ purchase.transaction_number }}
                                </TableCell>
                                <TableCell class="hidden font-medium md:table-cell">
                                    {{ purchase.category.name }}
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    {{ purchase.subcategory.name }}
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    {{ purchase.supplier.name }}
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    {{ formatCurrency(purchase.cost) }}
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    {{ purchase.notes }}
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    {{ formatDate(purchase.created_at) }}
                                </TableCell>
                                <TableCell>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button aria-haspopup="true" size="icon" variant="ghost">
                                                <MoreHorizontal class="w-4 h-4" />
                                                <span class="sr-only">Toggle menu</span>
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                            <DropdownMenuItem>Edit</DropdownMenuItem>
                                            <DropdownMenuItem>Delete</DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
                <CardFooter>
                    <div class="text-xs text-muted-foreground">
                        Showing <strong>1-10</strong> of <strong>32</strong> products
                    </div>
                </CardFooter>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>