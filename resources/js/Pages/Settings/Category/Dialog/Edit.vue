<script setup>
import { ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3'
import { Edit, Loader2 } from 'lucide-vue-next';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Button } from '@/Components/ui/button';
import InputError from '@/Components/InputError.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog';
import Swal from 'sweetalert2';

const props = defineProps({
    categories: Object,
})

const emit = defineEmits(['update-category']);

const form = useForm({
    name: props.categories.name
})

watch(() => props.categories, (newCategories) => {
    form.name = newCategories.name
}, { immediate: true })

const isOpen = ref(false);

const closeSheet = () => {
    isOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.patch(route('categories.update', props.categories), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            emit('update-category', response.props.categories);
            closeSheet();
            if (response.props.flash.success) {
                Swal.fire({
                    text: response.props.flash.success,
                    iconHtml: '<img src="/assets/icons/Success.png">',
                    confirmButtonColor: "#1B1212",
                });
            } else if (response.props.flash.error) {
                Swal.fire('Error', "Oops! Something went wrong", 'error');
            }
        },
        onError: (errors) => {
            // console.log(errors);
        }
    })
}

</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="ghost" size="xs" title="Edit">
                <Edit :size="18" class="text-green-500" />
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Edit category</DialogTitle>
                <DialogDescription>
                    Make changes to the category here. Click save changes when you're done.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit">
                <div class="grid gap-2 my-4">
                    <Label for="name" class="after:content-['*'] after:ml-0.5 after:text-red-500">Name</Label>
                    <Input v-model="form.name" id="name" type="text" />
                    <InputError :message="form.errors.name" />
                </div>
                <DialogFooter>
                    <Button variant="secondary" type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                        Save changes
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
