<script setup>
import { computed, h, ref } from 'vue'
import { cn, valueUpdater } from '@/lib/utils'
import { Input } from '@/Components/ui/input'
import { router } from "@inertiajs/vue3";
import { Button } from '@/Components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/Components/ui/table'
import { ArrowUpDown, CalendarRange, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, CircleX, File, RefreshCcw, Search } from 'lucide-vue-next'
import { FlexRender, getCoreRowModel, getFilteredRowModel, getPaginationRowModel, getSortedRowModel, useVueTable, } from '@tanstack/vue-table'
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/Components/ui/select';
import Create from './Dialog/Create.vue';
import Edit from './Dialog/Edit.vue';
import Swal from 'sweetalert2';
import View from './Dialog/View.vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { RangeCalendar } from '@/Components/ui/range-calendar';
import { getLocalTimeZone } from '@internationalized/date'

const props = defineProps({
    dues: Object,
    sales: Object,
    units: Object,
    products: Object,
    customers: Object,
    categories: Object,
    inventories: Object,
    transactions: Object,
    subcategories: Object,
})

const data = ref([...props.sales])
const customerData = ref([...props.customers])
const sorting = ref([])
const filter = ref('')
const exportSale = ref('')

// Updated display range function
const getDisplayRange = () => {
    const totalItems = data.value.length;
    const pageIndex = table.getState().pagination.pageIndex;
    const pageSize = table.getState().pagination.pageSize;

    const start = pageIndex * pageSize + 1;
    const end = Math.min((pageIndex + 1) * pageSize, totalItems);

    return `${start} - ${end} of ${totalItems}`;
}

const df = new Intl.DateTimeFormat('en-PH', {
    dateStyle: 'medium',
});

const range = ref({
    start: null,
    end: null,
})

const filteredData = computed(() => {
    return data.value.filter(item => {
        const sale_date = new Date(item.sale_date);

        const isDateInRange =
            (!range.value.start || sale_date >= range.value.start.toDate(getLocalTimeZone())) &&
            (!range.value.end || sale_date <= range.value.end.toDate(getLocalTimeZone()));

        return isDateInRange;
    });
});

const resetDateRange = () => {
    range.value = {
        start: null,
        end: null,
    };
};

const handleCustomerCreated = (customer) => {
    customerData.value = Array.isArray(customer) ? [...customer] : [...customerData.value]
};

const handleSale = (sale) => {
    data.value = Array.isArray(sale) ? [...sale] : [...data.value]
};

const handleSalesCanceled = (id) => {
    Swal.fire({
        title: '<h2 class="custom-title">Are you sure you want to cancel this transaction?</h2>',
        html: '<p class="custom-text">Please note that this is irreversible</p>',
        iconHtml: '<img src="/assets/icons/Warning.png">',
        showCancelButton: true,
        confirmButtonColor: "#C00F0C",
        cancelButtonColor: "#1B1212",
        confirmButtonText: "Yes",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('sales.destroy', id), {
                onSuccess: (response) => {
                    data.value = [...response.props.sales]
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
            })
        }
    });
}

const formattedCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};

const TIME_UNITS = [
    { unit: 'year', seconds: 31536000 },
    { unit: 'month', seconds: 2592000 },
    { unit: 'day', seconds: 86400 },
    { unit: 'hour', seconds: 3600 },
    { unit: 'minute', seconds: 60 },
    { unit: 'second', seconds: 1 }
];

const timeAgo = (date) => {
    const secondsElapsed = Math.floor((new Date() - date) / 1000);

    for (const { unit, seconds } of TIME_UNITS) {
        const interval = Math.floor(secondsElapsed / seconds);
        if (interval >= 1) {
            return `${interval} ${unit}${interval > 1 ? 's' : ''} ago`;
        }
    }

    return 'just now';
};

const formattedDate = (value) => new Intl.DateTimeFormat('en-PH', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
}).format(value)

const columns = [
    {
        accessorKey: 'transaction_number',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Transaction No.', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const { transaction_type } = row.original;
            const { transaction_number } = row.original

            return h('div', { class: 'px-2' }, [
                h('div', transaction_type),
                h('div', { class: 'text-sm text-gray-500' }, `# ${transaction_number}`)
            ]);
        },
    },
    {
        accessorKey: 'sale_date',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Sale Date', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const { sale_date } = row.original

            return h('div', { class: 'px-2' }, [
                h('div', sale_date)
            ]);
        },
    },
    {
        accessorKey: 'customer_name',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Customer Name', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const { customer_name } = row.original;
            const { customer_email } = row.original;

            return h('div', { class: 'px-2' }, [
                h('div', { class: 'font-medium' }, customer_name),
                h('div', { class: 'text-slate-500' }, customer_email)
            ]);
        },
    },
    {
        accessorKey: 'total_amount',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Total Amount', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const { total_amount } = row.original;

            return h('div', { class: 'px-2' }, formattedCurrency(total_amount));
        },
    },
    {
        accessorKey: 'status',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Payment Method', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const { status } = row.original;

            return h('div', { class: 'px-2' }, status);
        },
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Created At', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const { created_at } = row.original

            const date = new Date(created_at)

            return h('div', { class: 'px-2' }, [
                h('div', formattedDate(date)),
                h('div', { class: 'text-xs text-gray-500' }, timeAgo(date))
            ]);
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const dues = props.dues
            const units = props.units
            const sales = row.original;
            const customers = props.customers
            const products = props.products
            const categories = props.categories
            const inventories = props.inventories
            const transactions = props.transactions
            const subcategories = props.subcategories

            return h('div', { class: 'flex items-center gap-1 justify-end' }, [
                h(View, {
                    sales
                }),
                h(Edit, {
                    dues,
                    sales,
                    units,
                    customers,
                    products,
                    categories,
                    inventories,
                    transactions,
                    subcategories,
                    onUpdateSale: handleSale
                }),
                h(Button, {
                    size: 'xs',
                    variant: 'ghost',
                    class: 'text-red-600 hover:text-red-800',
                    title: 'Cancel',
                    onClick: () => handleSalesCanceled(sales.id)
                }, () => h(CircleX, { class: 'size-5' }))
            ]);
        },
    },
]

const table = useVueTable({
    data: filteredData,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
    state: {
        get sorting() { return sorting.value },
        get globalFilter() { return filter.value }
    },
    globalFilterFn: (row, columnId, filterValue) => {
        const searchableFields = [
            'status',
            'sale_date',
            'total_amount',
            'customer_name',
            'customer_email',
            'transaction_number',
            'transaction_type',
            'created_at',
        ];

        return searchableFields.some(field => {
            const value = getNestedValue(row.original, field);
            return String(value).toLowerCase().includes(filterValue.toLowerCase());
        });
    },
})

function getNestedValue(obj, path) {
    const keys = path.split('.');
    return keys.reduce((acc, key) => {
        return acc && acc[key] !== undefined ? acc[key] : null;
    }, obj);
}

const isExporting = ref(false)

const exportData = async () => {
    try {
        isExporting.value = true;

        const params = {
            start_date: range.value.start
                ? df.format(range.value.start.toDate(getLocalTimeZone()))
                : null,
            end_date: range.value.end
                ? df.format(range.value.end.toDate(getLocalTimeZone()))
                : null,
        };
        const queryString = new URLSearchParams(params).toString();

        const exportUrl = `${route(
            exportSale.value === 'summary' ? 'sales.summary_export' : 'sales.detailed_export'
        )}?${queryString}`;

        window.location.href = exportUrl;
    } catch (error) {
        console.error('Export failed:', error);
    } finally {
        setTimeout(() => {
            isExporting.value = false;
            exportSale.value = '';
            resetDateRange
        }, 1000);
    }
};
</script>

<template>
    <div class="flex items-center justify-between gap-2 py-4">
        <div class="relative items-center col-span-2">
            <Input v-model="filter" type="search" placeholder="Search..." class="pl-7 h-8 w-[150px] lg:w-[250px]" />
            <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                <Search class="size-4 text-muted-foreground" />
            </span>
        </div>

        <div class="flex items-center gap-2">
            <Button title="Reset date" size="sm" variant="outline" class="gap-1 h-7" :disabled="!range.start"
                @click="resetDateRange">
                <RefreshCcw class="h-3.5 w-3.5" />
            </Button>
            <Popover>
                <PopoverTrigger as-child>
                    <Button variant="outline"
                        :class="cn('h-7 justify-start gap-2 text-left font-normal', !range && 'text-muted-foreground')">
                        <CalendarRange class="w-4 h-4" />
                        <template v-if="range.start">
                            <template v-if="range.end">
                                {{ df.format(range.start.toDate(getLocalTimeZone())) }} - {{
                                    df.format(range.end.toDate(getLocalTimeZone())) }}
                            </template>

                            <template v-else>
                                {{ df.format(range.start.toDate(getLocalTimeZone())) }}
                            </template>
                        </template>
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-auto p-0">
                    <RangeCalendar v-model="range" initial-focus :number-of-months="1"
                        @update:start-value="(startDate) => range.start = startDate" />
                </PopoverContent>
            </Popover>
            <Select v-model="exportSale">
                <SelectTrigger class="w-[180px] h-7">
                    <SelectValue placeholder="Select report type" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem value="summary">
                            Summary
                        </SelectItem>
                        <SelectItem value="detailed">
                            Detailed
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
            <Button size="sm" variant="outline" class="gap-1 h-7"
                :disabled="isExporting || data.length === 0 || !exportSale" @click="exportData">
                <File class="h-3.5 w-3.5" />
                <span class="sr-only sm:not-sr-only sm:whitespace-nowrap">
                    {{ isExporting ? 'Exporting...' : 'Export' }}
                </span>
            </Button>
            <Create :sales="sales" :categories="categories" :subcategories="subcategories" :customers="customers"
                :transactions="transactions" :units="units" :dues="dues" :inventories="inventories" :products="products"
                @create-customer="handleCustomerCreated" @create-sale="handleSale" />
        </div>
    </div>
    <div class="border rounded-md">
        <Table>
            <TableHeader>
                <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                    <TableHead v-for="header in headerGroup.headers" :key="header.id">
                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
                            :props="header.getContext()" />
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-if="table.getRowModel().rows?.length">
                    <template v-for="row in table.getRowModel().rows" :key="row.id">
                        <TableRow :data-state="row.getIsSelected() && 'selected'">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="row.getIsExpanded()">
                            <TableCell :colspan="row.getAllCells().length">
                                {{ JSON.stringify(row.original) }}
                            </TableCell>
                        </TableRow>
                    </template>
                </template>
                <TableRow v-else>
                    <TableCell :colspan="columns.length" class="h-24 text-center">
                        No results.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
    <div class="flex items-center justify-end py-4 space-x-2">
        <div class="flex items-center space-x-2">
            <p class="text-sm font-medium">
                Items per page:
            </p>
            <Select :model-value="`${table.getState().pagination.pageSize}`" @update:model-value="table.setPageSize">
                <SelectTrigger class="h-8 w-[70px]">
                    <SelectValue :placeholder="`${table.getState().pagination.pageSize}`" />
                </SelectTrigger>
                <SelectContent side="top">
                    <SelectItem v-for="pageSize in [10, 20, 30, 40, 50]" :key="pageSize" :value="`${pageSize}`">
                        {{ pageSize }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div class="flex items-center space-x-6">
            <span class="text-sm">
                {{ getDisplayRange() }}
            </span>

            <div class="flex items-center space-x-2">
                <Button variant="outline" size="icon" :disabled="!table.getCanPreviousPage()"
                    @click="table.firstPage()">
                    <ChevronsLeft class="size-4" />
                </Button>
                <Button variant="outline" size="icon" :disabled="!table.getCanPreviousPage()"
                    @click="table.previousPage()">
                    <ChevronLeft class="size-4" />
                </Button>
                <Button variant="outline" size="icon" :disabled="!table.getCanNextPage()" @click="table.nextPage()">
                    <ChevronRight class="size-4" />
                </Button>
                <Button variant="outline" size="icon" :disabled="!table.getCanNextPage()" @click="table.lastPage()">
                    <ChevronsRight class="size-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
