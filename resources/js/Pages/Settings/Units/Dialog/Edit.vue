<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3'
import { Edit, Loader2 } from 'lucide-vue-next';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Button } from '@/Components/ui/button';
import InputError from '@/Components/InputError.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog';
import Swal from 'sweetalert2';

const props = defineProps({
    unit: Object,
})

const emit = defineEmits(['update-unit']);

const form = useForm({
    name: props.unit.name,
    abbreviation: props.unit.abbreviation
})

watch(() => props.unit, (newUnits) => {
    form.name = newUnits.name,
        form.abbreviation = newUnits.abbreviation
}, { immediate: true })

const isOpen = ref(false);

const closeSheet = () => {
    form.reset();
    form.clearErrors();
    isOpen.value = false;
};

const submit = () => {
    form.patch(route('units.update', props.unit), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            emit('update-unit', response.props.units);
            closeSheet();
            if (response.props.flash.success) {
                Swal.fire({
                    text: response.props.flash.success,
                    iconHtml: '<img src="/assets/icons/Success.png">',
                    confirmButtonColor: "#1B1212",
                });
            } else {
                Swal.fire({
                    text: response.props.flash.error,
                    icon: 'error',
                    confirmButtonColor: "#1B1212",
                });
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

const isSubmitDisabled = computed(() => {
    const commonFieldsValid =
        form.name &&
        form.abbreviation;

    return !commonFieldsValid || form.processing;
});
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
                <DialogTitle>Edit unit</DialogTitle>
                <DialogDescription>
                    Make changes to the unit here. Click save changes when you're done.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit">
                <div class="grid gap-2 my-4">
                    <Label for="name" class="after:content-['*'] after:ml-0.5 after:text-red-500">Name</Label>
                    <Input v-model="form.name" id="name" type="text" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid gap-2 my-4">
                    <Label for="abbreviation"
                        class="after:content-['*'] after:ml-0.5 after:text-red-500">Abbreviation</Label>
                    <Input v-model="form.abbreviation" id="abbreviation" type="text" />
                    <InputError :message="form.errors.abbreviation" />
                </div>
                <DialogFooter>
                    <Button variant="outline" type="button"
                        class="gap-1 h-7 text-white bg-[#C00F0C] hover:bg-red-600 hover:text-white" @click="closeSheet">
                        Cancel
                    </Button>
                    <Button type="submit" class="gap-1 h-7" :disabled="isSubmitDisabled">
                        <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                        Save changes
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
