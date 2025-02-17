<script setup>
import { computed, ref, watch } from 'vue';
import { Plus, Loader2 } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3'
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Button } from '@/Components/ui/button';
import InputError from '@/Components/InputError.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select/index';
import Swal from 'sweetalert2';
import { cn } from '@/lib/utils';

const props = defineProps({
    categories: { type: Object },
    category_id: { type: Number, default: null },
    routing: { type: String },
    class: { type: String, required: false }
})

const emit = defineEmits(['create-subcategory']);

const form = useForm({
    name: null,
    category_id: props.category_id ? String(props.category_id) : null
})

const isOpen = ref(false);

const closeSheet = () => {
    form.reset();
    form.clearErrors();
    isOpen.value = false;
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
                } else {
                    Swal.fire({
                        text: response.props.flash.error,
                        icon: 'error',
                        confirmButtonColor: "#1B1212",
                    });
                }
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
        form.name &&
        form.category_id;

    return !commonFieldsValid || form.processing;
});
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm" :class="cn('gap-1 h-7', props.class)">
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
                    <Button variant="outline" type="button"
                        class="gap-1 h-7 text-white bg-[#C00F0C] hover:bg-red-600 hover:text-white" @click="closeSheet">
                        Cancel
                    </Button>
                    <Button size="sm" class="gap-1 h-7" type="submit" :disabled="isSubmitDisabled">
                        <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                        Submit
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
