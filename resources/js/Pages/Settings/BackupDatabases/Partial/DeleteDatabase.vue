<script setup>
import { Button } from '@/Components/ui/button';
import { Trash2 } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref } from 'vue';

const props = defineProps({
    backup: { type: String, required: true }
});

const form = useForm({});

const isCleaning = ref(false);

const deleteBackup = (backup) => {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will permanently delete the backup!',
        iconHtml: '<img src="/assets/icons/Warning.png">',
        showCancelButton: true,
        confirmButtonColor: '#C00F0C',
        cancelButtonColor: '#1B1212',
        confirmButtonText: 'Yes',
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('backup.destroy', { file: backup }), {
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
    <Button size="sm" title="delete" variant="outline" class="text-red-500 hover:text-red-500 h-7"
        @click="deleteBackup(backup)">
        <Trash2 class="h-3.5 w-3.5" />
    </Button>
</template>