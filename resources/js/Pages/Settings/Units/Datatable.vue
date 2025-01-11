<script setup>
import { computed, h, ref } from 'vue'
import { valueUpdater } from '@/lib/utils'
import { Input } from '@/Components/ui/input'
import { Button } from '@/Components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/Components/ui/table'
import { ArrowUpDown, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, Search, Trash2 } from 'lucide-vue-next'
import { FlexRender, getCoreRowModel, getExpandedRowModel, getFilteredRowModel, getPaginationRowModel, getSortedRowModel, useVueTable, } from '@tanstack/vue-table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import Create from './Dialog/Create.vue'
import View from './Dialog/View.vue'
import Edit from './Dialog/Edit.vue'
import Swal from 'sweetalert2'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    units: Object,
})

const data = ref(props.units)
const sorting = ref([])
const filter = ref('')
// Pagination state
const pagination = ref({
    pageIndex: 0,
    pageSize: data.value.length, // Default to "All"
    isAllSelected: true, // Tracks if "All" is explicitly selected
});

// Fixed page sizes
const fixedSizes = [10, 20, 30, 40, 50];
const sizes = computed(() => [...fixedSizes, 'All']);

// Helper to determine the selected value in the dropdown
const getSelectedValue = () => {
    if (pagination.value.isAllSelected) {
        return 'All';
    }
    return String(pagination.value.pageSize);
};

const handleSelection = (value) => {
    const newPageSize = value === 'All' ? data.value.length : Number(value);

    // Update pagination state
    pagination.value = {
        ...pagination.value,
        pageSize: newPageSize,
        pageIndex: 0, // Reset to first page when changing page size
        isAllSelected: value === 'All'
    };

    // Update table page size
    table.setPageSize(newPageSize);
};

// Updated display range function
const getDisplayRange = () => {
    const totalItems = data.value.length;
    const pageIndex = table.getState().pagination.pageIndex;
    const pageSize = table.getState().pagination.pageSize;

    const start = pageIndex * pageSize + 1;
    const end = Math.min((pageIndex + 1) * pageSize, totalItems);

    return `${start} - ${end} of ${totalItems}`;
}

const handleUnit = (unit) => {
    data.value = unit
}

const handleDeleted = (id) => {
    Swal.fire({
        title: '<h2 class="custom-title">Are you sure you want to delete this unit?</h2>',
        html: '<p class="custom-text">Please note that this is irreversible and all related data to this unit will be deleted.</p>',
        iconHtml: '<img src="/assets/icons/Warning.png">',
        showCancelButton: true,
        confirmButtonColor: "#C00F0C",
        cancelButtonColor: "#1B1212",
        confirmButtonText: "Yes, delete it",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('units.destroy', id), {
                onSuccess: (response) => {
                    data.value = response.props.units
                    if (response.props.flash.success) {
                        Swal.fire({
                            text: response.props.flash.success,
                            iconHtml: '<img src="/assets/icons/Success.png">',
                            confirmButtonColor: "#1B1212",
                        });
                    } else if (response.props.flash.error) {
                        Swal.fire('Error', "Oops! Something went wrong", 'error');
                    }
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

const formattedDate = (value) => new Intl.DateTimeFormat('en-PH', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
}).format(value)

const columns = [
    {
        accessorKey: 'name',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Name', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const { name } = row.original;
            return h('div', { class: 'px-2' }, [
                h('div', { class: 'font-medium' }, name),
            ])
        },
    },
    {
        accessorKey: 'abbreviation',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Abbreviation', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const { abbreviation } = row.original;
            return h('div', { class: 'px-2' }, [
                h('div', { class: 'font-medium' }, abbreviation),
            ])
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

            const timeAgoString = timeAgo(date);

            return h('div', { class: 'px-2' }, [
                h('div', formattedDate(date)),
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
            const { updated_at } = row.original
            const date = new Date(updated_at)

            const timeAgoString = timeAgo(date);

            return h('div', { class: 'px-2' }, [
                h('div', formattedDate(date)),
                h('div', { class: 'text-xs text-gray-500' }, timeAgoString)
            ]);
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const unit = row.original;

            return h('div', { class: 'flex items-center gap-1 justify-end' }, [
                h(View, {
                    unit,
                }),
                h(Edit, {
                    unit,
                    onUpdateUnit: handleUnit
                }),
                h(Button, {
                    size: 'xs',
                    variant: 'ghost',
                    class: 'text-red-600 hover:text-red-800',
                    title: 'Delete',
                    onClick: () => handleDeleted(unit.id)
                }, () => h(Trash2, { class: 'size-5' })),
            ]);
        },
    },
]

const table = useVueTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getExpandedRowModel: getExpandedRowModel(),
    onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
    state: {
        get sorting() { return sorting.value },
        get globalFilter() { return filter.value },
        get pagination() { return pagination.value }
    },
    onPaginationChange: (updater) => {
        pagination.value = typeof updater === 'function' ? updater(pagination.value) : updater
    },
    globalFilterFn: (row, columnId, filterValue) => {
        const searchableFields = [
            'name',
            'abbreviation'
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
        <div class="relative items-center col-span-2">
            <Input v-model="filter" type="search" placeholder="Search..." class="pl-7 h-8 w-[150px] lg:w-[250px]" />
            <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                <Search class="size-4 text-muted-foreground" />
            </span>
        </div>
        <Create @create-unit="handleUnit" />
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
            <Select :model-value="getSelectedValue()" @update:model-value="(value) => handleSelection(value)">
                <SelectTrigger class="h-8 w-[70px]">
                    <SelectValue :placeholder="getSelectedValue()" />
                </SelectTrigger>
                <SelectContent side="top">
                    <SelectItem v-for="size in sizes" :key="size" :value="size === 'All' ? size : String(size)">
                        {{ size }}
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