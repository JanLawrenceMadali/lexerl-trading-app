<script setup>
import { cn } from '@/lib/utils'
import { Input } from '@/Components/ui/input'
import { Button } from '@/Components/ui/button'
import { useForm, usePage } from '@inertiajs/vue3'
import { computed, reactive, ref, watch } from 'vue'
import { Textarea } from '@/Components/ui/textarea'
import { Check, ChevronsUpDown, Edit, PlusCircle } from 'lucide-vue-next'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/Components/ui/command'
import Label from '@/Components/ui/label/Label.vue'
import InputError from '@/Components/InputError.vue'
import { Sheet, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle, SheetTrigger } from '@/Components/ui/sheet'
import { useToast } from '@/Components/ui/toast'
import ErrorToast from '@/Components/ErrorToast.vue'

const props = defineProps({
    units: Object,
    purchases: Object,
    suppliers: Object,
    categories: Object,
    transactions: Object,
    subcategories: Object,
})

const page = usePage()

const form = useForm({
    cost: props.purchases.cost,
    notes: props.purchases.notes,
    quantity: props.purchases.quantity,
    supplier_id: String(props.purchases.supplier_id),
    category_id: String(props.purchases.category_id),
    subcategory_id: String(props.purchases.subcategory_id),
    unit_measure_id: String(props.purchases.unit_measure_id),
    transaction_id: String(props.purchases.transaction_id),
    transaction_number: props.purchases.transaction_number,
})

const state = reactive({
    search: '',
    supplierId: null,
    categoryId: null,
    subCategoryId: null,
    openCategory: false,
    openSupplier: false,
    openSubCategory: false,
    showCreateSupplierDialog: false
})

watch(() => state.supplierId, (newId) => {
    if (newId) {
        form.supplier_id = newId;
    }
});

const filteredCategory = computed(() => {
    return props.categories.filter(category =>
        category.name.toLowerCase().includes(state.search.toLowerCase())
    )
})

const filteredSubCategory = computed(() => {
    return props.subcategories.filter(subCategory =>
        subCategory.name.toLowerCase().includes(state.search.toLowerCase())
    )
})

const filteredSupplier = computed(() => {
    return props.suppliers.filter(supplier =>
        supplier.name.toLowerCase().includes(state.search.toLowerCase())
    )
})

const handleSearch = (value) => {
    state.search = value
}

const selectCategory = (categoryId) => {
    state.categoryId = categoryId;
    const selectedCategory = props.categories.find(category => category.id === categoryId);
    if (selectedCategory) {
        form.category_id = selectedCategory.id;
    }
    state.openCategory = false;
};

const selectSubCategory = (subCategoryId) => {
    state.subCategoryId = subCategoryId;
    const selectedSubCategory = props.subcategories.find(subCategory => subCategory.id === subCategoryId);
    if (selectedSubCategory) {
        form.subcategory_id = selectedSubCategory.id;
    }
    state.openSubCategory = false;
};

const selectSupplier = (supplierId) => {
    state.supplierId = supplierId;
    const selectedSupplier = props.suppliers.find(supplier => supplier.id === supplierId);
    if (selectedSupplier) {
        form.supplier_id = selectedSupplier.id;
    }
    state.openSupplier = false;
};

const selectedCategory = computed(() =>
    props.categories.find(category => category.id == form.category_id)
)

const selectedSubCategory = computed(() =>
    props.subcategories.find(subCategory => subCategory.id == form.subcategory_id)
)

const selectedSupplier = computed(() =>
    props.suppliers.find(supplier => supplier.id == form.supplier_id)
)

const createSupplier = (name) => {
    console.log(`Creating new supplier: ${name}`)
    state.showCreateSupplierDialog = false
}

const isSheetOpen = ref(false);
const emit = defineEmits(['purchase-updated'])

const closeSheet = () => {
    isSheetOpen.value = false;
};

const { toast } = useToast()
const submit = () => {
    form.patch(route('purchase-in.update', props.purchases.id), {
        preserveState: false,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            closeSheet();
            toast({
                title: "Success 🥳",
                description: `${page.props.flash.message}`,
            })
            emit('purchase-updated', props.purchases.id)
        },
        onError: (errors) => {
            const errorMessages = Object.entries(errors)
                .flatMap(([field, messages]) => {
                    if (Array.isArray(messages)) {
                        return messages;
                    }
                    return [messages];
                });

            toast({
                title: "Oops! Something went wrong 😣",
                description: h(ErrorToast, {
                    messages: errorMessages
                }),
                variant: "destructive",
            })
        },
    });
};

</script>

<template>
    <Sheet v-model:open="isSheetOpen">
        <SheetTrigger as-child>
            <Button variant="ghost" size="xs" title="Edit">
                <Edit :size="18" />
            </Button>
        </SheetTrigger>
        <SheetContent class="min-w-full sm:min-w-[800px]">
            <SheetHeader>
                <SheetTitle>Edit Transactions</SheetTitle>
                <SheetDescription>
                    Edit your transactions here.
                </SheetDescription>
            </SheetHeader>
            <div class="py-4">
                <div class="p-4 border rounded-lg">
                    <form @submit.prevent="submit" class="grid gap-6">
                        <div class="grid items-center gap-3 lg:grid-cols-2">
                            <div class="grid gap-3">
                                <Label>Transaction Type</Label>
                                <Select v-model="form.transaction_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select transaction type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem v-for="transaction in transactions" :key="transaction.id"
                                                :value="String(transaction.id)">
                                                {{ transaction.transaction_type }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.transaction_id" />
                            </div>
                            <div class="grid gap-3">
                                <Label for="transaction_number">Transaction Number</Label>
                                <Input id="transaction_number" v-model="form.transaction_number" type="number"
                                    class="w-full" />
                                <InputError :message="form.errors.transaction_number" />
                            </div>
                        </div>
                        <div class="grid items-center gap-3 lg:grid-cols-2">
                            <div class="grid gap-3">
                                <Label>Unit Measure</Label>
                                <Select v-model="form.unit_measure_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select unit type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem v-for="unit in units" :key="unit.id" :value="String(unit.id)">{{
                                                unit.abbreviation }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.unit_measure_id" />
                            </div>
                            <div class="grid gap-3">
                                <Label for="quantity">Quantity</Label>
                                <Input id="quantity" v-model="form.quantity" type="number" class="w-full" />
                                <InputError :message="form.errors.quantity" />
                            </div>
                        </div>
                        <div class="grid items-center gap-3 lg:grid-cols-2">
                            <div class="grid gap-3">
                                <Label>Category</Label>
                                <Popover v-model:open="state.openCategory">
                                    <PopoverTrigger as-child>
                                        <Button variant="outline" role="combobox" :aria-expanded="state.openCategory"
                                            class="justify-between font-normal">{{
                                                selectedCategory?.name || "Select category..." }}
                                            <ChevronsUpDown class="w-4 h-4 ml-2 opacity-50 shrink-0" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="p-0">
                                        <Command>
                                            <CommandInput type="search" placeholder="Search category..."
                                                @input="handleSearch($event.target.value)" />
                                            <CommandEmpty>
                                                No category found.
                                                <Button variant="outline" size="sm"
                                                    @click="state.showCreateSupplierDialog = true" class="mt-2">Create
                                                    new category
                                                </Button>
                                            </CommandEmpty>
                                            <CommandList>
                                                <CommandGroup>
                                                    <CommandItem v-model="form.category_id"
                                                        v-for="category in filteredCategory" :key="category.id"
                                                        :value="category.id" @select="selectCategory(category.id)">
                                                        {{ category.name }}
                                                        <Check
                                                            :class="cn('ml-auto h-4 w-4', state.categoryId === category.id ? 'opacity-100' : 'opacity-0')" />
                                                    </CommandItem>
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                <InputError :message="form.errors.category_id" />
                            </div>
                            <div class="grid gap-3">
                                <Label>Subcategory</Label>
                                <Popover v-model:open="state.openSubCategory">
                                    <PopoverTrigger as-child>
                                        <Button variant="outline" role="combobox" :aria-expanded="state.openSubCategory"
                                            class="justify-between font-normal">{{
                                                selectedSubCategory?.name || "Select subcategory..."
                                            }}
                                            <ChevronsUpDown class="w-4 h-4 ml-2 opacity-50 shrink-0" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="p-0">
                                        <Command>
                                            <CommandInput type="search" placeholder="Search subcategory..."
                                                @input="handleSearch($event.target.value)" />
                                            <CommandEmpty>
                                                No subcategory found.
                                                <Button variant="outline" size="sm"
                                                    @click="state.showCreateSupplierDialog = true" class="mt-2">Create
                                                    new subcategory
                                                </Button>
                                            </CommandEmpty>
                                            <CommandList>
                                                <CommandGroup>
                                                    <CommandItem v-model="form.subcategory_id"
                                                        v-for="subCategory in filteredSubCategory" :key="subCategory.id"
                                                        :value="subCategory.id"
                                                        @select="selectSubCategory(subCategory.id)">
                                                        {{ subCategory.name }}
                                                        <Check
                                                            :class="cn('ml-auto h-4 w-4', state.subCategoryId === subCategory.id ? 'opacity-100' : 'opacity-0')" />
                                                    </CommandItem>
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                <InputError :message="form.errors.subcategory_id" />
                            </div>
                        </div>
                        <div class="grid items-center gap-3 lg:grid-cols-2">
                            <div class="grid gap-3">
                                <Label>Supplier</Label>
                                <Popover v-model:open="state.openSupplier">
                                    <PopoverTrigger as-child>
                                        <Button variant="outline" role="combobox" :aria-expanded="state.openSupplier"
                                            class="justify-between font-normal">{{
                                                selectedSupplier?.name || "Select Supplier..." }}
                                            <ChevronsUpDown class="w-4 h-4 ml-2 opacity-50 shrink-0" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="p-0">
                                        <Command>
                                            <CommandInput type="search" placeholder="Search supplier..."
                                                @input="handleSearch($event.target.value)" />
                                            <CommandEmpty>
                                                No supplier found.
                                                <Button variant="outline" size="sm"
                                                    @click="state.showCreateSupplierDialog = true" class="mt-2">Create
                                                    new supplier
                                                </Button>
                                            </CommandEmpty>
                                            <CommandList>
                                                <CommandGroup>
                                                    <CommandItem v-model="form.supplier_id"
                                                        v-for="supplier in filteredSupplier" :key="supplier.id"
                                                        :value="supplier.id" @select="selectSupplier(supplier.id)">
                                                        {{ supplier.name }}
                                                        <Check
                                                            :class="cn('ml-auto h-4 w-4', state.supplierId === supplier.id ? 'opacity-100' : 'opacity-0')" />
                                                    </CommandItem>
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                <InputError :message="form.errors.supplier_id" />
                            </div>
                            <div class="grid gap-3">
                                <Label for="cost">Cost</Label>
                                <Input id="cost" v-model="form.cost" type="number" class="w-full" />
                                <InputError :message="form.errors.cost" />
                            </div>
                        </div>
                        <div class="grid gap-3">
                            <Label for="description">Notes/Description <span
                                    class="text-slate-500">(Optional)</span></Label>
                            <Textarea v-model="form.notes" id="description" class="min-h-32" />
                        </div>
                        <SheetFooter>
                            <Button class="disabled:cursor-progress" :disabled="form.processing"
                                type="submit">Submit</Button>
                        </SheetFooter>
                    </form>
                </div>
            </div>
        </SheetContent>
    </Sheet>
</template>
