<script setup>
import { h, ref, watch } from 'vue'
import { valueUpdater } from '@/lib/utils'
import { Input } from '@/Components/ui/input'
import { Link, router } from "@inertiajs/vue3";
import { Button } from '@/Components/ui/button'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/Components/ui/table'
import { ArrowUpDown, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, PlusCircle, Trash2 } from 'lucide-vue-next'
import { FlexRender, getCoreRowModel, getExpandedRowModel, getFilteredRowModel, getPaginationRowModel, getSortedRowModel, useVueTable, } from '@tanstack/vue-table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import Delete from './Dialog/Delete.vue';
import Create from './Dialog/Create.vue';
import Edit from './Dialog/Edit.vue';

const props = defineProps({
    units: Object,
    purchases: Object,
    suppliers: Object,
    categories: Object,
    transactions: Object,
    subcategories: Object,
})

const data = ref(props.purchases)

const handlePurchaseCreated = () => {
    router.reload({ only: ['purchases'] })
}

const handlePurchaseDeleted = () => {
    data.value = data.value.filter(purchase => purchase.id !== deletedPurchaseId)
}

const handlePurchaseEdited = () => {
    router.reload({ only: ['purchases'] })
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
        accessorKey: 'transaction.transaction_type',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Transaction Type', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const purchase = row.original;
            return h('div', { class: 'px-2' }, [
                h('div', { class: 'font-medium' }, purchase.transaction.transaction_type),
                h('div', { class: 'text-slate-500' }, `Transaction # ${purchase.transaction_number}`)
            ]);
        },
    },
    {
        accessorKey: 'unit_measure.name',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Unit', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const purchase = row.original;
            return h('div', { class: 'px-2' }, purchase.unit_measure.abbreviation)
        },
    },
    {
        accessorKey: 'quantity',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Quantity', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const quantity = Number.parseFloat(row.getValue('quantity'))
            return h('div', { class: 'font-medium px-2' }, quantity)
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
                h('div', { class: 'font-medium' }, purchase.category?.name || 'N/A'),
                h('div', { class: 'text-slate-500' },
                    purchase.subcategory.category_id === purchase.category?.id
                        ? purchase.subcategory.name
                        : 'No subcategory'
                )
            ]);
        },
    },
    {
        accessorKey: 'cost',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Cost', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const cost = Number.parseFloat(row.getValue('cost'))
            const formatted = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', }).format(cost)
            return h('div', { class: 'font-medium px-2' }, formatted)
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
                            h('div', { class: 'px-2 truncate w-28' }, notes)
                        ),
                        h(TooltipContent, {}, () =>
                            h('p', {}, notes)
                        )
                    ]
                })
            );
        },
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Created At', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const purchase = row.original
            const date = new Date(purchase.created_at)
            const formattedDate = new Intl.DateTimeFormat('en-PH', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            }).format(date)

            const timeAgoString = timeAgo(date);

            return h('div', { class: 'px-2' }, [
                h('div', {}, formattedDate),
                h('div', { class: 'text-xs text-gray-500' }, timeAgoString)
            ]);
        },
    },
    {
        accessorKey: 'updated_at',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Updated At', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const purchase = row.original
            const date = new Date(purchase.updated_at)
            const formattedDate = new Intl.DateTimeFormat('en-PH', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            }).format(date)

            const timeAgoString = timeAgo(date);

            return h('div', { class: 'px-2' }, [
                h('div', {}, formattedDate),
                h('div', { class: 'text-xs text-gray-500' }, timeAgoString)
            ]);
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const purchase = row.original;
            const units = props.units
            const suppliers = props.suppliers
            const categories = props.categories
            const transactions = props.transactions
            const subcategories = props.subcategories

            return h('div', { class: 'flex items-center gap-1' }, [
                h(Edit, {
                    purchases: purchase,
                    units,
                    suppliers,
                    categories,
                    transactions,
                    subcategories,
                    onPurchaseUpdated: handlePurchaseEdited
                }),
                h(Delete, {
                    purchaseId: purchase.id,
                    onPurchaseDeleted: handlePurchaseDeleted
                }),
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
            'cost',
            'notes',
            'quantity',
            'created_at',
            'supplier.name',
            'category.name',
            'subcategory.name',
            'unit_measure.abbreviation',
            'transaction_number',
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