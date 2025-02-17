<script setup>
import { Eye } from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog'
import { Separator } from '@/Components/ui/separator'
import { ScrollArea } from '@/Components/ui/scroll-area';
import { watch, ref } from 'vue';

const props = defineProps({ inventory: Object })

const data = ref(props.inventory)

const formatCurrency = (value) => {
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

watch(() => props.inventory, (newInventory) => {
    data.value = newInventory
}, { immediate: true })
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="ghost" size="xs" title="View">
                <Eye :size="18" />
            </Button>
        </DialogTrigger>
        <DialogContent class="p-0">
            <DialogHeader class="px-6 pt-6">
                <DialogTitle>
                    {{ data.transaction_type }} - {{ data.transaction_number }}
                </DialogTitle>
                <DialogDescription>
                    Purchase Date: {{ data.purchase_date }}
                </DialogDescription>
            </DialogHeader>
            <ScrollArea class="h-[600px] m-2">
                <div class="grid gap-3 p-4 text-sm">
                    <div class="font-semibold"> Supplier Details </div>
                    <ul class="grid gap-3">
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Name</span> <span>{{ data.supplier_name }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Email</span> <span>{{ data.supplier_email }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground ">Address 1</span> <span class="w-1/2 text-right">{{
                                data.supplier_address1 }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground ">Address 2</span> <span class="w-1/2 text-right">{{
                                data.supplier_address2 }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground ">Contact Person</span> <span>{{
                                data.supplier_contact_person }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground ">Contact Number</span> <span>{{
                                data.supplier_contact_number }}</span>
                        </li>
                    </ul>

                    <div class="font-semibold"> Product Details </div>
                    <ul class="grid gap-3">
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Category</span> <span>{{ data.category_name }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Sub Category</span> <span>{{ data.subcategory_name
                                }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Quantity</span> <span>{{ formattedNumber(data.quantity) }} {{
                                data.abbreviation }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Landed Cost</span> <span>{{ formatCurrency(data.landed_cost) }}</span>
                        </li>
                        <Separator />
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Amount</span> <span>{{ formatCurrency(data.amount) }}</span>
                        </li>
                        <li class="grid gap-1 py-2">
                            <span class="text-muted-foreground">Notes:</span>
                            <span>{{ data?.description || 'No notes found.' }}</span>
                        </li>
                    </ul>
                </div>
            </ScrollArea>
            <DialogFooter class="px-6 pb-6">
                <DialogClose as-child>
                    <Button type="button" variant="secondary">
                        Close
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
