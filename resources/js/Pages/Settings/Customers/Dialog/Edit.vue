<script setup>
import { ref, watch } from 'vue';
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

const emit = defineEmits(['update-customer']);

const form = useForm({
    name: props.customers.name,
    email: props.customers.email,
    address1: props.customers.address1,
    address2: props.customers.address2,
    contact_person: props.customers.contact_person,
    contact_number: props.customers.contact_number,
})

watch(() => props.customers, (newCustomers) => {
    form.name = newCustomers.name,
        form.email = newCustomers.email,
        form.address1 = newCustomers.address1,
        form.address2 = newCustomers.address2,
        form.contact_person = newCustomers.contact_person,
        form.contact_number = newCustomers.contact_number
}, { immediate: true })

const isOpen = ref(false);

const closeSheet = () => {
    isOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.patch(route('customers.update', props.customers), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            emit('update-customer', response.props.customers);
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
                <DialogTitle>Edit customer</DialogTitle>
                <DialogDescription>
                    Make changes to the customer here. Click save changes when you're done.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit">
                <div class="grid gap-2 my-4">
                    <Label for="name" class="after:content-['*'] after:ml-0.5 after:text-red-500">Name</Label>
                    <Input v-model="form.name" id="name" type="text" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label for="email" class="after:content-['*'] after:ml-0.5 after:text-red-500">Email</Label>
                    <Input v-model="form.email" id="email" type="email" />
                    <InputError :message="form.errors.email" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label for="contact_person" class="after:content-['*'] after:ml-0.5 after:text-red-500">Contact Person</Label>
                    <Input v-model="form.contact_person" id="contact_person" type="text" />
                    <InputError :message="form.errors.contact_person" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label for="contact_number" class="after:content-['*'] after:ml-0.5 after:text-red-500">Contact Number</Label>
                    <Input v-model="form.contact_number" id="contact_number" type="text" />
                    <InputError :message="form.errors.contact_number" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label for="address1" class="after:content-['*'] after:ml-0.5 after:text-red-500">Address1</Label>
                    <Input v-model="form.address1" id="address1" type="text" />
                    <InputError :message="form.errors.address1" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label for="address2">Address2 <span class="text-xs text-muted-foreground">(Optional)</span></Label>
                    <Input v-model="form.address2" id="addres2s" type="text" />
                    <InputError :message="form.errors.address2" />
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
