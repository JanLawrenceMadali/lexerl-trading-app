<script setup>
import { ref } from "vue";
import Datatable from './Datatable.vue';
import Header from '@/Components/Header.vue';
import Settings from '../Index.vue'

const props = defineProps({
    suppliers: Object
})

const items = ref([
    { label: 'Settings', href: 'settings' },
    { label: 'Suppliers', href: 'suppliers' },
])
</script>

<template>
    <Head title="Manage Suppliers" />
    <Settings>
        <div class="px-4">
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
        </div>
    </Settings>
</template>
