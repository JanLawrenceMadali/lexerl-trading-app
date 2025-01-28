<script setup>
import { Button } from '@/Components/ui/button'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/Components/ui/dialog'
import { TriangleAlert } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    inventory: {
        type: Object,
        required: true
    },
    quantity: {
        type: Number,
        required: true
    },
    submit: {
        type: Function,
        required: true
    },
});

const isOpen = ref(false);

const close = () => {
    isOpen.value = false;
}

</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button class="disabled:cursor-not-allowed disabled:bg-[#757575] h-7">
                Submit
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle class="text-2xl text-slate-700">Are you sure you want to proceed?</DialogTitle>
                <DialogDescription class="space-y-4">
                    <p v-if="quantity > inventory.quantity" class="text-lg text-center text-red-500">
                        You are about to increase the quantity from {{
                            inventory.quantity.toLocaleString() }} to {{ quantity.toLocaleString() }}.</p>
                    <p v-else class="text-lg text-center text-red-500">
                        You are about to decrease the quantity from {{ inventory.quantity.toLocaleString() }} to {{
                            quantity.toLocaleString() }}.</p>
                    <p class="flex text-orange-500">
                        <TriangleAlert class="w-5 h-5 mr-1" />
                        Warning: This may affect the inventory quantity. This action cannot be undone.
                    </p>
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button type="button" @click="close()" class="bg-[#C00F0C] hover:bg-red-500 h-7">
                    No
                </Button>
                <Button type="button" @click="submit()" class="disabled:cursor-not-allowed disabled:bg-[#757575] h-7">
                    Yes
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>