<script setup>
import { Button } from '@/Components/ui/button';
import { History } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref } from 'vue';

const props = defineProps({
    backup: { type: String, required: true }
});

const form = useForm({});

const isUploading = ref(false);

const restoreBackup = (backup) => {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will overwrite the current database with the selected backup.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#C00F0C',
        cancelButtonColor: '#1B1212',
        confirmButtonText: 'Yes, restore it!',
    }).then((result) => {
        if (result.isConfirmed) {
            isUploading.value = true;

            form.post(route('backup.restore', { backup_file: backup }), {
                onSuccess: (response) => {
                    if (response.props.flash.success) {
                        Swal.fire({
                            title: "Success!",
                            text: response.props.flash.success,
                            iconHtml: '<img src="/assets/icons/Success.png">',
                            confirmButtonColor: "#1B1212",
                        });
                    } else if (response.props.flash.error) {
                        Swal.fire('Error', response.props.flash.error, 'error');
                    }
                },
                onError: (errors) => {
                    Swal.fire('Error', 'Failed to restore backup.', 'error');
                },
                onFinish: () => {
                    isUploading.value = false;
                },
            });
        }
    });
};
</script>
<template>
    <Button size="sm" title="restore" variant="outline" class="text-green-500 h-7 hover:text-green-600" @click="restoreBackup(backup)">
        <span class="whitespace-nowrap">
            <History class="h-3.5 w-3.5" />
        </span>
    </Button>
</template>