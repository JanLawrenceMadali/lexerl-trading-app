<script setup>
import { ref } from 'vue';
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

const data = ref(props.subcategories)

const form = useForm({
    name: data.value.name,
    category_id: String(data.value.category_id)
})

const isOpen = ref(false);

const closeSheet = () => {
    isOpen.value = false;
};

const submit = () => {
    form.patch(route('subcategories.update', data.value), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            form.reset();
            closeSheet();
            if (props.routing) {
                form.get(route(props.routing));
            } else {
                form.get(route('subcategories'));
            }

            Swal.fire({
                title: "Success!",
                text: response.props.flash.success,
                iconHtml: '<img src="/assets/icons/Success.png">',
                confirmButtonColor: "#1B1212",
            });
        },
        onError: (error) => {
            console.log(error);
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
                <DialogTitle>Edit sub category</DialogTitle>
                <DialogDescription>
                    Make changes to the sub category here. Click save changes when you're done.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit">
                <div class="grid gap-2 my-4">
                    <Label for="name">Name</Label>
                    <Input v-model="form.name" id="name" type="text" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label>Category</Label>
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
                    <Button variant="secondary" type="submit">
                        <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                        Save changes
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
