<script setup>
import { ref } from 'vue';
import { Plus } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3'
import { Loader2 } from 'lucide-vue-next';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Button } from '@/Components/ui/button';
import InputError from '@/Components/InputError.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select/index';
import Swal from 'sweetalert2';

const props = defineProps({
    categories: Object
})

const form = useForm({
    name: null,
    category_id: null,
})

const isOpen = ref(false);

const closeSheet = () => {
    isOpen.value = false;
};

const submit = () => {
    form.post(route('subcategory.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            form.reset();
            closeSheet();
            form.get(route('purchase-in'))

            Swal.fire({
                title: "Created!",
                text: "Sub Category successfully created!",
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
            <Button variant="outline" size="sm" class="m-2">
                <Plus class="mr-1 size-4" />
                Add new supplier
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>Create new Sub Category</DialogTitle>
                <DialogDescription>
                    Enter your Sub Category and select which Category. Click save when you're done.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit">
                <div class="grid gap-4 py-4">
                    <div class="grid items-center grid-cols-4 gap-4">
                        <Label for="name" class="text-right">Name</Label>
                        <Input v-model="form.name" id="name" class="col-span-3" />
                        <InputError class="col-span-4 text-right" :message="form.errors.name" />
                    </div>
                    <div class="grid items-center grid-cols-4 gap-4">
                        <Label class="text-right">Category</Label>
                        <Select v-model="form.category_id">
                            <SelectTrigger class="col-span-3">
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
                        <InputError class="col-span-4 text-right" :message="form.errors.category_id" />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="secondary" type="submit">
                        <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                        Submit
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
