<script setup>
import { Button } from '@/Components/ui/button';
import { useForm } from '@inertiajs/vue3';
import { DatabaseBackup } from 'lucide-vue-next';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const form = useForm({});

const isLoading = ref(false);

const createBackup = () => {
    isLoading.value = true;
    form.post(route('backup.manual'), {
        onSuccess: (response) => {
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
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};
</script>
<template>
    <Button size="sm" variant="outline" class="gap-1 !text-white bg-blue-500 hover:bg-blue-600 h-7"
        @click="createBackup" :disabled="isLoading">
        <DatabaseBackup class="h-3.5 w-3.5" />
        <span class="whitespace-nowrap">
            {{ isLoading ? 'Backing up...' : 'Backup Database' }}
        </span>
    </Button>
</template>