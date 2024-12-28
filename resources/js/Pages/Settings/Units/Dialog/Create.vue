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
    route: String,
});

const emit = defineEmits(['create-unit']);

const form = useForm({
    name: null,
    abbreviation: null
})

const isOpen = ref(false);

const closeSheet = () => {
    isOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.post(route('units.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            emit('create-unit', response.props.units);
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
            <Button variant="outline" size="sm" class="m-2">
                <Plus class="mr-1 size-4" />
                Add new units
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Add new units</DialogTitle>
                <DialogDescription>
                    Fill in the form below to add a new units. Click submit when you're done.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit">
                <div class="grid gap-2 my-4">
                    <Label for="name" class="after:content-['*'] after:ml-0.5 after:text-red-500">Name</Label>
                    <Input v-model="form.name" id="name" type="text" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label for="abbreviation" class="after:content-['*'] after:ml-0.5 after:text-red-500">Abbreviation</Label>
                    <Input v-model="form.abbreviation" id="abbreviation" type="text" />
                    <InputError :message="form.errors.abbreviation" />
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
