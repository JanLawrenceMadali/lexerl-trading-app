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
    suppliers: Object
});

const emit = defineEmits(['update-supplier']);

const form = useForm({
    name: props.suppliers.name,
    email: props.suppliers.email,
    address1: props.suppliers.address1,
    address2: props.suppliers.address2,
    contact_person: props.suppliers.contact_person,
    contact_number: props.suppliers.contact_number,
});

watch(() => props.suppliers, (newSuppliers) => {
    form.name = newSuppliers.name,
        form.email = newSuppliers.email,
        form.address1 = newSuppliers.address1,
        form.address2 = newSuppliers.address2,
        form.contact_person = newSuppliers.contact_person,
        form.contact_number = newSuppliers.contact_number
}, { immediate: true })

const isOpen = ref(false);

const closeSheet = () => {
    isOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.patch(route('suppliers.update', props.suppliers), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            emit('update-supplier', response.props.suppliers);
            closeSheet();
            if (response.props.flash.success) {
                Swal.fire({
                    title: "Success!",
                    text: response.props.flash.success,
                    iconHtml: '<img src="/assets/icons/Success.png">',
                    confirmButtonColor: "#1B1212",
                });
            } else if (response.props.flash.error) {
                Swal.fire({
                    title: "Oops! Something went wrong",
                    text: response.props.flash.error,
                    icon: 'error',
                    confirmButtonColor: "#1B1212",
                });
            }

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
                <DialogTitle>Edit supplier</DialogTitle>
                <DialogDescription>
                    Make changes to the supplier here. Click save changes when you're done.
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
                    <Input v-model="form.address2" id="address2" type="text" />
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
