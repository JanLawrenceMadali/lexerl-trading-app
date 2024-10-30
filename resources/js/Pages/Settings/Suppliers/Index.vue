<script setup>
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow, } from '@/Components/ui/table'
import Settings from '../Index.vue'
const props = defineProps({
    suppliers: Object
})

</script>

<template>

    <Head title="Manage Suppliers" />
    <Settings>
        <Table>
            <TableCaption>A list of your recent invoices.</TableCaption>
            <TableHeader>
                <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Contact Person</TableHead>
                    <TableHead>Contact Number</TableHead>
                    <TableHead>Address1</TableHead>
                    <TableHead>Address2</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="supplier in suppliers" :key="supplier.id">
                    <TableCell>{{ supplier.name }}</TableCell>
                    <TableCell>{{ supplier.email }}</TableCell>
                    <TableCell>{{ supplier.contact_person }}</TableCell>
                    <TableCell>{{ supplier.contact_number }}</TableCell>
                    <TableCell>{{ supplier.address1 }}</TableCell>
                    <TableCell>{{ supplier.address2 }}</TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </Settings>
</template>
