<script setup>
import Datatable from './Datatable.vue';
import Settings from '../Index.vue'

const props = defineProps({
    units: Object
})

</script>

<template>

    <Head title="Manage Units" />
    <Settings>
        <Card>
            <CardHeader>
                <CardTitle>Units</CardTitle>
                <CardDescription class="flex items-center justify-between">
                    Manage units and view their information.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Datatable :units="units" />
            </CardContent>
            <CardFooter>
            </CardFooter>
        </Card>
    </Settings>
</template>
