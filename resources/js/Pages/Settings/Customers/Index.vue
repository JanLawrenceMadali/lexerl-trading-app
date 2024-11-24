<script setup>
import Datatable from './Datatable.vue';
import Settings from '../Index.vue'

const props = defineProps({
    customers: Object
})

</script>

<template>
    <Head title="Manage Customers" />
    <Settings>
        <div class="px-4">
            <Card>
                <CardHeader>
                    <CardTitle>Customers</CardTitle>
                    <CardDescription class="flex items-center justify-between">
                        Manage customers and view their information.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Datatable :customers="customers" />
                </CardContent>
                <CardFooter>
                </CardFooter>
            </Card>
        </div>
    </Settings>
</template>
