<script setup>
import { ref } from 'vue';
import { Plus, Loader2 } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3'
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Button } from '@/Components/ui/button';
import InputError from '@/Components/InputError.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select/index';
import Swal from 'sweetalert2';

const props = defineProps({
    categories: { type: Object },
    category_id: { type: Number, default: null },
    routing: { type: String }
})

const emit = defineEmits(['create-subcategory']);

const form = useForm({
    name: null,
    category_id: props.category_id ? String(props.category_id) : null
})

const isOpen = ref(false);

const closeSheet = () => {
    isOpen.value = false;
    form.reset();
    form.clearErrors();
};

const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

const submit = () => {
    form.post(route('subcategories.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            emit('create-subcategory', response.props.subcategories);
            closeSheet();
            if (props.routing) {
                Toast.fire({
                    icon: "success",
                    title: "Success!"
                });
            } else {
                if (response.props.flash.success) {
                    Swal.fire({
                        text: response.props.flash.success,
                        iconHtml: '<img src="/assets/icons/Success.png">',
                        confirmButtonColor: "#1B1212",
                    });
                } else if (response.props.flash.error) {
                    Swal.fire('Error', "Oops! Something went wrong", 'error');
                }
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
            <Button variant="outline" size="sm" class="m-2">
                <Plus class="mr-1 size-4" />
                Add new sub category
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Create new Sub Category</DialogTitle>
                <DialogDescription>
                    Fill in the details of the new sub category and assign which category. Click submit when you're
                    done.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit">
                <div class="grid gap-2 my-4">
                    <Label for="name" class="after:content-['*'] after:ml-0.5 after:text-red-500">Name</Label>
                    <Input v-model="form.name" id="name" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label class="after:content-['*'] after:ml-0.5 after:text-red-500">Category</Label>
                    <Select v-model="form.category_id">
                        <SelectTrigger>
                            <SelectValue placeholder="Select category" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem v-for="category in categories" :key="category.id"
                                    :value="String(category.id)">
                                    {{ category.name }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.category_id" />
                </div>
                <DialogFooter>
                    <Button variant="secondary" type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                        Submit
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
