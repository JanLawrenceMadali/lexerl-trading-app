<script setup>
import Datatable from './Datatable.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    inventories: Object
})

</script>

<template>

    <Head title="Current Inventory" />
    <AuthenticatedLayout>
        <div class="px-4 py-4 sm:py-0 md:gap-8 md:px-6">
            <Card>
                <CardHeader>
                    <CardTitle>Current Inventory</CardTitle>
                    <CardDescription class="flex items-center justify-between">
                        View list of all the current inventory in the system.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Datatable :inventories="inventories" />
                </CardContent>
                <CardFooter>
                </CardFooter>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
