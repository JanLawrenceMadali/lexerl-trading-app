<script setup>
import Datatable from './Datatable.vue';
import Settings from '../Index.vue'

const props = defineProps({
    subcategories: Object,
    categories: Object
})
</script>

<template>

    <Head title="Manage Sub Category" />
    <Settings>
        <Card>
            <CardHeader>
                <CardTitle>Sub Category</CardTitle>
                <CardDescription class="flex items-center justify-between">
                    Manage sub categories and view their information.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Datatable :subcategories="subcategories" :categories="categories" />
            </CardContent>
            <CardFooter>
            </CardFooter>
        </Card>
    </Settings>
</template>
