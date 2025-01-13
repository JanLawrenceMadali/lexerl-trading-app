<script setup>
import { Button } from '@/Components/ui/button';
import { Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import Swal from 'sweetalert2';
import { useForm } from '@inertiajs/vue3';

const isCleaning = ref(false);

const form = useForm({});

const cleanAllBackups = () => {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will permanently delete all backups!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#C00F0C',
        cancelButtonColor: '#1B1212',
        confirmButtonText: 'Yes',
    }).then((result) => {
        if (result.isConfirmed) {
            isCleaning.value = true;

            form.delete(route('backup.clean-all'), {
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
                    isCleaning.value = false;
                },
            });
        }
    });
};
</script>

<template>
    <Button size="sm" variant="outline" class="gap-1 bg-red-500 h-7 !text-white hover:bg-red-600"
        @click="cleanAllBackups" :disabled="isCleaning">
        <Trash2 class="h-3.5 w-3.5" />
        <span class="whitespace-nowrap">
            {{ isCleaning ? 'Deleting...' : 'Delete All Backups' }}
        </span>
    </Button>
</template>