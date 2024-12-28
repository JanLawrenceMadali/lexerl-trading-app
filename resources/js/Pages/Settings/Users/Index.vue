<script setup>
import Datatable from './Datatable.vue';
import Settings from '../Index.vue'

const props = defineProps({
    users: Object,
    roles: Object
})

</script>

<template>

    <Head title="Manage Users" />
    <Settings>
        <Card>
            <CardHeader>
                <CardTitle>Users</CardTitle>
                <CardDescription class="flex items-center justify-between">
                    Manage users and view their information.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Datatable :users="users" :roles="roles" />
            </CardContent>
            <CardFooter>
            </CardFooter>
        </Card>
    </Settings>
</template>
