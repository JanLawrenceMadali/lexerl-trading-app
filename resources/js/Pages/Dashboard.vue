<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ScrollArea } from '@/Components/ui/scroll-area'
import { ArrowRightFromLine, CirclePlus, Delete, Download, PhilippinePeso, Redo, RefreshCcw, SquareArrowOutUpRight, Wrench } from 'lucide-vue-next'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import Header from '@/Components/Header.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    total_sale: { type: Number },
    total_gross: { type: Number },
    total_purchase: { type: Number },
    activity_logs: { type: Object },
    total_collectible: { type: Number },
})

const items = ref([
    { label: 'Dashboard', href: 'dashboard' }
])

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};

const formattedDate = (value) => new Intl.DateTimeFormat('en-PH', {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: 'numeric',
    hour12: true
}).format(value)

</script>

<template>

    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <Header :items="items" />
        <div class="flex flex-col flex-1 gap-4 px-4 md:gap-8 md:px-6">
            <div class="grid grid-cols-4 gap-8">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                        <CardTitle class="text-sm font-medium">
                            Total Purchases
                        </CardTitle>
                        <PhilippinePeso class="w-4 h-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ formatCurrency(total_purchase) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            sample description
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                        <CardTitle class="text-sm font-medium">
                            Total Sales
                        </CardTitle>
                        <PhilippinePeso class="w-4 h-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ formatCurrency(total_sale) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            sample description
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                        <CardTitle class="text-sm font-medium">
                            Total Collectibles
                        </CardTitle>
                        <PhilippinePeso class="w-4 h-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ formatCurrency(total_collectible) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            sample description
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                        <CardTitle class="text-sm font-medium">
                            Total Gross Profit
                        </CardTitle>
                        <PhilippinePeso class="w-4 h-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ formatCurrency(total_purchase - total_sale) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            sample description
                        </p>
                    </CardContent>
                </Card>
            </div>
            <div class="grid grid-cols-3 gap-8">
                <Card>
                    <CardHeader>
                        <CardTitle>
                            <Link :href="route('activity_logs')" class="flex items-center gap-2 hover:underline">
                            Logs
                            <SquareArrowOutUpRight class="size-4" />
                            </Link>
                        </CardTitle>
                    </CardHeader>
                    <ScrollArea class="h-[450px] mr-2">
                        <CardContent class="grid gap-6">
                            <div v-for="log in activity_logs" :key="log"
                                :class="['flex items-center justify-between p-2 rounded-lg']">
                                <div class="flex items-center gap-4">
                                    <span v-if="log.action === 'created'">
                                        <CirclePlus class="text-emerald-500 size-6" />
                                        <!-- Bright green for new items -->
                                    </span>
                                    <span v-else-if="log.action === 'updated'">
                                        <RefreshCcw class="text-amber-500 size-6" />
                                    </span>
                                    <span v-else-if="log.action === 'deleted'">
                                        <Delete class="text-rose-600 size-6" />
                                    </span>
                                    <span v-else-if="log.action === 'exported'">
                                        <ArrowRightFromLine class="text-blue-500 size-6" />
                                    </span>
                                    <span v-else-if="log.action === 'manual'">
                                        <Wrench class="text-purple-500 size-6" />
                                    </span>
                                    <span v-else-if="log.action === 'download'">
                                        <Download class="text-cyan-500 size-6" />
                                    </span>
                                    <span v-else-if="log.action === 'restore'">
                                        <Redo class="text-indigo-500 size-6" />
                                    </span>

                                    <div class="grid gap-1">
                                        <p class="text-sm font-medium leading-none">
                                            {{ log.description }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ formattedDate(new Date(log.created_at)) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="activity_logs.length === 0" class="flex items-center justify-center">
                                <p class="text-sm text-muted-foreground">
                                    No activity logs found.
                                </p>
                            </div>
                        </CardContent>
                    </ScrollArea>
                </Card>
                <!-- <Card class="col-span-2">
                    <CardHeader class="flex flex-row items-center">
                        <div class="grid gap-2">
                            <CardTitle>Transactions</CardTitle>
                            <CardDescription>
                                Recent transactions from your store.
                            </CardDescription>
                        </div>
                        <Button as-child size="sm" class="gap-1 ml-auto">
                            <a href="#">
                                View All
                                <ArrowUpRight class="w-4 h-4" />
                            </a>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Customer</TableHead>
                                    <TableHead class="hidden xl:table-column">
                                        Type
                                    </TableHead>
                                    <TableHead class="hidden xl:table-column">
                                        Status
                                    </TableHead>
                                    <TableHead class="hidden xl:table-column">
                                        Date
                                    </TableHead>
                                    <TableHead class="text-right">
                                        Amount
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow>
                                    <TableCell>
                                        <div class="font-medium">
                                            Liam Johnson
                                        </div>
                                        <div class="hidden text-sm text-muted-foreground md:inline">
                                            liam@example.com
                                        </div>
                                    </TableCell>
                                    <TableCell class="hidden xl:table-column">
                                        Sale
                                    </TableCell>
                                    <TableCell class="hidden xl:table-column">
                                        <Badge class="text-xs" variant="outline">
                                            Approved
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="hidden md:table-cell lg:hidden xl:table-column">
                                        2023-06-23
                                    </TableCell>
                                    <TableCell class="text-right">
                                        $250.00
                                    </TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableCell>
                                        <div class="font-medium">
                                            Olivia Smith
                                        </div>
                                        <div class="hidden text-sm text-muted-foreground md:inline">
                                            olivia@example.com
                                        </div>
                                    </TableCell>
                                    <TableCell class="hidden xl:table-column">
                                        Refund
                                    </TableCell>
                                    <TableCell class="hidden xl:table-column">
                                        <Badge class="text-xs" variant="outline">
                                            Declined
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="hidden md:table-cell lg:hidden xl:table-column">
                                        2023-06-24
                                    </TableCell>
                                    <TableCell class="text-right">
                                        $150.00
                                    </TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableCell>
                                        <div class="font-medium">
                                            Noah Williams
                                        </div>
                                        <div class="hidden text-sm text-muted-foreground md:inline">
                                            noah@example.com
                                        </div>
                                    </TableCell>
                                    <TableCell class="hidden xl:table-column">
                                        Subscription
                                    </TableCell>
                                    <TableCell class="hidden xl:table-column">
                                        <Badge class="text-xs" variant="outline">
                                            Approved
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="hidden md:table-cell lg:hidden xl:table-column">
                                        2023-06-25
                                    </TableCell>
                                    <TableCell class="text-right">
                                        $350.00
                                    </TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableCell>
                                        <div class="font-medium">
                                            Emma Brown
                                        </div>
                                        <div class="hidden text-sm text-muted-foreground md:inline">
                                            emma@example.com
                                        </div>
                                    </TableCell>
                                    <TableCell class="hidden xl:table-column">
                                        Sale
                                    </TableCell>
                                    <TableCell class="hidden xl:table-column">
                                        <Badge class="text-xs" variant="outline">
                                            Approved
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="hidden md:table-cell lg:hidden xl:table-column">
                                        2023-06-26
                                    </TableCell>
                                    <TableCell class="text-right">
                                        $450.00
                                    </TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableCell>
                                        <div class="font-medium">
                                            Liam Johnson
                                        </div>
                                        <div class="hidden text-sm text-muted-foreground md:inline">
                                            liam@example.com
                                        </div>
                                    </TableCell>
                                    <TableCell class="hidden xl:table-column">
                                        Sale
                                    </TableCell>
                                    <TableCell class="hidden xl:table-column">
                                        <Badge class="text-xs" variant="outline">
                                            Approved
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="hidden md:table-cell lg:hidden xl:table-column">
                                        2023-06-27
                                    </TableCell>
                                    <TableCell class="text-right">
                                        $550.00
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card> -->

            </div>
        </div>
    </AuthenticatedLayout>
</template>
