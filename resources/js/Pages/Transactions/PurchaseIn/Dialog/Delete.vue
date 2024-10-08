<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/Components/ui/alert-dialog'
import { Button } from '@/Components/ui/button'
import { Trash2 } from 'lucide-vue-next'
import { useToast } from '@/Components/ui/toast'
import ErrorToast from '@/Components/ErrorToast.vue'

const props = defineProps({
    purchaseId: {
        type: Number,
        required: true
    }
})

const emit = defineEmits(['purchaseDeleted'])

const isOpen = ref(false)

const open = () => isOpen.value = true

const close = () => isOpen.value = false

const { toast } = useToast()

const handleDelete = () => {
    router.delete(route('purchase-in.destroy', props.purchaseId), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            close()
            emit('purchaseDeleted', props.purchaseId)
            toast({
                title: 'Success 🥳',
                description: 'Purchase In deleted successfully',
            });
        },
        onError: (errors) => {
            const errorMessages = Object.entries(errors)
                .flatMap(([field, messages]) => {
                    if (Array.isArray(messages)) {
                        return messages;
                    }
                    return [messages];
                });

            toast({
                title: "Oops! Something went wrong 😣",
                description: h(ErrorToast, {
                    messages: errorMessages
                }),
                variant: "destructive",
            })
            console.error('Error creating purchase:', errors);
        },
    })
}
</script>

<template>
    <AlertDialog :open="isOpen" @update:open="isOpen = $event">
        <AlertDialogTrigger as-child>
            <Button variant="ghost" size="xs" class="text-red-600 hover:text-red-800" title="Delete" @click="open">
                <Trash2 :size="18" />
            </Button>
        </AlertDialogTrigger>
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    This action cannot be undone. This will permanently delete the purchase and remove it from our
                    servers.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="handleDelete">Delete</AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
