<script setup>
import { Button } from '@/Components/ui/button'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/Components/ui/dialog'
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
        <DialogContent class="p-8">
            <DialogHeader>
                <DialogTitle class="flex flex-col items-center justify-center">
                    <img src="/assets/icons/Warning.png" class="size-20" alt="">
                    <span class="mt-8 text-[22px] text-slate-700">Are you sure you want to proceed?</span>
                </DialogTitle>
                <DialogDescription class="mt-2 space-y-2 text-base text-center text-red-700">
                    <p v-if="quantity > inventory.quantity">
                        You are about to increase the quantity from {{
                            inventory.quantity.toLocaleString() }} to {{ quantity.toLocaleString() }}.</p>
                    <p v-else>
                        You are about to decrease the quantity from {{ inventory.quantity.toLocaleString() }} to {{
                            quantity.toLocaleString() }}.</p>
                    <p>
                        Please note that this action is irreversible and may affect the current inventory.
                    </p>
                </DialogDescription>
            </DialogHeader>
            <div class="flex justify-center gap-2 mt-2">
                <Button type="button" @click="submit()" class="bg-[#C00F0C] hover:bg-red-500 text-lg">
                    Yes
                </Button>
                <Button type="button" @click="close()" class="disabled:cursor-not-allowed disabled:bg-[#757575] text-lg">
                    Cancel
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>