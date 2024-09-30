<script setup>
import Header from '@/Components/Header.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ListFilter } from 'lucide-vue-next';
import Sales from './Sales/Sales.vue';
import Purchases from './Purchases/Purchases.vue';
import Collectibles from './Collectibles/Collectibles.vue';
import Create from './Dialog/Create.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage()
const tx_docs = page.props.tx_docs
const purchases = page.props.purchases
const categories = page.props.categories
const subcategories = page.props.subcategories

</script>

<template>

    <Head title="Transactions" />
    <AuthenticatedLayout>
        <Header label="Transactions" />
        <div class="grid items-start flex-1 gap-4 p-4 sm:px-6 sm:py-0 md:gap-8">
            <Tabs default-value="purchases">
                <div class="flex items-center">
                    <TabsList>
                        <TabsTrigger value="purchases">
                            Purchases
                        </TabsTrigger>
                        <TabsTrigger value="sales">
                            Sales
                        </TabsTrigger>
                        <TabsTrigger value="collectibles">
                            Collectibles
                        </TabsTrigger>
                    </TabsList>
                    <div class="flex items-center gap-2 ml-auto">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" size="sm" class="gap-1 h-7">
                                    <ListFilter class="h-3.5 w-3.5" />
                                    <span class="sr-only sm:not-sr-only sm:whitespace-nowrap">
                                        Filter
                                    </span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuLabel>Filter by</DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem checked>
                                    Active
                                </DropdownMenuItem>
                                <DropdownMenuItem>Draft</DropdownMenuItem>
                                <DropdownMenuItem>
                                    Archived
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                        <Create :purchases="purchases" :categories="categories" :subcategories="subcategories" :tx_docs="tx_docs" />
                    </div>
                </div>
                <!-- Sales Tabs -->
                <Sales />
                <!-- Purchases Tabs -->
                <Purchases :purchases="purchases"/>
                <!-- Collectibles Tabs -->
                <Collectibles />

            </Tabs>
        </div>
    </AuthenticatedLayout>
</template>