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

const isOpen = ref(true);

const close = () => {
    isOpen.value = false;
}

</script>

<template>
    <Dialog :open="isOpen">
        <DialogTrigger as-child>
            <Button class="disabled:cursor-not-allowed disabled:bg-[#757575] h-7">
                Submit
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Are you sure you want to proceed?</DialogTitle>
                <DialogDescription>
                    You are about to increase the quantity of {{ inventory.quantity }} to {{ quantity }}.
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