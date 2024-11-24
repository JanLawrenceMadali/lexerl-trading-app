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
import Swal from 'sweetalert2';

const props = defineProps({
    route: String
});

const form = useForm({
    name: null,
    email: null,
    address1: null,
    address2: null,
    contact_person: null,
    contact_number: null,
})

const isOpen = ref(false);

const closeSheet = () => {
    isOpen.value = false;
};

const submit = () => {
    form.post(route('supplier.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            form.reset();
            closeSheet();
            if (props.route === 'purchase-in') {
                form.get(route('purchase-in'))
            } else {
                form.get(route('suppliers'))
            }

            Swal.fire({
                title: "Success!",
                text: response.props.flash.message,
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
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Create new supplier</DialogTitle>
                <DialogDescription>
                    Enter the supplier details. Click save when you're done.
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
                    <Label for="address2">Address2</Label>
                    <Input v-model="form.address2" id="address2" type="text" />
                    <InputError :message="form.errors.address2" />
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
