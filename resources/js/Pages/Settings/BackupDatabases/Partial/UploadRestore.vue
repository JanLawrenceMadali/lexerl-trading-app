<script setup>
import { Button } from '@/Components/ui/button'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/Components/ui/dialog'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Loader2, Upload } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import Swal from 'sweetalert2';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const isUploading = ref(false);
const selectedFile = ref(null);
const isOpen = ref(false);

const form = useForm({
    backup: null
})

const closeSheet = () => {
    form.reset();
    form.clearErrors();
    isOpen.value = false;
    isUploading.value = false;
    selectedFile.value = null;
}

const uploadAndRestoreBackup = () => {
    isUploading.value = true;

    form.backup = selectedFile.value

    form.post(route('backup.upload-restore'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
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
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button size="sm" variant="outline" class="gap-1 bg-green-500 h-7 !text-white hover:bg-green-600">
                <Upload class="h-3.5 w-3.5" />
                Upload & Restore
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Upload & Restore</DialogTitle>
                <DialogDescription>
                    Upload a backup file to restore the database.
                </DialogDescription>
            </DialogHeader>
            <div class="flex items-center justify-center w-full">
                <Label for="dropzone-file"
                    class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <Upload class="w-10 h-10 mb-3 text-gray-400" />
                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                            <span class="font-semibold">Click to upload</span>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">File must have .sqlite extension</p>
                    </div>
                    <Input id="dropzone-file" type="file" accept=".sqlite"
                        @change="(e) => selectedFile = e.target.files[0]" class="hidden" />
                    <p class="text-base text-gray-500 dark:text-gray-400">
                        {{ selectedFile ? selectedFile.name + ' (' + (selectedFile.size / 1024).toFixed(2) + ' KB)' :
                            'No file selected' }}
                    </p>
                    <InputError class="mt-2" :message="form.errors.backup" />
                </Label>
            </div>
            <DialogFooter>
                <Button variant="outline" type="button"
                    class="gap-1 h-7 text-white bg-[#C00F0C] hover:bg-red-600 hover:text-white" @click="closeSheet">
                    Cancel
                </Button>
                <Button size="sm" class="gap-1 h-7" type="submit" :disabled="isUploading || !selectedFile"
                    @click="uploadAndRestoreBackup">
                    <Loader2 v-if="isUploading" class="w-4 h-4 mr-2 animate-spin" />
                    Restore
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>