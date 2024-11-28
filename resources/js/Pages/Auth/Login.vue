<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { LogIn, Loader2 } from 'lucide-vue-next';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="username">Username</Label>
                <Input id="username" type="text" v-model="form.username" autofocus autocomplete="username"
                    placeholder="Username" />
                <InputError :message="form.errors.username" />
            </div>

            <div class="grid gap-2 mt-4">
                <Label for="password">Password</Label>
                <Input id="password" type="password" v-model="form.password" autocomplete="current-password" />
                <InputError :message="form.errors.password" />
            </div>

            <div class="block mt-4">
                <Label class="flex items-center space-x-2">
                    <Checkbox id="remember" v-model:checked="form.remember" />
                    <Label for="remember"
                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                        Stay signed in
                    </Label>
                </Label>
            </div>

            <div class="flex items-center justify-end my-4">
                <Button :class="['w-full', { 'opacity-25': form.processing }]" :disabled="form.processing">
                    <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                    <LogIn v-else class="w-4 h-4 mr-2" />
                    Sign In
                </Button>
            </div>

            <p class="text-sm text-center text-muted-foreground">Forgot password? Please contact your <span class="underline">Administrator</span>.</p>
        </form>

    </GuestLayout>
</template>
