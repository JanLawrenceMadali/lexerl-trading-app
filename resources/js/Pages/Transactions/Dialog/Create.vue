<script setup>
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Sheet, SheetClose, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle, SheetTrigger } from '@/Components/ui/sheet'
import { Check, ChevronsUpDown, Coins, DollarSign, Hash, PlusCircle } from 'lucide-vue-next'
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/Components/ui/command'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import { cn } from '@/lib/utils'
import { Textarea } from '@/Components/ui/textarea'
import { reactive, computed, onMounted, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import CreateSupplier from './CreateSupplier.vue'

const state = reactive({
    search: '',
    open: false,
    purchaseId: null,
    showCreateSupplierDialog: false
})

const props = defineProps({
    tx_docs: Array,
    purchases: Array,
    categories: Array,
    subcategories: Array,
})

const form = useForm({
    cost: null,
    notes: null,
    amount: null,
    tx_doc_id: null,
    category_id: null,
    supplier_id: null,
    tx_doc_number: null,
    subcategory_id: null,
})

watch(() => state.purchaseId, (newId) => {
    if (newId) {
        form.supplier_id = newId;
    }
});

const filteredPurchases = computed(() => {
    return props.purchases.filter(purchase =>
        purchase.supplier.name.toLowerCase().includes(state.search.toLowerCase())
    )
})

const handleSearch = (value) => {
    state.search = value
    // if (filteredPurchases.value.length === 0 && value.trim() !== '') {
    //     state.showCreateSupplierDialog = true
    // }
}

const selectPurchase = (purchaseId) => {
    state.purchaseId = purchaseId;
    const selectedPurchase = props.purchases.find(p => p.id === purchaseId);
    if (selectedPurchase) {
        form.supplier_id = selectedPurchase.supplier.id;
    }
    state.open = false;
};

const selectedPurchase = computed(() =>
    props.purchases.find(purchase => purchase.id === state.purchaseId)
)

const createSupplier = (name) => {
    console.log(`Creating new supplier: ${name}`)
    state.showCreateSupplierDialog = false
}

const submit = async (event) => {
    event.preventDefault()
    try {
        await form.post(route('transactions.store'))
    } catch (error) {
        console.log('Error on submit: ', error);
    }
}

</script>

<template>
    <Sheet>
        <SheetTrigger as-child>
            <Button size="sm" class="gap-1 h-7">
                <PlusCircle class="h-3.5 w-3.5" />
                <span class="sr-only sm:not-sr-only sm:whitespace-nowrap">
                    Add New
                </span>
            </Button>
        </SheetTrigger>
        <SheetContent class="min-w-full sm:min-w-[400px]">
            <SheetHeader>
                <SheetTitle>Add New Transactions</SheetTitle>
                <SheetDescription>
                    Add your transactions here. Click submit when you're done.
                </SheetDescription>
            </SheetHeader>
            <div class="py-4">
                <div class="p-4 border rounded-lg">
                    <form class="grid grid-cols-1 gap-3" @submit.prevent="submit">
                        <!-- tx_doc_id -->
                        <Select v-model="form.tx_doc_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select document type here." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem v-for="tx_doc in tx_docs" :key="tx_doc.id" :value="String(tx_doc.id)">
                                        {{ tx_doc.name }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <!--  -->
                        <div class="relative items-center w-full max-w-sm">
                            <Input v-model="form.tx_doc_number" type="number" placeholder="Tx number" class="pl-7" />
                            <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                <Hash class="size-4 text-muted-foreground" />
                            </span>
                        </div>
                        <!-- amount input -->
                        <div class="relative items-center w-full max-w-sm">
                            <Input v-model="form.amount" type="number" placeholder="Amount" class="pl-7" />
                            <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                <Coins class="size-4 text-muted-foreground" />
                            </span>
                        </div>
                        <!-- category input -->
                        <Select v-model="form.category_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Category" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem v-for="category in categories" :key="category.id"
                                        :value="String(category.id)">
                                        {{ category.name }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <!-- subcategory input -->
                        <Select v-model="form.subcategory_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Sub Category" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem v-for="subcategory in subcategories" :key="subcategory"
                                        :value="String(subcategory.id)">
                                        {{ subcategory.name }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <!-- supplier input -->
                        <Popover v-model:open="state.open">
                            <PopoverTrigger as-child>
                                <Button variant="outline" role="combobox" :aria-expanded="state.open"
                                    class="justify-between">
                                    {{ selectedPurchase?.supplier.name || "Select supplier..." }}
                                    <ChevronsUpDown class="w-4 h-4 ml-2 opacity-50 shrink-0" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-[200px] p-0">
                                <Command>
                                    <CommandInput placeholder="Search supplier..."
                                        @input="handleSearch($event.target.value)" />
                                    <CommandEmpty>
                                        No supplier found.
                                        <Button variant="outline" @click="state.showCreateSupplierDialog = true"
                                            class="mt-2">
                                            Create New Supplier
                                        </Button>
                                    </CommandEmpty>
                                    <CommandList>
                                        <CommandGroup>
                                            <CommandItem v-model="form.supplier_id"
                                                v-for="purchase in filteredPurchases" :key="purchase.id"
                                                :value="purchase.id" @select="selectPurchase(purchase.id)">
                                                {{ purchase.supplier.name }}
                                                <Check :class="cn(
                                                    'ml-auto h-4 w-4',
                                                    state.purchaseId === purchase.id ? 'opacity-100' : 'opacity-0'
                                                )" />
                                            </CommandItem>
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>

                        <CreateSupplier v-model:isOpen="state.showCreateSupplierDialog" @create="createSupplier" />
                        <!-- cost input -->
                        <div class="relative items-center w-full max-w-sm">
                            <Input v-model="form.cost" type="number" placeholder="Cost" class="pl-7" />
                            <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                <DollarSign class="size-4 text-muted-foreground" />
                            </span>
                        </div>
                        <!-- notes input -->
                        <Textarea v-model="form.notes" placeholder="Notes (Optional)" />
                        <SheetFooter>
                            <Button type="submit">Submit</Button>
                        </SheetFooter>
                    </form>
                </div>
            </div>
        </SheetContent>
    </Sheet>
</template>
