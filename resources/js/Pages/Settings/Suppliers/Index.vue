<script setup>
import Datatable from './Datatable.vue';
import Settings from '../Index.vue'

const props = defineProps({
    suppliers: Object
})

</script>

<template>

    <Head title="Manage Suppliers" />
    <Settings>
        <Card>
            <CardHeader>
                <CardTitle>Suppliers</CardTitle>
                <CardDescription class="flex items-center justify-between">
                    Manage suppliers and view their information.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Datatable :suppliers="suppliers" />
            </CardContent>
            <CardFooter>
            </CardFooter>
        </Card>
    </Settings>
</template>
