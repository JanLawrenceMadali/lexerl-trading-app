<script setup>
import Datatable from './Datatable.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    sales: Object
})

</script>

<template>

    <Head title="Collectibles" />
    <AuthenticatedLayout>
        <div class="px-4 py-4 sm:py-0 md:gap-8 md:px-6">
            <Card>
                <CardHeader>
                    <CardTitle>Collectibles</CardTitle>
                    <CardDescription class="flex items-center justify-between">
                        Manage your collectibles and view their information.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Datatable :sales="sales" />
                </CardContent>
                <CardFooter>
                </CardFooter>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
