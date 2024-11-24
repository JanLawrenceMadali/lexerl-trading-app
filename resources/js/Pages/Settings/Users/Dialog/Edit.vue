<script setup>
import { ref } from 'vue';
import { Edit, Plus } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3'
import { Loader2 } from 'lucide-vue-next';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Button } from '@/Components/ui/button';
import InputError from '@/Components/InputError.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog';
import Swal from 'sweetalert2';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';

const props = defineProps({
    users: Object,
    roles: Object,
});

const users = ref(props.users);

const form = useForm({
    username: users.value.username,
    email: users.value.email,
    role_id: String(users.value.role_id),
    password: null,
    password_confirmation: null
})

const isOpen = ref(false);

const closeSheet = () => {
    isOpen.value = false;
};

const submit = () => {
    form.post(route('users.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            form.reset();
            closeSheet();
            form.get(route('users'))

            Swal.fire({
                title: "Success!",
                text: response.props.flash.message,
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
                <DialogTitle>Edit user</DialogTitle>
                <DialogDescription>
                    Enter the user details. Click save when you're done.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit">
                <div class="grid gap-2 my-4">
                    <Label>Role</Label>
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
                    <Label for="username">Username</Label>
                    <Input id="username" type="text" v-model="form.username" autofocus autocomplete="username" />
                    <InputError :message="form.errors.username" />
                </div>
                <div class="grid gap-2 mb-4">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" v-model="form.email" autocomplete="email" />
                    <InputError :message="form.errors.email" />
                </div>
                <div class="grid gap-2 mb-4">
                    <Label for="password">Password</Label>
                    <Input id="password" type="password" v-model="form.password" autocomplete="password" />
                    <InputError :message="form.errors.password" />
                </div>
                <div class="grid gap-2 mb-4">
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input id="password_confirmation" type="password" v-model="form.password_confirmation"
                        autocomplete="password_confirmation" />
                    <InputError :message="form.errors.password_confirmation" />
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
