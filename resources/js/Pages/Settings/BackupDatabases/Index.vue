<script setup>
import Datatable from './Datatable.vue';
import Settings from '../Index.vue'

const props = defineProps({
    backups: Object
})

</script>

<template>

    <Head title="Manage Backup Databases" />
    <Settings>
        <Card>
            <CardHeader>
                <CardTitle>Backup Databases</CardTitle>
                <CardDescription class="flex items-center justify-between">
                    Manage backup databases.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Datatable :backups="backups" />
            </CardContent>
            <CardFooter>
            </CardFooter>
        </Card>
    </Settings>
</template>
