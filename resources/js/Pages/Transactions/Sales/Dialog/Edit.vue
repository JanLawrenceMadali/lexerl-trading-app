<script setup>
import { cn } from '@/lib/utils'
import { Input } from '@/Components/ui/input'
import { Button } from '@/Components/ui/button'
import { useForm } from '@inertiajs/vue3'
import { computed, reactive, ref, watch } from 'vue'
import { Textarea } from '@/Components/ui/textarea'
import { CalendarIcon, Check, ChevronDown, ChevronsUpDown, Edit, HandCoins, Hash, Loader2, PhilippinePeso, Plus, Boxes } from 'lucide-vue-next'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/Components/ui/command'
import Label from '@/Components/ui/label/Label.vue'
import InputError from '@/Components/InputError.vue'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/Components/ui/dialog'
import { Calendar } from '@/Components/ui/calendar'

const props = defineProps({
    units: Object,
    dues: Object,
    sales: Object,
    customers: Object,
    categories: Object,
    transactions: Object,
    subcategories: Object,
})

const form = useForm({
    notes: props.sales.notes,
    amount: props.sales.amount,
    is_paid: String(props.sales.is_paid),
    quantity: props.sales.quantity,
    landed_cost: props.sales.landed_cost,
    sales_date: props.sales.sales_date,
    due_date_id: String(props.sales.due_date_id),
    customer_id: String(props.sales.customer_id),
    category_id: String(props.sales.category_id),
    subcategory_id: String(props.sales.subcategory_id),
    unit_measure_id: String(props.sales.unit_measure_id),
    transaction_id: String(props.sales.transaction_id),
    transaction_number: props.sales.transaction_number,
})

const df = new Intl.DateTimeFormat('en-PH', {
    month: '2-digit',
    day: '2-digit',
    year: 'numeric',
});

const selectedDate = ref()

const formatAndSetDate = (date) => {
    if (date) {
        form.sales_date = df.format(new Date(date));
    }
};

watch(() => selectedDate.value, (newDate) => {
    formatAndSetDate(newDate);
});

watch(
    () => [form.quantity, form.landed_cost],
    ([quantity, landed_cost]) => {
        form.amount = quantity && landed_cost ? quantity * landed_cost : null
    }
)

const state = reactive({
    search: '',
    customerId: null,
    categoryId: null,
    subCategoryId: null,
    openCategory: false,
    openCustomer: false,
    openSubCategory: false,
    showCreateCustomerDialog: false
})

watch(() => state.customerId, (newId) => {
    if (newId) {
        form.customer_id = newId;
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

const filteredCustomer = computed(() => {
    return props.customers.filter(customer =>
        customer.name.toLowerCase().includes(state.search.toLowerCase())
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

const selectCustomer = (customerId) => {
    state.customerId = customerId;
    const selectedCustomer = props.customers.find(customer => customer.id === customerId);
    if (selectedCustomer) {
        form.customer_id = selectedCustomer.id;
    }
    state.openCustomer = false;
};

const selectedCategory = computed(() =>
    props.categories.find(category => category.id == form.category_id)
)

const selectedSubCategory = computed(() =>
    props.subcategories.find(subCategory => subCategory.id == form.subcategory_id)
)

const selectedCustomer = computed(() =>
    props.customers.find(customer => customer.id == form.customer_id)
)

const createCustomer = (name) => {
    console.log(`Creating new customer: ${name}`)
    state.showCreateCustomerDialog = false
}

const isOpen = ref(false);
const emit = defineEmits(['sales-updated'])

const closeSheet = () => {
    isOpen.value = false;
};

const submit = () => {
    form.patch(route('sales.update', props.sales.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            form.get(route('sales'))
            closeSheet();
            emit('sales-updated', props.sales.id)
        },
        onError: (errors) => {
            console.log(errors);
        },
    });
};

const isSubmitDisabled = computed(() => {
    const isForm = !form.amount || !form.quantity || !form.customer_id || !form.category_id || !form.landed_cost || !form.sales_date || !form.subcategory_id || !form.unit_measure_id || !form.transaction_id || !form.transaction_number;
    return isForm || form.processing;
});

</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="ghost" size="xs" title="Edit">
                <Edit :size="18" class="text-green-500" />
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:min-w-[400px] md:min-w-[1500px]">
            <DialogHeader>
                <DialogTitle>Edit Transactions</DialogTitle>
                <DialogDescription>
                    Edit your transactions here.
                </DialogDescription>
            </DialogHeader>
            <div class="py-4">
                <div class="p-4 border rounded-lg">
                    <form @submit.prevent="submit" class="grid gap-3 md:gap-4">
                        <div class="grid items-center gap-3 md:grid-cols-2">
                            <!-- Transaction Type -->
                            <div class="grid items-center gap-3 md:w-full md:text-right md:grid-cols-5">
                                <Label class="md:col-span-2">
                                    <span class="after:content-['*'] after:ml-0.5 after:text-red-500">
                                        Transaction Type
                                    </span>
                                </Label>
                                <Select v-model="form.transaction_id">
                                    <SelectTrigger
                                        :class="['col-span-3', { 'border-red-600 focus:ring-red-500': form.errors.transaction_id }]">
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
                                <InputError class="col-span-5" :message="form.errors.transaction_id" />
                            </div>
                            <!-- Transaction Number -->
                            <div class="grid items-center gap-3 md:text-right md:grid-cols-5">
                                <Label for="transaction_number" class="md:col-span-2">
                                    <span class="after:content-['*'] after:ml-0.5 after:text-red-500">
                                        Transaction Number
                                    </span>
                                </Label>
                                <div class="relative items-center w-full md:col-span-3">
                                    <Input id="transaction_number" v-model="form.transaction_number" type="number" min=0
                                        oninput="validity.valid||(value='');"
                                        :class="['pl-8', { 'border-red-600 focus-visible:ring-red-500': form.errors.transaction_number }]" />
                                    <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                        <Hash class="size-5 text-muted-foreground" />
                                    </span>
                                </div>
                                <InputError class="col-span-5" :message="form.errors.transaction_number" />
                            </div>
                        </div>
                        <div class="grid items-center gap-3 md:grid-cols-2">
                            <!-- Customer -->
                            <div class="grid items-center gap-3 md:text-right md:grid-cols-5">
                                <Label class="col-span-2">
                                    <span class="after:content-['*'] after:ml-0.5 after:text-red-500">Customer</span>
                                </Label>
                                <Popover v-model:open="state.openCustomer">
                                    <PopoverTrigger as-child
                                        :class="['col-span-3', { 'border-red-600 focus:ring-red-500': form.errors.transaction_id }]">
                                        <Button variant="outline" role="combobox" :aria-expanded="state.openCustomer"
                                            class="justify-between font-normal">{{
                                                selectedCustomer?.name || "Select customer" }}
                                            <ChevronDown class="w-4 h-4 ml-2 opacity-50 shrink-0" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-[420px] p-0">
                                        <Command>
                                            <CommandInput type="search" placeholder="Search customer"
                                                @input="handleSearch($event.target.value)" />
                                            <Button variant="outline" size="sm"
                                                @click="state.showCreateCustomerDialog = true" class="m-2">
                                                <Plus class="mr-1 size-4" />
                                                Add new customer
                                            </Button>
                                            <CommandEmpty>
                                                No customer found.
                                            </CommandEmpty>
                                            <CommandList>
                                                <CommandGroup>
                                                    <CommandItem v-model="form.customer_id"
                                                        v-for="customer in filteredCustomer" :key="customer.id"
                                                        :value="customer.id" @select="selectCustomer(customer.id)">
                                                        {{ customer.name }}
                                                        <Check
                                                            :class="cn('ml-auto h-4 w-4', state.customerId === customer.id ? 'opacity-100' : 'opacity-0')" />
                                                    </CommandItem>
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                <InputError class="col-span-5" :message="form.errors.customer_id" />
                            </div>
                            <!-- Purchase Date -->
                            <div class="grid items-center gap-3 md:text-right md:grid-cols-5">
                                <Label class="md:col-span-2">
                                    <span class="after:content-['*'] after:ml-0.5 after:text-red-500">
                                        Purchase Date
                                    </span>
                                </Label>
                                <Popover>
                                    <PopoverTrigger as-child
                                        :class="['col-span-3', { 'border-red-600 focus:ring-red-500': form.errors.sales_date }]">
                                        <Button variant="outline"
                                            :class="cn('justify-start text-left font-normal', !form.sales_date && 'text-muted-foreground')">
                                            <CalendarIcon class="w-4 h-4 mr-2" />
                                            {{ form.sales_date
                                                ? df.format(new Date(form.sales_date))
                                                : "Pick a date"
                                            }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0">
                                        <Calendar v-model="selectedDate" initial-focus />
                                    </PopoverContent>
                                </Popover>
                                <InputError class="col-span-5" :message="form.errors.sales_date" />
                            </div>
                        </div>

                        <div class="grid items-center gap-3 md:grid-cols-2">
                            <!-- Category -->
                            <div class="grid items-center gap-3 md:text-right md:grid-cols-5">
                                <Label class="md:col-span-2">
                                    <span class="after:content-['*'] after:ml-0.5 after:text-red-500">Category</span>
                                </Label>
                                <Popover v-model:open="state.openCategory">
                                    <PopoverTrigger as-child
                                        :class="['col-span-3', { 'border-red-600 focus:ring-red-500': form.errors.transaction_id }]">
                                        <Button variant="outline" role="combobox" :aria-expanded="state.openCategory"
                                            class="justify-between font-normal">{{
                                                selectedCategory?.name || "Select category" }}
                                            <ChevronDown class="ml-2 opacity-50 size-4 shrink-0" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-[420px] p-0">
                                        <Command>
                                            <CommandInput type="search" placeholder="Search category"
                                                @input="handleSearch($event.target.value)" />
                                            <Button variant="outline" size="sm"
                                                @click="state.showCreateCustomerDialog = true" class="m-2">
                                                <Plus class="mr-1 size-4" />
                                                Add new category
                                            </Button>
                                            <CommandEmpty>
                                                No category found.
                                            </CommandEmpty>
                                            <CommandList>
                                                <CommandGroup>
                                                    <CommandItem v-model="form.category_id"
                                                        v-for="category in filteredCategory" :key="category.id"
                                                        :value="category.id" @select="selectCategory(category.id)">
                                                        {{ category.name }}
                                                        <Check
                                                            :class="cn('ml-auto size-4', state.categoryId === category.id ? 'opacity-100' : 'opacity-0')" />
                                                    </CommandItem>
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                <InputError class="col-span-5" :message="form.errors.category_id" />
                            </div>
                            <!-- SubCategory -->
                            <div class="grid items-center gap-3 md:text-right md:grid-cols-5">
                                <Label class="col-span-2">
                                    <span class="after:content-['*'] after:ml-0.5 after:text-red-500">Subcategory</span>
                                </Label>
                                <Popover v-model:open="state.openSubCategory">
                                    <PopoverTrigger as-child
                                        :class="['col-span-3', { 'border-red-600 focus:ring-red-500': form.errors.transaction_id }]">
                                        <Button variant="outline" role="combobox" :aria-expanded="state.openSubCategory"
                                            class="justify-between font-normal">{{
                                                selectedSubCategory?.name || "Select subcategory"
                                            }}
                                            <ChevronDown class="w-4 h-4 ml-2 opacity-50 shrink-0" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-[420px] p-0">
                                        <Command>
                                            <CommandInput type="search" placeholder="Search subcategory"
                                                @input="handleSearch($event.target.value)" />
                                            <Button variant="outline" size="sm"
                                                @click="state.showCreateCustomerDialog = true" class="m-2">
                                                <Plus class="mr-1 size-4" />
                                                Add new subcategory
                                            </Button>
                                            <CommandEmpty>
                                                No subcategory found.
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
                                <InputError class="col-span-5" :message="form.errors.subcategory_id" />
                            </div>
                        </div>

                        <div class="grid items-center gap-3 md:grid-cols-2">
                            <div class="grid items-center gap-3 md:text-right md:grid-cols-5">
                                <!-- Quantity -->
                                <div class="grid items-center grid-cols-4 col-span-4 gap-3">
                                    <Label for="quantity" class="col-span-2">
                                        <span
                                            class="after:content-['*'] after:ml-0.5 after:text-red-500">Quantity</span>
                                    </Label>
                                    <div class="relative items-center w-full md:col-span-2">
                                        <Input id="quantity" v-model="form.quantity" type="number" min=0
                                            oninput="validity.valid||(value='');"
                                            :class="['pl-8', { 'border-red-600 focus-visible:ring-red-500': form.errors.quantity }]" />
                                        <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                            <Boxes class="size-5 text-muted-foreground" />
                                        </span>
                                    </div>
                                </div>

                                <!-- Unit -->
                                <div class="col-span-1">
                                    <Select v-model="form.unit_measure_id">
                                        <SelectTrigger
                                            :class="{ 'border-red-600 focus:ring-red-500': form.errors.unit_measure_id }">
                                            <SelectValue placeholder="Unit" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem v-for="unit in units" :key="unit.id"
                                                    :value="String(unit.id)">{{ unit.abbreviation }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="col-span-5">
                                    <InputError :message="form.errors.quantity" />
                                    <InputError :message="form.errors.unit_measure_id" />
                                </div>
                            </div>
                            <div class="grid items-center gap-3 md:text-right md:grid-cols-5">
                                <!-- Payment -->
                                <div class="grid items-center grid-cols-4 col-span-4 gap-3">
                                    <Label for="is_paid" class="col-span-2">
                                        <span class="after:content-['*'] after:ml-0.5 after:text-red-500">Payment</span>
                                    </Label>
                                    <div class="relative items-center w-full md:col-span-2">
                                        <Input id="is_paid" v-model="form.is_paid" type="number" min=0
                                            oninput="validity.valid||(value='');"
                                            :class="['pl-8', { 'border-red-600 focus-visible:ring-red-500': form.errors.is_paid }]" />
                                        <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                            <HandCoins class="size-5 text-muted-foreground" />
                                        </span>
                                    </div>
                                </div>

                                <!-- Due Date -->
                                <div class="col-span-1">
                                    <Select v-model="form.due_date_id">
                                        <SelectTrigger
                                            :class="{ 'border-red-600 focus:ring-red-500': form.errors.due_date_id }">
                                            <SelectValue placeholder="Due Date" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem v-for="due in dues" :key="due.id" :value="String(due.id)">{{
                                                    due.days }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="col-span-5">
                                    <InputError :message="form.errors.quantity" />
                                    <InputError :message="form.errors.due_date_id" />
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div class="grid grid-cols-1 gap-3 md:gap-4">
                                <!-- Landed Cost -->
                                <div class="grid items-center gap-3 md:text-right md:grid-cols-5">
                                    <Label for="landed_cost" class="md:col-span-2">
                                        <span class="after:content-['*'] after:ml-0.5 after:text-red-500">
                                            Landed Cost
                                        </span>
                                    </Label>
                                    <div class="relative items-center w-full md:col-span-3">
                                        <Input id="landed_cost" v-model="form.landed_cost" type="number" min=0
                                            oninput="validity.valid||(value='');"
                                            :class="['pl-8', { 'border-red-600 focus-visible:ring-red-500': form.errors.landed_cost }]" />
                                        <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                            <PhilippinePeso class="size-5 text-muted-foreground" />
                                        </span>
                                    </div>
                                    <InputError class="col-span-5" :message="form.errors.landed_cost" />
                                </div>
                                <!-- Amount -->
                                <div class="grid items-center gap-3 md:text-right md:grid-cols-5">
                                    <Label for="amount" class="md:col-span-2">Amount</Label>
                                    <div class="relative items-center w-full md:col-span-3">
                                        <Input id="amount" v-model="form.amount" type="number" disabled
                                            class="pl-8 bg-slate-200" />
                                        <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                            <PhilippinePeso class="size-5 text-muted-foreground" />
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- Additional details -->
                            <div class="grid gap-3 md:text-right md:grid-cols-5">
                                <Label for="description" class="py-3 md:col-span-2">Additional Details</Label>
                                <Textarea v-model="form.notes" id="description" class="md:col-span-3 min-h-30" />
                            </div>
                        </div>

                        <DialogFooter>
                            <DialogClose as-child>
                                <Button type="button" class="bg-[#C00F0C] hover:bg-red-500">
                                    Cancel
                                </Button>
                            </DialogClose>
                            <Button variant="secondary" class="disabled:cursor-not-allowed" :disabled="isSubmitDisabled"
                                type="submit">
                                <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                                Submit
                            </Button>
                        </DialogFooter>
                    </form>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
