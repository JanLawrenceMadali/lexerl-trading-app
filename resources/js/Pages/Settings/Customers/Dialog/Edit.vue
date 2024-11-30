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
    routing: String
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
    form.patch(route('customers.update', data.value), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            form.reset();
            closeSheet();
            if (props.routing) {
                form.get(route(props.routing))
            } else {
                form.get(route('customers'))
            }

            Swal.fire({
                title: "Success!",
                text: response.props.flash.success,
                iconHtml: '<img src="/assets/icons/Success.png">',
                confirmButtonColor: "#1B1212",
            });
        },
        onError: (error) => {
            // console.log(error);
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
                <DialogTitle>Edit customer</DialogTitle>
                <DialogDescription>
                    Make changes to the customer here. Click save changes when you're done.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit">
                <div class="grid gap-2 my-4">
                    <Label for="name">Name</Label>
                    <Input v-model="form.name" id="name" type="text" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label for="email">Email</Label>
                    <Input v-model="form.email" id="email" type="email" />
                    <InputError :message="form.errors.email" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label for="contact_person">Contact Person</Label>
                    <Input v-model="form.contact_person" id="contact_person" type="text" />
                    <InputError :message="form.errors.contact_person" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label for="contact_number">Contact Number</Label>
                    <Input v-model="form.contact_number" id="contact_number" type="text" />
                    <InputError :message="form.errors.contact_number" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label for="address1">Address1</Label>
                    <Input v-model="form.address1" id="address1" type="text" />
                    <InputError :message="form.errors.address1" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label for="address2">Address2 <span class="text-xs text-muted-foreground">(Optional)</span></Label>
                    <Input v-model="form.address2" id="addres2s" type="text" />
                    <InputError :message="form.errors.address2" />
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
