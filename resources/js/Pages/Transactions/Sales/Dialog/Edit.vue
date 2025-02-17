<script setup>
import { cn } from '@/lib/utils'
import { Input } from '@/Components/ui/input'
import { Button } from '@/Components/ui/button'
import { useForm } from '@inertiajs/vue3'
import { computed, reactive, ref, watch } from 'vue'
import { Textarea } from '@/Components/ui/textarea'
import { CalendarIcon, Trash2, Edit, Hash, Loader2, PhilippinePeso, Plus, Boxes } from 'lucide-vue-next'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import Label from '@/Components/ui/label/Label.vue'
import InputError from '@/Components/InputError.vue'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/Components/ui/dialog'
import { Calendar } from '@/Components/ui/calendar'
import DropdownSearch from '@/Components/DropdownSearch.vue'
import FormattedNumberInput from '@/Components/FormattedNumberInput.vue'
import Create from '@/Pages/Settings/Customers/Dialog/Create.vue'
import Swal from 'sweetalert2';

const props = defineProps({
    dues: Object,
    sales: Object,
    units: Object,
    customers: Object,
    categories: Object,
    inventories: Object,
    transactions: Object,
    subcategories: Object,
})

const sales = ref({ ...props.sales });

const form = useForm({
    status_id: String(sales.value.status_id),
    sale_date: sales.value.sale_date,
    due_date_id: sales.value.due_date_id === null ? sales.value.due_date_id : String(sales.value.due_date_id),
    description: sales.value.description,
    customer_id: sales.value.customer_id,
    transaction_id: String(sales.value.transaction_id),
    transaction_number: sales.value.transaction_number,
    total_amount: sales.value.total_amount,
    products: sales.value.inventory_sale.map((product) => ({
        amount: product.amount,
        quantity: product.quantity,
        selling_price: product.selling_price,
        unit_id: String(product.unit_id),
        category_id: String(product.category_id),
        subcategory_id: String(product.subcategory_id),
    })),
    productDeleted: [],
});


const addProduct = () => {
    form.products.push({
        amount: null,
        unit_id: null,
        quantity: null,
        category_id: null,
        selling_price: null,
        subcategory_id: null,
    });
};

const removeProduct = (product, index) => {
    form.products.splice(index, 1);
    if (product) {
        form.productDeleted = [...form.productDeleted, product];
    }
}

const df = new Intl.DateTimeFormat('en-PH', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
});

const selectedDate = ref(null)

const isPopoverOpen = ref(false);

const handleDateSelect = (date) => {
    form.sale_date = date;
    isPopoverOpen.value = false;
};

const formatAndSetDate = (date) => {
    if (date) {
        form.sale_date = df.format(new Date(date));
    }
};

watch(() => selectedDate.value, (newDate) => {
    formatAndSetDate(newDate);
});

watch(() => form.products, (newProducts) => {
    const total = newProducts.reduce((total, product) => {
        return total + (product.amount || 0);
    }, 0);
    form.total_amount = Number(total.toFixed(2));
}, { deep: true });

watch(() => form.products, (newProducts) => {
    newProducts.forEach((product, index) => {
        const { quantity, selling_price } = product;
        form.products[index].amount = quantity && selling_price ? quantity * selling_price : null;
    });
},
    { deep: true }
);

watch(() => props.sales, (newSale) => {
    sales.value = { ...newSale };
}, { deep: true })

watch(() => props.sales, (newSale) => {
    form.status_id = String(newSale.status_id);
    form.sale_date = newSale.sale_date;
    form.due_date_id = newSale.due_date_id === null ? newSale.due_date_id : String(newSale.due_date_id);
    form.description = newSale.description;
    form.customer_id = newSale.customer_id;
    form.transaction_id = String(newSale.transaction_id);
    form.transaction_number = newSale.transaction_number;
    form.total_amount = newSale.total_amount;
    form.products = newSale.inventory_sale.map((product) => ({
        amount: product.amount,
        quantity: product.quantity,
        selling_price: product.selling_price,
        unit_id: String(product.unit_id),
        category_id: String(product.category_id),
        subcategory_id: String(product.subcategory_id),
    }));
},
    { deep: true }
);

const state = reactive({
    search: ''
})

const filteredCustomer = computed(() => {
    const lowerSearch = state.search.toLowerCase()
    return props.customers.filter((customer) =>
        customer.name.toLowerCase().includes(lowerSearch),
    )
})

const filteredCategory = computed(() => {
    return props.categories.filter(category =>
        props.inventories.some(inventory => inventory.category_id === category.id
        )
    )
});

const filteredSubcategory = (categoryId) => {
    return props.subcategories.filter(subcategory =>
        props.inventories.some(inventory => inventory.subcategory_id === subcategory.id &&
            inventory.category_id == categoryId
        )
    );
};

const filteredUnit = (products) => {
    return props.units.filter(unit =>
        props.inventories.some(inventory => inventory.unit_id === unit.id
            && inventory.subcategory_id == products.subcategory_id
            && inventory.category_id == products.category_id
        )
    )
};

const totalQuantity = computed(() => {
    if (!form.products || form.products.length === 0) return 0;

    const calculateProductQuantity = (product) => {
        const { category_id, subcategory_id, unit_id, children } = product;

        // Return 0 if all are null
        if (!category_id && !subcategory_id && !unit_id) return 0;

        const productTotal = props.inventories.reduce((total, item) => {
            const isMatchingCategory = !category_id || category_id == item.category_id;
            const isMatchingSubcategory = !subcategory_id || subcategory_id == item.subcategory_id;
            const isMatchingUnit = !unit_id || unit_id == item.unit_id;

            if (isMatchingCategory && isMatchingSubcategory && isMatchingUnit) {
                return total + item.quantity;
            }
            return total;
        }, 0);

        const childrenTotals = children && children.length > 0
            ? children.map(calculateProductQuantity)
            : [];

        return [productTotal, ...childrenTotals.flat()];
    };

    return form.products.flatMap(calculateProductQuantity);
});

const isOpen = ref(false);

const closeModal = () => {
    isOpen.value = false;
    form.reset();
    form.clearErrors();
};

const emit = defineEmits(['update-sale']);

const submit = () => {
    form.patch(route('sales.update', props.sales.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (response) => {
            emit('update-sale', response.props.sales);
            closeModal();
            if (response.props.flash.success) {
                Swal.fire({
                    text: response.props.flash.success,
                    iconHtml: '<img src="/assets/icons/Success.png">',
                    confirmButtonColor: "#1B1212",
                });
            } else {
                Swal.fire({
                    text: response.props.flash.error,
                    icon: 'error',
                    confirmButtonColor: "#1B1212",
                });
            }
        },
        onError: (errors) => {
            // console.log(errors);
        },
    });
};

const isSubmitDisabled = computed(() => {
    // Check if any product has all required fields filled
    const hasValidProducts = form.products.some(product =>
        product.unit_id &&
        product.category_id &&
        product.subcategory_id &&
        product.quantity &&
        product.selling_price
    );

    // Common required fields for all statuses
    const commonFieldsValid =
        form.status_id &&
        form.sale_date &&
        form.customer_id &&
        form.transaction_id &&
        form.transaction_number &&
        hasValidProducts;

    // Additional validation when status_id is 2
    if (form.status_id === '2') {
        return !commonFieldsValid || !form.due_date_id || form.processing;
    }

    return !commonFieldsValid || form.processing;
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
                    Edit your transaction details and click submit when done
                </DialogDescription>
            </DialogHeader>
            <div class="py-4">
                <div class="p-4 border rounded-lg">
                    <form @submit.prevent="submit" class="grid gap-4">

                        <div class="grid items-center grid-cols-2 gap-3">
                            <!-- Customer -->
                            <div class="grid items-center grid-cols-5 gap-3 text-right">
                                <Label class="after:content-['*'] after:ml-0.5 after:text-red-500 col-span-2">
                                    Customer Name
                                </Label>
                                <DropdownSearch :items="filteredCustomer" label-key="name" value-key="id"
                                    :class="['col-span-3 justify-between font-normal', !form.customer_id && 'text-muted-foreground', { 'border-red-600 focus:ring-red-500': form.errors.customer_id }]"
                                    :has-error="form.errors.customer_id" placeholder="Select a customer"
                                    v-model="form.customer_id">
                                    <Create />
                                </DropdownSearch>
                                <InputError class="col-span-2 col-start-3 text-start"
                                    :message="form.errors.customer_id" />
                            </div>
                            <!-- Sale Date -->
                            <div class="grid items-center grid-cols-5 gap-3 text-right">
                                <Label class="after:content-['*'] after:ml-0.5 after:text-red-500 col-span-2">
                                    Sale Date
                                </Label>
                                <Popover v-model:open="isPopoverOpen">
                                    <PopoverTrigger as-child>
                                        <Button variant="outline"
                                            :class="cn('justify-start text-left font-normal col-span-3', !form.sale_date && 'text-muted-foreground', { 'border-red-600 focus-visible:ring-red-500': form.errors.sale_date })">
                                            <CalendarIcon class="mr-2 size-4" />
                                            {{ form.sale_date
                                                ? df.format(new Date(form.sale_date))
                                                : "mm/dd/yyyy" }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0">
                                        <Calendar v-model="selectedDate" initial-focus
                                            @update:model-value="handleDateSelect" />
                                    </PopoverContent>
                                </Popover>
                                <InputError class="col-span-2 col-start-3 text-start"
                                    :message="form.errors.sale_date" />
                            </div>
                        </div>

                        <div class="grid items-center grid-cols-2 gap-3">
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
                                <InputError class="col-span-2 col-start-3 text-start"
                                    :message="form.errors.transaction_id" />
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
                                <InputError class="col-span-2 col-start-3 text-start"
                                    :message="form.errors.transaction_number" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="font-bold">Sale Item</div>
                            <Button @click="addProduct()" type="button" size="sm" class="gap-1 h-7">
                                <Plus class="size-3.5" />
                                <span class="sr-only sm:not-sr-only sm:whitespace-nowrap">
                                    Add Item
                                </span>
                            </Button>
                        </div>
                        <div class="max-h-[300px] overflow-auto p-4 border rounded-lg">
                            <Table>
                                <TableHeader class="bg-slate-100">
                                    <TableRow>
                                        <TableHead><span class="sr-only">Index</span></TableHead>
                                        <TableHead class="after:content-['*'] after:ml-0.5 after:text-red-500">
                                            Category
                                        </TableHead>
                                        <TableHead class="after:content-['*'] after:ml-0.5 after:text-red-500">
                                            Sub Category
                                        </TableHead>
                                        <TableHead class="after:content-['*'] after:ml-0.5 after:text-red-500">
                                            Unit
                                        </TableHead>
                                        <TableHead class="after:content-['*'] after:ml-0.5 after:text-red-500">
                                            Selling Price
                                        </TableHead>
                                        <TableHead class="after:content-['*'] after:ml-0.5 after:text-red-500">
                                            Quantity
                                        </TableHead>
                                        <TableHead class="after:content-['*'] after:ml-0.5 after:text-red-500">
                                            Amount
                                        </TableHead>
                                        <TableHead>
                                            <span class="sr-only">Action</span>
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-if="form.products.length > 0" v-for="(product, index) in form.products"
                                        :key="index" class="hover:bg-muted/0">
                                        <TableCell>{{ index + 1 }}</TableCell>
                                        <TableCell> <!-- Category -->
                                            <Select v-model="product.category_id">
                                                <SelectTrigger
                                                    :class="['w-[180px]', { 'border-red-600 focus:ring-red-500': form.errors[`products.${index}.category_id`] }]">
                                                    <SelectValue placeholder="Select category" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectItem v-for="category in filteredCategory"
                                                            :key="category.id" :value="String(category.id)">
                                                            {{ category.name }}
                                                        </SelectItem>
                                                        <SelectItem class="w-[150px]" disabled
                                                            v-if="filteredCategory.length === 0" :value="String(0)">
                                                            No category available
                                                        </SelectItem>
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                            <InputError class="mt-3"
                                                :message="form.errors[`products.${index}.category_id`]" />
                                        </TableCell>
                                        <TableCell> <!-- SubCategory -->
                                            <Select v-model="product.subcategory_id" :disabled="!product.category_id">
                                                <SelectTrigger
                                                    :class="['min-w-[180px]', { 'border-red-600 focus:ring-red-500': form.errors[`products.${index}.subcategory_id`] }]">
                                                    <SelectValue placeholder="Select sub category" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectItem
                                                            v-for="subcategory in filteredSubcategory(product.category_id)"
                                                            :key="subcategory.id" :value="String(subcategory.id)">
                                                            {{ subcategory.name }}
                                                        </SelectItem>
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                            <InputError class="mt-3"
                                                :message="form.errors[`products.${index}.subcategory_id`]" />
                                        </TableCell>
                                        <TableCell> <!-- Unit -->
                                            <Select v-model="product.unit_id" :disabled="!product.subcategory_id">
                                                <SelectTrigger
                                                    :class="['w-[180px]', { 'border-red-600 focus:ring-red-500': form.errors[`products.${index}.unit_id`] }]">
                                                    <SelectValue placeholder="Select unit" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectItem v-for="unit in filteredUnit(product)" :key="unit.id"
                                                            :value="String(unit.id)">
                                                            {{ unit.abbreviation }}
                                                        </SelectItem>
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                            <InputError class="mt-3"
                                                :message="form.errors[`products.${index}.unit_id`]" />
                                        </TableCell>
                                        <TableCell> <!-- Selling Price -->
                                            <div class="relative items-center">
                                                <Input v-model="product.selling_price" type="number" min="0" step=".01"
                                                    oninput="validity.valid||(value='');"
                                                    :class="['pl-7', { 'border-red-600 focus-visible:ring-red-500': form.errors[`products.${index}.selling_price`] }]" />
                                                <span
                                                    class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                                    <PhilippinePeso class="size-4 text-muted-foreground" />
                                                </span>
                                            </div>
                                            <InputError class="mt-3"
                                                :message="form.errors[`products.${index}.selling_price`]" />
                                        </TableCell>
                                        <TableCell> <!-- Quantity -->
                                            <div class="relative items-center">
                                                <Input v-model.number="product.quantity" type="number" min="0"
                                                    step="0.01" :class="[
                                                        'pl-7',
                                                        { 'border-red-600 focus-visible:ring-red-500': form.errors[`products.${index}.quantity`] }
                                                    ]" />
                                                <span
                                                    class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                                    <Boxes class="size-4 text-muted-foreground" />
                                                </span>
                                                <small :class="['absolute text-green-600 font-medium top-2.5 right-3', {
                                                    'text-red-500': (totalQuantity[index] <= 0)
                                                }]">
                                                    {{ (totalQuantity[index]).toLocaleString('en-US', {
                                                        minimumFractionDigits: 2, maximumFractionDigits: 2
                                                    }) }} left
                                                </small>
                                            </div>
                                            <InputError class="mt-3"
                                                :message="form.errors[`products.${index}.quantity`]" />
                                        </TableCell>
                                        <TableCell> <!-- Amount -->
                                            <div class="relative items-center">
                                                <FormattedNumberInput v-model="product.amount" disabled
                                                    class="pl-7 bg-slate-200" />
                                                <span
                                                    class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                                    <PhilippinePeso class="size-4 text-muted-foreground" />
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell> <!-- Action -->
                                            <Button variant="ghost" @click="removeProduct(product, index)" type="button"
                                                size="xs" class="text-red-500 hover:text-red-600">
                                                <Trash2 class="size-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else class="hover:bg-muted/0">
                                        <TableCell :colspan="7" class="h-24 text-center">
                                            No products found.
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <div class="grid items-center grid-cols-2 gap-3">
                            <!-- Additional details -->
                            <div class="grid grid-cols-5 gap-3 text-right">
                                <Label for="description" class="col-span-2 pt-3">Additional Details</Label>
                                <Textarea placeholder="Type your additional details here. (optional)"
                                    v-model="form.description" id="description" class="col-span-3" />
                            </div>
                            <!-- total amount -->
                            <div class="grid items-center gap-4 text-right">
                                <div class="grid items-center grid-cols-5 gap-3">
                                    <Label for="total_amount"
                                        class="after:content-['*'] after:ml-0.5 after:text-red-500 col-span-2">
                                        Total Amount
                                    </Label>
                                    <div class="relative items-center col-span-3">
                                        <FormattedNumberInput v-model="form.total_amount" disabled
                                            class="pl-7 bg-slate-200" />
                                        <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                                            <PhilippinePeso class="size-4 text-muted-foreground" />
                                        </span>
                                    </div>
                                </div>
                                <div class="grid items-center grid-cols-10">
                                    <!-- Due Date -->
                                    <div v-if="form.status_id == 2"
                                        class="grid items-center grid-cols-7 col-span-7 gap-3 text-right">
                                        <Label class="after:content-['*'] after:ml-0.5 after:text-red-500 col-span-4">
                                            Due Date
                                        </Label>
                                        <Select v-model="form.due_date_id">
                                            <SelectTrigger
                                                :class="['col-span-3', { 'border-red-600 focus:ring-red-500': form.errors.due_date_id }]">
                                                <SelectValue placeholder="Select Due Date" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectItem v-for="due in dues" :key="due.id"
                                                        :value="String(due.id)">
                                                        {{ due.days }}
                                                    </SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                        <InputError class="col-span-3 col-start-5 text-start"
                                            :message="form.errors.due_date_id" />
                                    </div>
                                    <!-- Status -->
                                    <div :class="form.status_id == 2 ? 'col-span-3' : 'col-span-10'">
                                        <div class="flex items-center justify-end gap-[50px] px-2">
                                            <div class="flex items-center space-x-2">
                                                <input v-model="form.status_id" type="radio" id="cash" value="1" />
                                                <label for="cash">Cash</label>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <input v-model="form.status_id" type="radio" id="credit" value="2" />
                                                <label for="credit">Credit</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <DialogFooter class="flex items-center mt-4">
                            <div>
                                <InputError :message="form.products.findIndex((_, i) => form.errors[`products.${i}.duplicate`]) !== -1
                                    ? form.errors[`products.${form.products.findIndex((_, i) => form.errors[`products.${i}.duplicate`])}.duplicate`]
                                    : null" class="text-sm" />
                            </div>
                            <Button @click="closeModal()" type="button" class="bg-[#C00F0C] hover:bg-red-500 h-7">
                                Cancel
                            </Button>
                            <Button class="disabled:cursor-not-allowed disabled:bg-[#757575] h-7" type="submit"
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
