<script setup>
import { Button } from '@/Components/ui/button';
import { useForm } from '@inertiajs/vue3';
import { PackageX } from 'lucide-vue-next';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const isPurging = ref(false);
const form = useForm({
    password: ''
});

const purge = () => {
    Swal.fire({
        title: '<h2 class="custom-title">Purge All Transactions</h2>',
        html: `
            <p class="mb-4 custom-text">This action will delete all Purchase In and Sales transactions. Make sure backup is done before performing this task.</p>
            <input 
                type="password" 
                id="password" 
                class="w-64 p-2 mx-4 my-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                placeholder="Enter your password to confirm">
        `,
        iconHtml: '<img src="/assets/icons/Warning.png">',
        showCancelButton: true,
        confirmButtonColor: '#C00F0C',
        cancelButtonColor: '#1B1212',
        confirmButtonText: 'Yes',
        preConfirm: () => {
            const password = document.getElementById('password').value;
            if (!password) {
                Swal.showValidationMessage('Password is required');
                return false;
            }
            form.password = password;
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            isPurging.value = true;
            form.delete(route('backup.purge'), {
                preserveScroll: true,
                onSuccess: (page) => {
                    isPurging.value = false;
                    if (page.props.flash.success) {
                        Swal.fire({
                            text: page.props.flash.success,
                            iconHtml: '<img src="/assets/icons/Success.png">',
                            confirmButtonColor: "#1B1212",
                        });
                    } else {
                        Swal.fire({
                            text: page.props.flash.error,
                            icon: 'error',
                            confirmButtonColor: "#1B1212",
                        });
                    }
                },
                onError: (error) => {
                    isPurging.value = false;
                    Swal.fire({
                        text: error.password,
                        icon: 'error',
                        confirmButtonColor: "#1B1212",
                    });
                },
                onFinish: () => {
                    isPurging.value = false;
                }
            });
        }
    });
};
</script>

<template>
    <Button size="sm" variant="outline" class="gap-1 bg-orange-500 h-7 !text-white hover:bg-orange-600" @click="purge"
        :disabled="form.processing">
        <PackageX class="h-3.5 w-3.5" />
        <span class="whitespace-nowrap">
            {{ form.processing ? 'Purging...' : 'Purge Transactions' }}
        </span>
    </Button>
</template>

<style>
/* Optional: Add these styles to your global CSS to customize the SweetAlert dialog */
.swal2-popup {
    padding: 2rem;
}

.swal2-title {
    font-size: 1.5rem !important;
    padding: 0 !important;
}

.swal2-html-container {
    margin: 1rem 0 !important;
}

.swal2-confirm {
    padding: 0.5rem 1rem !important;
}

.swal2-cancel {
    padding: 0.5rem 1rem !important;
}
</style>