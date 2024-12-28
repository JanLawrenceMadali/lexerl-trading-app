<script setup>
import Datatable from './Datatable.vue';
import Settings from '../Index.vue'

const props = defineProps({
    categories: Object
})

</script>

<template>

    <Head title="Manage Category" />
    <Settings>
        <Card>
            <CardHeader>
                <CardTitle>Category</CardTitle>
                <CardDescription class="flex items-center justify-between">
                    Manage categories and view their information.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Datatable :categories="categories" />
            </CardContent>
            <CardFooter>
            </CardFooter>
        </Card>
    </Settings>
</template>
