<script setup>
import { ref, watch } from 'vue';
import { Plus, Loader2 } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3'
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Button } from '@/Components/ui/button';
import InputError from '@/Components/InputError.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog';
import Swal from 'sweetalert2';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';

const props = defineProps({
    roles: Object
});

const emit = defineEmits(['create-user']);

const form = useForm({
    username: null,
    email: null,
    role_id: null,
    password: null,
    password_confirmation: null
})

const isOpen = ref(false);

const closeSheet = () => {
    isOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.post(route('users.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            emit('create-user', response.props.users);
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
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm" class="gap-1 h-7">
                <Plus class="mr-1 size-4" />
                Add new user
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Add new user</DialogTitle>
                <DialogDescription>
                    Fill in the form below to add a new user. Click submit when you're done.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit" novalidate>
                <div class="grid gap-2 my-4">
                    <Label class="after:content-['*'] after:ml-0.5 after:text-red-500">Role</Label>
                    <Select v-model="form.role_id">
                        <SelectTrigger>
                            <SelectValue placeholder="Select a roles" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem v-for="role in roles" :key="role" :value="String(role.id)">
                                    {{ role.name }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.role_id" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label for="username" class="after:content-['*'] after:ml-0.5 after:text-red-500">Username</Label>
                    <Input id="username" type="text" v-model="form.username" autofocus autocomplete="username" />
                    <InputError :message="form.errors.username" />
                </div>
                <div class="grid gap-2 mb-4">
                    <Label for="email" class="after:content-['*'] after:ml-0.5 after:text-red-500">Email</Label>
                    <Input id="email" type="email" v-model="form.email" autocomplete="email" />
                    <InputError :message="form.errors.email" />
                </div>
                <div class="grid gap-2 mb-4">
                    <Label for="password" class="after:content-['*'] after:ml-0.5 after:text-red-500">Password</Label>
                    <Input id="password" type="password" v-model="form.password" autocomplete="password" />
                    <InputError :message="form.errors.password" />
                </div>
                <div class="grid gap-2 mb-4">
                    <Label for="password_confirmation"
                        class="after:content-['*'] after:ml-0.5 after:text-red-500">Confirm Password</Label>
                    <Input id="password_confirmation" type="password" v-model="form.password_confirmation"
                        autocomplete="password_confirmation" />
                    <InputError :message="form.errors.password_confirmation" />
                </div>
                <DialogFooter>
                    <Button variant="outline" type="button" class="gap-1 h-7" @click="closeSheet">
                        Cancel
                    </Button>
                    <Button type="submit" class="gap-1 h-7" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                        Submit
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
