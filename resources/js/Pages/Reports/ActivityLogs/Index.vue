<script setup>
import Datatable from './Datatable.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    activity_logs: Object
})
</script>

<template>

    <Head title="Logs" />
    <AuthenticatedLayout>
        <div class="px-4 py-4 sm:py-0 md:gap-8 md:px-6">
            <Card>
                <CardHeader>
                    <CardTitle>Logs</CardTitle>
                    <CardDescription class="flex items-center justify-between">
                        View logs to track user actions and changes in the system.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Datatable :activity_logs="activity_logs" />
                </CardContent>
                <CardFooter>
                </CardFooter>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
