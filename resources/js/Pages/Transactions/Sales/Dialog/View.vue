<script setup>
import { Eye } from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog'
import { ref } from "vue";
import { ScrollArea } from '@/Components/ui/scroll-area';
import { watch } from 'vue';

const props = defineProps({ sales: Object })

const data = ref(props.sales)

const formattedCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};

const formattedNumber = (value) => {
    return new Intl.NumberFormat().format(value);
};

watch(() => props.sales, (newSales) => {
    data.value = newSales
}, { immediate: true })
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="ghost" size="xs" title="View">
                <Eye :size="18" />
            </Button>
        </DialogTrigger>
        <DialogContent class="grid-rows-[auto_minmax(0,1fr)_auto] p-0 max-h-[90dvh]">
            <DialogHeader class="p-6 pb-0">
                <DialogTitle>
                    {{ data.transaction_type }} - {{ data.transaction_number }}
                </DialogTitle>
                <DialogDescription class="space-y-1">
                        <div>Sale Date: {{ data.sale_date }}</div>
                        <div>Payment Method: {{ data.status }}</div>
                </DialogDescription>
            </DialogHeader>
            <ScrollArea class="h-[600px] m-2">
                <div class="grid gap-4 px-6 py-4 overflow-y-auto text-sm">
                    <div class="font-semibold"> Customer Details </div>
                    <ul class="grid gap-3">
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Name</span>
                            <span>{{ data.customer_name }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Email</span>
                            <span>{{ data.customer_email }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground ">Address 1</span>
                            <span class="w-1/2 text-right">{{ data.customer_address1 }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground ">Address 2</span>
                            <span class="w-1/2 text-right">{{ data.customer_address2 }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground ">Contact Person</span>
                            <span>{{ data.customer_contact_person }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground ">Contact Number</span>
                            <span>{{ data.customer_contact_number }}</span>
                        </li>
                    </ul>

                    <div class="font-semibold"> Product Details </div>
                    <ul v-for="product in data.inventory_sale" class="grid gap-3 p-4 border rounded-lg">
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Category</span> <span>{{ product.category_name }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Sub Category</span> <span>{{ product.subcategory_name
                                }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Quantity</span> <span>
                                {{ formattedNumber(product.quantity) }} {{ product.abbreviation }}
                            </span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Selling Price</span> <span>{{
                                formattedCurrency(product.selling_price) }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Amount</span> <span>{{ formattedCurrency(product.amount)
                                }}</span>
                        </li>
                    </ul>

                    <span class="flex items-center justify-between">
                        <span class="text-muted-foreground">Total Amount:</span>
                        <span>{{ formattedCurrency(data.total_amount) }}</span>
                    </span>
                    <span class="grid gap-1 py-2">
                        <span class="text-muted-foreground">Notes:</span>
                        <span>{{ data?.description || 'No notes found.' }}</span>
                    </span>
                </div>
            </ScrollArea>
            <DialogFooter class="p-6 pt-0">
                <DialogClose as-child>
                    <Button type="button" class="h-7" variant="secondary">
                        Close
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
