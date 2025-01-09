<script setup>
import { Link } from '@inertiajs/vue3'
import { ScrollArea } from '@/Components/ui/scroll-area'
import { ArrowRightFromLine, CircleCheck, CirclePlus, CircleX, Delete, Download, LogIn, LogOut, Redo, RefreshCcw, SquareArrowOutUpRight, Wrench } from 'lucide-vue-next'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Chart from '@/Components/Chart.vue'

const props = defineProps({
    total_sale: { type: Number },
    total_purchase: { type: Number },
    activity_logs: { type: Object },
    total_collectible: { type: Number },
    monthly_sales: { type: Object },
    chartData: { type: Object }
})

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', {
        // style: 'currency',
        // currency: 'PHP',
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
        <div class="flex flex-col flex-1 gap-6 px-6">
            <div class="grid grid-cols-4 gap-6">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                        <CardTitle class="text-sm font-medium">
                            Total Purchases
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="mt-4">
                        <div class="text-4xl text-[#772E25]">
                            <sup>PHP</sup> <span class="font-semibold">{{ formatCurrency(total_purchase) }}</span>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                        <CardTitle class="text-sm font-medium">
                            Total Sales
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="mt-4">
                        <div class="text-4xl text-[#772E25]">
                            <sup>PHP</sup> <span class="font-semibold">{{ formatCurrency(total_sale) }}</span>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                        <CardTitle class="text-sm font-medium">
                            Total Collectibles
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="mt-4">
                        <div class="text-4xl text-[#772E25]">
                            <sup>PHP</sup> <span class="font-semibold">{{ formatCurrency(total_collectible) }}</span>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                        <CardTitle class="text-sm font-medium">
                            Total Gross Profit
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="mt-4">
                        <div class="text-4xl text-[#772E25]">
                            <sup>PHP</sup> <span class="font-semibold">{{ formatCurrency(total_sale - total_purchase)
                                }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
            <div class="grid grid-cols-3 gap-6">
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
                                    <span v-if="log.action === 'create'">
                                        <CirclePlus class="text-emerald-500 size-6" />
                                    </span>
                                    <span v-else-if="log.action === 'update'">
                                        <RefreshCcw class="text-amber-500 size-6" />
                                    </span>
                                    <span v-else-if="log.action === 'delete'">
                                        <Delete class="text-rose-600 size-6" />
                                    </span>
                                    <span v-else-if="log.action === 'cancel'">
                                        <CircleX class="text-rose-600 size-6" />
                                    </span>
                                     <span v-else-if="log.action === 'paid'">
                                        <CircleCheck class="text-green-600 size-6" />
                                    </span>
                                    <span v-else-if="log.action === 'export'">
                                        <ArrowRightFromLine class="text-blue-500 size-6" />
                                    </span>
                                    <span v-else-if="log.action === 'backup'">
                                        <Wrench class="text-purple-500 size-6" />
                                    </span>
                                    <span v-else-if="log.action === 'download'">
                                        <Download class="text-cyan-500 size-6" />
                                    </span>
                                    <span v-else-if="log.action === 'restore'">
                                        <Redo class="text-indigo-500 size-6" />
                                    </span>
                                    <span v-else-if="log.action === 'login'">
                                        <LogIn class="text-green-500 size-6" />
                                    </span>
                                    <span v-else-if="log.action === 'logout'">
                                        <LogOut class="text-red-500 size-6" />
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
                <Card class="col-span-2">
                    <CardHeader>
                        <CardTitle>
                            <Link :href="route('sales')" class="flex items-center gap-2 hover:underline">
                            Sales
                            <SquareArrowOutUpRight class="size-4" />
                            </Link>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Chart :chart-data="chartData" />
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
