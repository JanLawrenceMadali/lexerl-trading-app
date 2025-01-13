<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3'
import { Edit, Loader2 } from 'lucide-vue-next';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Button } from '@/Components/ui/button';
import InputError from '@/Components/InputError.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog';
import Swal from 'sweetalert2';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';

const props = defineProps({
    subcategories: Object,
    categories: Object,
    routing: String
})

const emit = defineEmits(['update-subcategory']);

const form = useForm({
    name: props.subcategories.name,
    category_id: String(props.subcategories.category_id)
})

watch(() => props.subcategories, (newSubCategories) => {
    form.name = newSubCategories.name,
        form.category_id = String(newSubCategories.category_id)
}, { immediate: true })

const isOpen = ref(false);

const closeSheet = () => {
    isOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.patch(route('subcategories.update', props.subcategories), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            emit('update-subcategory', response.props.subcategories);
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
        form.name &&
        form.category_id

    return !commonFieldsValid || form.processing;
});
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
                <DialogTitle>Edit sub category</DialogTitle>
                <DialogDescription>
                    Make changes to the sub category here. Click save changes when you're done.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit">
                <div class="grid gap-2 my-4">
                    <Label for="name" class="after:content-['*'] after:ml-0.5 after:text-red-500">Name</Label>
                    <Input v-model="form.name" id="name" type="text" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label class="after:content-['*'] after:ml-0.5 after:text-red-500">Category</Label>
                    <Select v-model="form.category_id">
                        <SelectTrigger>
                            <SelectValue placeholder="Select a category" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem v-for="category in categories" :key="category" :value="String(category.id)">
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
                        Save changes
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
