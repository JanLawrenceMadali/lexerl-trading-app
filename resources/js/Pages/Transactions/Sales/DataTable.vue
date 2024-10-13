<script setup>
import { h, ref } from 'vue'
import { valueUpdater } from '@/lib/utils'
import { Input } from '@/Components/ui/input'
import { router, usePage } from "@inertiajs/vue3";
import { Button } from '@/Components/ui/button'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/Components/ui/table'
import { ArrowUpDown, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, Trash2 } from 'lucide-vue-next'
import { FlexRender, getCoreRowModel, getExpandedRowModel, getFilteredRowModel, getPaginationRowModel, getSortedRowModel, useVueTable, } from '@tanstack/vue-table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import Create from './Dialog/Create.vue';
import Edit from './Dialog/Edit.vue';
import Swal from 'sweetalert2';
import View from './Dialog/View.vue';

const props = defineProps({
    units: Object,
    sales: Object,
    dues: Object,
    customers: Object,
    categories: Object,
    transactions: Object,
    subcategories: Object,
})

const page = usePage()

const data = ref(props.sales)

const reloadDataTable = () => {
    data.value = page.props.sales
}

const handleSalesCreated = () => {
    reloadDataTable()
    Swal.fire({
        title: "Created!",
        text: "Transaction successfully created!",
        iconHtml: '<img src="/assets/icons/Success.png">',
        confirmButtonColor: "#1B1212",
    });
}

const handleSalesUpdated = () => {
    reloadDataTable()
    Swal.fire({
        title: "Updated!",
        text: "Transaction successfully updated!",
        iconHtml: '<img src="/assets/icons/Success.png">',
        confirmButtonColor: "#1B1212",
    });
}

const handleSalesDeleted = (id) => {
    Swal.fire({
        title: '<h2 class="custom-title">Are you sure you want to delete this transaction?</h2>',
        html: '<p class="custom-text">Please note that this is irreversible</p>',
        iconHtml: '<img src="/assets/icons/Warning.png">',
        showCancelButton: true,
        confirmButtonColor: "#C00F0C",
        cancelButtonColor: "#1B1212",
        confirmButtonText: "Yes",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('sales.destroy', id), {
                onSuccess: () => {
                    data.value = data.value.filter(item => item.id !== id);
                    router.get(route('sales'))
                    Swal.fire({
                        title: "Deleted!",
                        text: "Transaction successfully removed!",
                        iconHtml: '<img src="/assets/icons/Success.png">',
                        confirmButtonColor: "#1B1212",
                    });
                }
            })
        }
    });
}

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


const columns = [
    {
        accessorKey: 'transaction_number',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Transaction #', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const sale = row.original;
            return h('div', { class: 'px-2' }, `# ${sale.transaction_number}`);
        },
    },
    {
        accessorKey: 'sales_date',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Sales Date', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const sale = row.original
            const date = new Date(sale.sales_date)
            const formattedDate = new Intl.DateTimeFormat('en-PH', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            }).format(date)

            return h('div', { class: 'px-2' }, [
                h('div', {}, formattedDate),
                h('div', { class: 'text-xs text-gray-500' })
            ]);
        },
    },
    {
        accessorKey: 'customer.name',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Customer', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const sale = row.original;
            return h('div', { class: 'px-2' }, sale.customer.name)
        },
    },
    {
        accessorKey: 'quantity',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Quantity', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const sale = row.original
            const quantity = Number.parseFloat(row.getValue('quantity'))
            return h('div', { class: 'px-2' }, `${quantity} ${sale.unit_measure.abbreviation}`)
        },
    },
    {
        accessorKey: 'category.name',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Category', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const sale = row.original;
            return h('div', { class: 'px-2' }, [
                h('div', { class: 'font-medium' }, sale.category.name),
                h('div', { class: 'text-slate-500' },
                    sale.subcategory.category_id === sale.category?.id
                        ? sale.subcategory.name
                        : 'No subcategory'
                )
            ]);
        },
    },
    {
        accessorKey: 'is_paid',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Payment', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const sale = row.original;
            const paymentStatus = sale.is_paid === 0 ? 'Credit' : 'Cash';
            return h('div', { class: 'px-2' }, paymentStatus);
        },
    },
    {
        accessorKey: 'due_date',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Due Date', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const sale = row.original;
            return h('div', { class: 'px-2' }, sale.due_date.days);
        },
    },
    {
        accessorKey: 'amount',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Amount', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const sale = row.original;
            return h('div', { class: 'px-2' }, '₱' + sale.amount);
        },
    },
    {
        accessorKey: 'notes',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Notes', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const notes = row.getValue('notes');
            return h(TooltipProvider, {}, () =>
                h(Tooltip, {}, {
                    default: () => [
                        h(TooltipTrigger, { asChild: true }, () =>
                            h('div', { class: 'px-2 truncate w-40' }, notes)
                        ),
                        h(TooltipContent, {}, () =>
                            h('p', {}, notes)
                        )
                    ]
                })
            );
        },
    },
    // {
    //     accessorKey: 'created_at',
    //     header: ({ column }) => {
    //         return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Created At', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
    //     },
    //     cell: ({ row }) => {
    //         const sale = row.original
    //         const date = new Date(sale.created_at)
    //         const formattedDate = new Intl.DateTimeFormat('en-PH', {
    //             year: 'numeric',
    //             month: 'short',
    //             day: 'numeric',
    //         }).format(date)

    //         const timeAgoString = timeAgo(date);

    //         return h('div', { class: 'px-2' }, [
    //             h('div', {}, formattedDate),
    //             h('div', { class: 'text-xs text-gray-500' }, timeAgoString)
    //         ]);
    //     },
    // },
    // {
    //     accessorKey: 'updated_at',
    //     header: ({ column }) => {
    //         return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Updated At', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
    //     },
    //     cell: ({ row }) => {
    //         const sale = row.original
    //         const date = new Date(sale.updated_at)
    //         const formattedDate = new Intl.DateTimeFormat('en-PH', {
    //             year: 'numeric',
    //             month: 'short',
    //             day: 'numeric',
    //         }).format(date)

    //         const timeAgoString = timeAgo(date);

    //         return h('div', { class: 'px-2' }, [
    //             h('div', {}, formattedDate),
    //             h('div', { class: 'text-xs text-gray-500' }, timeAgoString)
    //         ]);
    //     },
    // },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const dues = props.dues
            const units = props.units
            const sales = row.original;
            const customers = props.customers
            const categories = props.categories
            const transactions = props.transactions
            const subcategories = props.subcategories

            return h('div', { class: 'flex items-center gap-1' }, [
                h(View, {

                }),
                h(Edit, {
                    units,
                    dues,
                    customers,
                    categories,
                    transactions,
                    sales: sales,
                    subcategories,
                    onSalesUpdated: handleSalesUpdated
                }),
                h(Button, {
                    size: 'xs',
                    variant: 'ghost',
                    class: 'text-red-600 hover:text-red-800',
                    title: 'Delete',
                    onClick: () => handleSalesDeleted(sales.id)
                }, () => h(Trash2, { class: 'size-5' }))
            ]);
        },
    },
]

const sorting = ref([])
const filter = ref('')
const columnFilters = ref([])
const columnVisibility = ref({})
const rowSelection = ref({})
const expanded = ref({})

const table = useVueTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getExpandedRowModel: getExpandedRowModel(),
    onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: updaterOrValue => valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: updaterOrValue => valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: updaterOrValue => valueUpdater(updaterOrValue, rowSelection),
    onExpandedChange: updaterOrValue => valueUpdater(updaterOrValue, expanded),
    initialState: {
        pagination: {
            pageSize: 5,
        }
    },
    state: {
        get sorting() { return sorting.value },
        get columnFilters() { return columnFilters.value },
        get columnVisibility() { return columnVisibility.value },
        get rowSelection() { return rowSelection.value },
        get expanded() { return expanded.value },
        get globalFilter() { return filter.value }
    },
    globalFilterFn: (row, columnId, filterValue) => {
        const searchableFields = [
            'notes',
            'amount',
            'quantity',
            'sales_date',
            'due_date.days',
            'customer.name',
            'category.name',
            'subcategory.name',
            'transaction_number',
            'unit_measure.abbreviation',
            'transaction.transaction_type',
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

</script>

<template>
    <div class="flex items-center justify-between gap-2 py-4">
        <Input placeholder="Search..." v-model="filter" class="h-8 w-[150px] lg:w-[250px]" />
        <Create @sales-created="handleSalesCreated" :sales="sales" :categories="categories"
            :subcategories="subcategories" :customers="customers" :transactions="transactions" :units="units" :dues="dues" />
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
                Rows per page
            </p>
            <Select :model-value="`${table.getState().pagination.pageSize}`" @update:model-value="table.setPageSize">
                <SelectTrigger class="h-8 w-[70px]">
                    <SelectValue :placeholder="`${table.getState().pagination.pageSize}`" />
                </SelectTrigger>
                <SelectContent side="top">
                    <SelectItem v-for="pageSize in [5, 10, 20, 30, 40, 50]" :key="pageSize" :value="`${pageSize}`">
                        {{ pageSize }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>
        <div class="flex w-[100px] items-center justify-center text-sm font-medium">
            Page {{ table.getState().pagination.pageIndex + 1 }} of
            {{ table.getPageCount() }}
        </div>
        <div class="space-x-2">
            <Button variant="outline" size="sm" :disabled="!table.getCanPreviousPage()" @click="table.firstPage()">
                <ChevronsLeft class="size-5" />
            </Button>
            <Button variant="outline" size="sm" :disabled="!table.getCanPreviousPage()" @click="table.previousPage()">
                <ChevronLeft class="size-5" />
            </Button>
            <Button variant="outline" size="sm" :disabled="!table.getCanNextPage()" @click="table.nextPage()">
                <ChevronRight class="size-5" />
            </Button>
            <Button variant="outline" size="sm" :disabled="!table.getCanNextPage()" @click="table.lastPage()">
                <ChevronsRight class="size-5" />
            </Button>
        </div>
    </div>
</template>