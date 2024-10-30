<script setup>
import { ref } from "vue";
import Datatable from './Datatable.vue';
import Header from '@/Components/Header.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    logs: Object
})

const items = ref([
    { label: 'Reports', href: '#' },
    { label: 'Activity Logs', href: 'activity_logs' },
])
</script>

<template>
    <Head title="Reports" />
    <AuthenticatedLayout>
        <Header :items="items" />
        <div class="px-4 py-4 sm:py-0 md:gap-8 md:px-6">
            <Card>
                <CardHeader>
                    <CardTitle>Activity Logs</CardTitle>
                    <CardDescription class="flex items-center justify-between">
                        Manage your purchases and view their information.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Datatable :logs="logs" />
                </CardContent>
                <CardFooter>
                </CardFooter>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
