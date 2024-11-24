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

const props = defineProps({
    customers: Object,
})

const data = ref(props.customers)

const form = useForm({
    name: data.value.name,
    email: data.value.email,
    address1: data.value.address1,
    address2: data.value.address2,
    contact_person: data.value.contact_person,
    contact_number: data.value.contact_number,
})

const isOpen = ref(false);

const closeSheet = () => {
    isOpen.value = false;
};

const submit = () => {
    form.patch(route('supplier.update', data.value), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            form.reset();
            closeSheet();
            form.get(route('settings.suppliers'))

            Swal.fire({
                title: "Success!",
                text: "Supplier successfully updated!",
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
        <DialogContent class="sm:max-w-[1200px]">
            <DialogHeader>
                <DialogTitle>Create new supplier</DialogTitle>
                <DialogDescription>
                    Enter the supplier details. Click save when you're done.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit">
                <div class="grid grid-cols-2 gap-4 py-4">
                    <div class="grid items-center grid-cols-5 gap-4">
                        <Label for="name" class="col-span-2 text-right">Name</Label>
                        <Input v-model="form.name" id="name" type="text" class="col-span-3" />
                        <InputError class="col-span-5 text-right" :message="form.errors.name" />
                    </div>
                    <div class="grid items-center grid-cols-5 gap-4">
                        <Label for="contact_person" class="col-span-2 text-right">Contact Person</Label>
                        <Input v-model="form.contact_person" id="contact_person" type="text" class="col-span-3" />
                        <InputError class="col-span-5 text-right" :message="form.errors.contact_person" />
                    </div>
                    <div class="grid items-center grid-cols-5 gap-4">
                        <Label for="email" class="col-span-2 text-right">Email</Label>
                        <Input v-model="form.email" id="email" type="email" class="col-span-3" />
                        <InputError class="col-span-5 text-right" :message="form.errors.email" />
                    </div>
                    <div class="grid items-center grid-cols-5 gap-4">
                        <Label for="contact_number" class="col-span-2 text-right">Contact Number</Label>
                        <Input v-model="form.contact_number" id="contact_number" type="text" class="col-span-3" />
                        <InputError class="col-span-5 text-right" :message="form.errors.contact_number" />
                    </div>
                    <div class="grid items-center grid-cols-4 gap-4">
                        <Label for="address1" class="text-right">Address1</Label>
                        <Input v-model="form.address1" id="address1" type="text" class="col-span-3" />
                        <InputError class="col-span-4 text-right" :message="form.errors.address1" />
                    </div>
                    <div class="grid items-center grid-cols-4 gap-4">
                        <Label for="address2" class="text-right">Address2</Label>
                        <Input v-model="form.address2" id="address2" type="text" class="col-span-3" />
                        <InputError class="col-span-4 text-right" :message="form.errors.address2" />
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
