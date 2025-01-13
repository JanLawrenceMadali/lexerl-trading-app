<script setup>
import { ref, watch } from 'vue';
import { Plus, Loader2 } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3'
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Button } from '@/Components/ui/button';
import InputError from '@/Components/InputError.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog';
import Swal from 'sweetalert2';
import { computed } from 'vue';

const emit = defineEmits(['create-category']);

const form = useForm({
    name: null
});

const isOpen = ref(false);

const closeSheet = () => {
    form.reset();
    form.clearErrors();
    isOpen.value = false;
};

const submit = () => {
    form.post(route('categories.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            emit('create-category', response.props.categories);
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

// clear errors when input/select value is not empty
watch(
    () => form.data(),
    (newValue, oldValue) => {
        Object.keys(newValue).forEach(key => {
            if (newValue[key] !== oldValue[key] && form.errors[key]) {
                form.errors[key] = null;
            }
        });
    },
    { deep: true }
);

const isSubmitDisabled = computed(() => {
    const commonFieldsValid =
        form.name

    return !commonFieldsValid || form.processing;
});
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm" class="gap-1 h-7">
                <Plus class="mr-1 size-4" />
                Add new category
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Add new category</DialogTitle>
                <DialogDescription>
                    Fill in the form below to add a new category. Click submit when you're done.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit">
                <div class="grid gap-2 my-4">
                    <Label for="name" class="after:content-['*'] after:ml-0.5 after:text-red-500">Name</Label>
                    <Input v-model="form.name" id="name" type="text" />
                    <InputError :message="form.errors.name" />
                </div>
                <DialogFooter>
                    <Button variant="outline" type="button"
                        class="gap-1 h-7 text-white bg-[#C00F0C] hover:bg-red-600 hover:text-white" @click="closeSheet">
                        Cancel
                    </Button>
                    <Button class="gap-1 h-7" type="submit" :disabled="isSubmitDisabled">
                        <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                        Submit
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
