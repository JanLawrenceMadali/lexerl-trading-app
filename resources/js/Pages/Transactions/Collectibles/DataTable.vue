<script setup>
import { h, ref } from 'vue'
import { valueUpdater } from '@/lib/utils'
import { Input } from '@/Components/ui/input'
import { router, usePage } from "@inertiajs/vue3";
import { Button } from '@/Components/ui/button'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/Components/ui/table'
import { ArrowUpDown, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, SquareArrowOutUpRight, Trash2 } from 'lucide-vue-next'
import { FlexRender, getCoreRowModel, getExpandedRowModel, getFilteredRowModel, getPaginationRowModel, getSortedRowModel, useVueTable, } from '@tanstack/vue-table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import Create from './Dialog/Create.vue';
import Edit from './Dialog/Edit.vue';
import Swal from 'sweetalert2';
import View from './Dialog/View.vue';

const props = defineProps({
    units: Object,
    purchases: Object,
    suppliers: Object,
    categories: Object,
    transactions: Object,
    subcategories: Object,
})

const page = usePage()

const data = ref(props.purchases)

const reloadDataTable = () => {
    data.value = page.props.purchases
}

const handlePurchaseCreated = () => {
    reloadDataTable()
    Swal.fire({
        title: "Created!",
        text: "Transaction successfully created!",
        iconHtml: '<img src="/assets/icons/Success.png">',
        confirmButtonColor: "#1B1212",
    });
}

const handlePurchaseUpdated = () => {
    reloadDataTable()
    Swal.fire({
        title: "Updated!",
        text: "Transaction successfully updated!",
        iconHtml: '<img src="/assets/icons/Success.png">',
        confirmButtonColor: "#1B1212",
    });
}

const handlePurchaseDeleted = (id) => {
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
            router.delete(route('purchase-in.destroy', id), {
                onSuccess: () => {
                    data.value = data.value.filter(item => item.id !== id);
                    router.get(route('purchase-in'))
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
            const purchase = row.original;
            return h('div', { class: 'px-2' }, `# ${purchase.transaction_number}`);
        },
    },
    {
        accessorKey: 'purchase_date',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Purchase Date', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const purchase = row.original
            const date = new Date(purchase.purchase_date)
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
        accessorKey: 'supplier.name',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Supplier', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const purchase = row.original;
            return h('div', { class: 'px-2' }, purchase.supplier.name)
        },
    },
    {
        accessorKey: 'quantity',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Quantity', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const purchase = row.original
            const quantity = Number.parseFloat(row.getValue('quantity'))
            return h('div', { class: 'px-2' }, `${quantity} ${purchase.unit_measure.abbreviation}`)
        },
    },
    {
        accessorKey: 'category.name',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Category', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const purchase = row.original;
            return h('div', { class: 'px-2' }, [
                h('div', { class: 'font-medium' }, purchase.category.name),
                h('div', { class: 'text-slate-500' },
                    purchase.subcategory.category_id === purchase.category?.id
                        ? purchase.subcategory.name
                        : 'No subcategory'
                )
            ]);
        },
    },
    {
        accessorKey: 'amount',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Amount', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const purchase = row.original;
            return h('div', { class: 'px-2' }, '₱' + purchase.amount);
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
    //         const purchase = row.original
    //         const date = new Date(purchase.created_at)
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
    //         const purchase = row.original
    //         const date = new Date(purchase.updated_at)
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
            const purchases = row.original;
            const units = props.units
            const suppliers = props.suppliers
            const categories = props.categories
            const transactions = props.transactions
            const subcategories = props.subcategories

            return h('div', { class: 'flex items-center gap-1' }, [
                h(View, {

                }),
                h(Edit, {
                    units,
                    suppliers,
                    categories,
                    transactions,
                    subcategories,
                    purchases: purchases,
                    onPurchaseUpdated: handlePurchaseUpdated
                }),
                h(Button, {
                    size: 'xs',
                    variant: 'ghost',
                    class: 'text-red-600 hover:text-red-800',
                    title: 'Delete',
                    onClick: () => handlePurchaseDeleted(purchase.id)
                }, () => h(Trash2, { class: 'size-5' })),
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
            'supplier.name',
            'category.name',
            'subcategory.name',
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
        <Create @purchase-created="handlePurchaseCreated" :purchases="purchases" :categories="categories"
            :subcategories="subcategories" :suppliers="suppliers" :transactions="transactions" :units="units" />
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