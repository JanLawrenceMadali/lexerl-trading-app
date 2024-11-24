<script setup>
import { ref } from "vue";
import Datatable from './Datatable.vue';
import Settings from '../Index.vue'

const props = defineProps({
    categories: Object
})

const items = ref([
    { label: 'Settings', href: 'settings' },
    { label: 'Category', href: 'categories' },
])
</script>

<template>
    <Head title="Manage Category" />
    <Settings>
        <div class="px-4">
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
        </div>
    </Settings>
</template>
