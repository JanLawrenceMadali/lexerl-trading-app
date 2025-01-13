<script setup>
import { Button } from '@/Components/ui/button';
import { useForm } from '@inertiajs/vue3';
import { PackageX } from 'lucide-vue-next';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const isPurging = ref(false);
const form = useForm({});

const purge = () => {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This action will delete all Purchase In and Sales transactions. Make sure Are you sure you want to proceed?',
        iconHtml: '<img src="/assets/icons/Warning.png">',
        showCancelButton: true,
        confirmButtonColor: '#C00F0C',
        cancelButtonColor: '#1B1212',
        confirmButtonText: 'Yes',
    }).then((result) => {
        if (result.isConfirmed) {
            isPurging.value = true;
            form.delete(route('backup.purge'), {
                onSuccess: (response) => {
                    isPurging.value = false;
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
                    isPurging.value = false;
                }
            });
        }
    })
}

</script>

<template>
    <Button size="sm" variant="outline" class="gap-1 bg-orange-500 h-7 !text-white hover:bg-orange-600" @click="purge">
        <PackageX class="h-3.5 w-3.5" />
        <span class="whitespace-nowrap">
            {{ isPurging ? 'Purging...' : 'Purge Transactions' }}
        </span>
    </Button>
</template>