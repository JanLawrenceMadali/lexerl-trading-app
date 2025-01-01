<script setup>
import { ref } from 'vue';
import { Plus, Loader2 } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3'
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Button } from '@/Components/ui/button';
import InputError from '@/Components/InputError.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog';
import Swal from 'sweetalert2';

const props = defineProps({
    routing: String
});

const emit = defineEmits(['create-supplier']);

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
    form.post(route('suppliers.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            emit('create-supplier', response.props.suppliers);
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
                Add new supplier
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Add new supplier</DialogTitle>
                <DialogDescription>
                    Fill in the details of the new supplier. Click submit when you're done.
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
                        Submit
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
