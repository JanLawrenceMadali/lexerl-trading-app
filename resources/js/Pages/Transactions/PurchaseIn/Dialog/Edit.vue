<script setup>
import { cn } from '@/lib/utils'
import { Input } from '@/Components/ui/input'
import { Button } from '@/Components/ui/button'
import { useForm } from '@inertiajs/vue3'
import { computed, reactive, ref, watch } from 'vue'
import { Textarea } from '@/Components/ui/textarea'
import { CalendarIcon, Edit, Loader2, Hash, PhilippinePeso, Plus, Boxes } from 'lucide-vue-next'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import Label from '@/Components/ui/label/Label.vue'
import InputError from '@/Components/InputError.vue'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/Components/ui/dialog'
import { Calendar } from '@/Components/ui/calendar'
import Swal from 'sweetalert2';
import FormattedNumberInput from '@/Components/FormattedNumberInput.vue'
import CreateSubcategory from '../../../Settings/Subcategory/Dialog/CreateSubcategory.vue'
import DropdownSearch from '@/Components/DropdownSearch.vue'
import Create from '@/Pages/Settings/Suppliers/Dialog/Create.vue'

const props = defineProps({
    units: Object,
    inventory: Object,
    suppliers: Object,
    categories: Object,
    transactions: Object,
    subcategories: Object,
})

const data = ref(props.inventory)

const form = useForm({
    amount: data.value.amount,
    quantity: data.value.quantity,
    description: data.value.description,
    landed_cost: data.value.landed_cost,
    purchase_date: data.value.purchase_date,
    supplier_id: data.value.supplier_id,
    category_id: data.value.category_id,
    subcategory_id: data.value.subcategory_id,
    unit_id: String(data.value.unit_id),
    transaction_id: String(data.value.transaction_id),
    transaction_number: data.value.transaction_number,
})

const df = new Intl.DateTimeFormat('en-PH', {
    month: 'numeric',
    day: 'numeric',
    year: 'numeric',
});

const selectedDate = ref()
const isPopoverOpen = ref(false);

const handleDateSelect = (date) => {
    form.sale_date = date;
    isPopoverOpen.value = false;
};

const formatAndSetDate = (date) => {
    if (date) {
        form.purchase_date = df.format(new Date(date));
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
    search: ''
})


const filteredCategory = computed(() => {
    const lowerSearch = state.search.toLowerCase()
    return props.categories.filter((category) =>
        category.name.toLowerCase().includes(lowerSearch),
    )
})

const filteredSubCategory = computed(() => {
    const lowerSearch = state.search.toLowerCase()
    return props.subcategories.filter(
        (subCategory) =>
            subCategory.category_id === form.category_id &&
            subCategory.name.toLowerCase().includes(lowerSearch),
    )
})

const filteredSupplier = computed(() => {
    const lowerSearch = state.search.toLowerCase()
    return props.suppliers.filter((supplier) =>
        supplier.name.toLowerCase().includes(lowerSearch),
    )
})

const isOpen = ref(false);

const closeSheet = () => {
    isOpen.value = false;
};

const routeReload = () => {
    form.get(route('purchase-in'))
}

const inventory = data.value;

const submit = () => {
    form.patch(route('purchase-in.update', inventory), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            closeSheet();
            routeReload();
            Swal.fire({
                title: "Updated!",
                text: "Transaction successfully updated!",
                iconHtml: '<img src="/assets/icons/Success.png">',
                confirmButtonColor: "#1B1212",
            });
        },
        onError: (errors) => {
            // console.log(errors);
        },
    });
};

const isSubmitDisabled = computed(() => {
    const isForm =
        !form.amount ||
        !form.unit_id ||
        !form.quantity ||
        !form.supplier_id ||
        !form.category_id ||
        !form.landed_cost ||
        !form.purchase_date ||
        !form.subcategory_id ||
        !form.transaction_id ||
        !form.transaction_number;
    return isForm || form.processing;
});

</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="ghost" size="xs" title="Edit">
                <Edit :size="18" />
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
                            <div class="grid items-center w-full grid-cols-5 gap-3 text-right">
                                <Label class="after:content-['*'] after:ml-0.5 after:text-red-500 col-span-2">
                                    Transaction Type
                                </Label>
                                <Select v-model="form.transaction_id">
                                    <SelectTrigger
                                        :class="['col-span-3 hover:text-foreground hover:bg-slate-100', !form.transaction_id && 'text-muted-foreground', { 'border-red-600 focus:ring-red-500': form.errors.transaction_id }]">
                                        <SelectValue placeholder="Select transaction type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem v-for="transaction in transactions" :key="transaction.id"
                                                :value="String(transaction.id)">
                                                {{ transaction.type }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError class="col-span-5" :message="form.errors.transaction_id" />
                            </div>
                            <!-- Transaction Number -->
                            <div class="grid items-center grid-cols-5 gap-3 text-right">
                                <Label for="transaction_number"
                                    class="after:content-['*'] after:ml-0.5 after:text-red-500 col-span-2">
                                    Transaction Number
                                </Label>
                                <div class="relative items-center w-full col-span-3">
                                    <Input id="transaction_number" v-model="form.transaction_number" type="text" min="0"
                                        oninput="validity.valid||(value='');"
                                        :class="['pl-7 uppercase', { 'border-red-600 focus-visible:ring-red-500': form.errors.transaction_number }]" />
                                    <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                        <Hash class="size-4 text-muted-foreground" />
                                    </span>
                                </div>
                                <InputError class="col-span-5" :message="form.errors.transaction_number" />
                            </div>
                        </div>

                        <div class="grid items-center gap-3 md:grid-cols-2">
                            <!-- Supplier -->
                            <div class="grid items-center grid-cols-5 gap-3 text-right">
                                <Label class="after:content-['*'] after:ml-0.5 after:text-red-500 col-span-2">
                                    Supplier Name
                                </Label>
                                <DropdownSearch :items="filteredSupplier" label-key="name" value-key="id"
                                    :class="['col-span-3 justify-between font-normal hover:text-foreground hover:bg-slate-100', !form.supplier_id && 'text-muted-foreground', { 'border-red-600 focus:ring-red-500': form.errors.supplier_id }]"
                                    :has-error="form.errors.supplier_id" placeholder="Select a supplier"
                                    v-model="form.supplier_id">
                                    <Create />
                                </DropdownSearch>
                                <InputError class="col-span-5" :message="form.errors.supplier_id" />
                            </div>
                            <!-- Purchase Date -->
                            <div class="grid items-center grid-cols-5 gap-3 text-right">
                                <Label class="after:content-['*'] after:ml-0.5 after:text-red-500 col-span-2">
                                    Purchase Date
                                </Label>
                                <Popover v-model:open="isPopoverOpen">
                                    <PopoverTrigger as-child>
                                        <Button variant="outline"
                                            :class="cn('justify-start text-left font-normal col-span-3', !form.purchase_date && 'text-muted-foreground', { 'border-red-600 focus-visible:ring-red-500': form.errors.purchase_date })">
                                            <CalendarIcon class="mr-2 size-4" />{{ form.purchase_date
                                                ? df.format(new Date(form.purchase_date)) : "mm/dd/yyyy" }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0">
                                        <Calendar v-model="selectedDate" initial-focus
                                            @update:model-value="handleDateSelect" />
                                    </PopoverContent>
                                </Popover>
                                <InputError class="col-span-5" :message="form.errors.purchase_date" />
                            </div>
                        </div>

                        <div class="grid items-center gap-3 md:grid-cols-2">
                            <!-- Category -->
                            <div class="grid items-center grid-cols-5 gap-3 text-right">
                                <Label class="after:content-['*'] after:ml-0.5 after:text-red-500 col-span-2">
                                    Category
                                </Label>
                                <DropdownSearch :items="filteredCategory" label-key="name" value-key="id"
                                    :class="['col-span-3 justify-between font-normal hover:text-foreground hover:bg-slate-100', !form.category_id && 'text-muted-foreground', { 'border-red-600 focus:ring-red-500': form.errors.category_id }]"
                                    :has-error="form.errors.category_id" placeholder="Select a category"
                                    v-model="form.category_id">
                                </DropdownSearch>
                                <InputError class="col-span-5" :message="form.errors.category_id" />
                            </div>
                            <!-- SubCategory -->
                            <div class="grid items-center gap-3 md:text-right md:grid-cols-5">
                                <Label class="col-span-2">
                                    <span class="after:content-['*'] after:ml-0.5 after:text-red-500">
                                        Sub Category
                                    </span>
                                </Label>
                                <DropdownSearch :items="filteredSubCategory" label-key="name" value-key="id"
                                    :class="['col-span-3 justify-between font-normal hover:text-foreground hover:bg-slate-100', !form.subcategory_id && 'text-muted-foreground', { 'border-red-600 focus:ring-red-500': form.errors.subcategory_id }]"
                                    :disabled="!form.category_id" :has-error="form.errors.subcategory_id"
                                    placeholder="Select a sub category" v-model="form.subcategory_id">
                                    <CreateSubcategory />
                                </DropdownSearch>
                                <InputError class="col-span-5" :message="form.errors.subcategory_id" />
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div class="grid grid-cols-1 gap-3 md:gap-4">
                                <div class="grid items-center gap-3 md:text-right md:grid-cols-5">
                                    <!-- Quantity -->
                                    <div class="grid items-center grid-cols-4 col-span-4 gap-3">
                                        <Label for="quantity"
                                            class="after:content-['*'] after:ml-0.5 after:text-red-500 col-span-2">
                                            Quantity
                                        </Label>
                                        <div class="relative items-center w-full col-span-2">
                                            <Input id="quantity" v-model="form.quantity" type="number" min="0"
                                                oninput="validity.valid||(value='');"
                                                :class="['pl-7', { 'border-red-600 focus-visible:ring-red-500': form.errors.quantity }]" />
                                            <span
                                                class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                                <Boxes class="size-4 text-muted-foreground" />
                                            </span>
                                        </div>
                                    </div>
                                    <!-- Unit -->
                                    <div class="col-span-1">
                                        <Select v-model="form.unit_id">
                                            <SelectTrigger
                                                :class="['hover:text-foreground hover:bg-slate-100', !form.unit_id && 'text-muted-foreground', { 'border-red-600 focus:ring-red-500': form.errors.unit_id }]">
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
                                        <InputError :message="form.errors.unit_id" />
                                    </div>
                                </div>
                                <!-- Landed Cost -->
                                <div class="grid items-center grid-cols-5 gap-3 text-right">
                                    <Label for="landed_cost"
                                        class="after:content-['*'] after:ml-0.5 after:text-red-500 col-span-2">
                                        Landed Cost
                                    </Label>
                                    <div class="relative items-center w-full col-span-3">
                                        <Input id="landed_cost" v-model="form.landed_cost" type="number" step=".01"
                                            min="0" oninput="validity.valid||(value='');"
                                            :class="['pl-7', { 'border-red-600 focus-visible:ring-red-500': form.errors.landed_cost }]" />
                                        <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                            <PhilippinePeso class="size-4 text-muted-foreground" />
                                        </span>
                                    </div>
                                    <InputError class="col-span-5" :message="form.errors.landed_cost" />
                                </div>
                                <!-- Amount -->
                                <div class="grid items-center grid-cols-5 gap-3 text-right">
                                    <Label for="amount" class="col-span-2">Amount</Label>
                                    <div class="relative items-center w-full col-span-3">
                                        <FormattedNumberInput v-model="form.amount" disabled
                                            class="pl-7 bg-slate-200" />
                                        <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                            <PhilippinePeso class="size-4 text-muted-foreground" />
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- Additional details -->
                            <div class="grid grid-cols-5 gap-3 text-right">
                                <Label for="description" class="col-span-2 pt-3">Additional Details</Label>
                                <Textarea placeholder="Type your additional details here. (optional)"
                                    v-model="form.description" id="description" class="col-span-3" />
                            </div>
                        </div>

                        <DialogFooter>
                            <DialogClose as-child>
                                <Button type="button" class="bg-[#C00F0C] hover:bg-red-500">
                                    Cancel
                                </Button>
                            </DialogClose>
                            <Button variant="secondary" class="disabled:cursor-not-allowed" type="submit"
                                :disabled="isSubmitDisabled">
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
