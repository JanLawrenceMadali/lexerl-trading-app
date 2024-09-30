<!-- CreateSupplierDialog.vue -->
<script setup>
import { ref } from 'vue'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'

const props = defineProps({
    isOpen: Boolean
})

const emit = defineEmits(['update:isOpen', 'create'])

const supplierName = ref('')

const handleSubmit = () => {
    emit('create', supplierName.value)
    supplierName.value = ''
    emit('update:isOpen', false)
}
</script>

<template>
    <Dialog :open="isOpen" @update:open="$emit('update:isOpen', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Create New Supplier</DialogTitle>
                <DialogDescription>
                    Enter the details for the new supplier.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="handleSubmit">
                <div class="grid gap-4 py-4">
                    <div class="grid items-center grid-cols-4 gap-4">
                        <label for="name" class="text-right">Name</label>
                        <Input id="name" v-model="supplierName" class="col-span-3" />
                    </div>
                </div>
                <DialogFooter>
                    <Button type="submit">Create Supplier</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
