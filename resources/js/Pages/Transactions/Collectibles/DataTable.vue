<script setup>
import { h, ref, computed, watch } from 'vue'
import { cn, valueUpdater } from '@/lib/utils'
import { Input } from '@/Components/ui/input'
import { Button } from '@/Components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/Components/ui/table'
import { ArrowUpDown, CalendarRange, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, File, RefreshCcw, Search } from 'lucide-vue-next'
import { FlexRender, getCoreRowModel, getExpandedRowModel, getFilteredRowModel, getPaginationRowModel, getSortedRowModel, useVueTable, } from '@tanstack/vue-table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Checkbox } from '@/Components/ui/checkbox/index'
import { useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import { RangeCalendar } from '@/Components/ui/range-calendar';
import { getLocalTimeZone } from '@internationalized/date'

const props = defineProps({
    sales: Object,
})

const data = ref(props.sales)
const sorting = ref([])
const filter = ref('')
const rowSelection = ref({})

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

const formattedCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};

const columns = [
    {
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            'checked': table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:checked': value => table.toggleAllPageRowsSelected(!!value),
            'ariaLabel': 'Select all',
        }),
        cell: ({ row }) => h(Checkbox, {
            'checked': row.getIsSelected(),
            'onUpdate:checked': value => row.toggleSelected(!!value),
            'ariaLabel': 'Select row',
        }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'transaction_number',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Transaction No.', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const { transaction_type } = row.original;
            const { transaction_number } = row.original;

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
                h('div', sale_date),
            ]);
        },
    },
    {
        accessorKey: 'due_dates',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Due Date', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const { due_date } = row.original;
            const { daysLeft } = row.original;

            return h('div', { class: 'px-2' }, [
                h('div', { class: 'font-medium' }, [
                    h('div', due_date),
                    h('div', { class: `${daysLeft < 0 ? 'text-xs text-red-500' : 'text-xs text-green-500'}` },
                        `${Math.abs(daysLeft)} ${daysLeft < 0 ? 'Day(s) overdue' : 'Day(s) Left'}`),
                ]),
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
        accessorKey: 'payment_method',
        header: ({ column }) => {
            return h(Button, { variant: 'ghost', size: 'xs', onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'), }, () => ['Payment Method', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const { payment_method } = row.original;

            return h('div', { class: 'px-2' }, payment_method);
        },
    }
]

const table = useVueTable({
    data: filteredData,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getExpandedRowModel: getExpandedRowModel(),
    onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
    onRowSelectionChange: updaterOrValue => valueUpdater(updaterOrValue, rowSelection),
    state: {
        get sorting() { return sorting.value },
        get rowSelection() { return rowSelection.value },
        get globalFilter() { return filter.value }
    },
    globalFilterFn: (row, columnId, filterValue) => {
        const searchableFields = [
            'sale_date',
            'due_date',
            'total_amount',
            'customer_name',
            'customer_email',
            'payment_method',
            'transaction_type',
            'transaction_number',
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

const form = useForm({ selectedIds: [] });

const selectedIds = computed(() =>
    table.getSelectedRowModel().rows.map(row => ({
        id: Number(row.original.id), status_id: Number(row.original.status_id)
    }))
);

watch(selectedIds, (newValue) => { form.selectedIds = newValue; });

const bulkUpdate = () => {
    form.post(route('collectibles.update'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            form.get(route('collectibles'))
            table.resetRowSelection();
            if (response.props.flash.success) {
                Swal.fire({
                    text: response.props.flash.success,
                    iconHtml: '<img src="/assets/icons/Success.png">',
                    confirmButtonColor: "#1B1212",
                });
            } else if (response.props.flash.error) {
                Swal.fire('Error', "Oops! Something went wrong", 'error');
            }
        },
        onError: (errors) => {
            // console.log(errors);
        }
    });
};

const isExporting = ref(false)

const exportData = () => {
    isExporting.value = true

    const params = {
        start_date: range.value.start
            ? df.format(range.value.start.toDate(getLocalTimeZone()))
            : null,
        end_date: range.value.end
            ? df.format(range.value.end.toDate(getLocalTimeZone()))
            : null,
    };
    const queryString = new URLSearchParams(params).toString();
    const exportUrl = `${route('collectibles.export')}?${queryString}`;

    window.location.href = exportUrl;

    setTimeout(() => {
        isExporting.value = false;
    }, 1000);
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
            <Button size="sm" variant="outline" class="gap-1 h-7" :disabled="isExporting || data.length === 0"
                @click="exportData">
                <File class="h-3.5 w-3.5" />
                <span class="sr-only sm:not-sr-only sm:whitespace-nowrap">
                    {{ isExporting ? 'Exporting...' : 'Export' }}
                </span>
            </Button>
            <Button size="sm" @click="bulkUpdate" class="gap-1 uppercase bg-[#023020] h-7"
                :disabled="selectedIds.length === 0">
                Mark as paid
            </Button>
        </div>
    </div>
    <span class="text-sm text-red-500">{{ form.errors.selectedIds }}</span>
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
        <div class="flex-1 text-sm text-muted-foreground">
            {{ table.getFilteredSelectedRowModel().rows.length }} of
            {{ table.getFilteredRowModel().rows.length }} row(s) selected.
        </div>
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
