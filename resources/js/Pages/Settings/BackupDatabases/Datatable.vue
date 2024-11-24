<script setup>
import { Button } from '@/Components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { DatabaseBackup, Download, RotateCcw, Trash } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Input } from '@/Components/ui/input';

const props = defineProps({
    backups: { type: Array, required: true },
});

const isLoading = ref(false);
const isCleaning = ref(false);
const isDownloading = ref(false);
const isUploading = ref(false);
const selectedFile = ref(null);

const form = useForm({});

// Create a new backup
const createBackup = () => {
    isLoading.value = true;
    form.post(route('backup.manual'), {
        onSuccess: () => {
            Swal.fire({
                title: "Success!",
                text: "Backup successfully created!",
                iconHtml: '<img src="/assets/icons/Success.png">',
                confirmButtonColor: "#1B1212",
            });
        },
        onError: (errors) => {
            Swal.fire('Error', 'Failed to create backup.', 'error');
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

// Download the latest backup
const downloadLatest = () => {
    if (!props.backups.length) {
        alert('No backups available to download.');
        return;
    }

    const latestBackup = props.backups[props.backups.length - 1]; // Get the latest backup

    isDownloading.value = true;

    axios.post(route('backup.download', { file: latestBackup }), {}, {
        responseType: 'blob'
    }).then(response => {
        const zipFileName = latestBackup.replace(/\.[^/.]+$/, "") + ".zip"; // Replace extension with .zip
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', zipFileName); // Set the download attribute with the ZIP filename
        document.body.appendChild(link);
        link.click();
        link.parentNode.removeChild(link);

        Swal.fire({
            title: "Success!",
            text: `The latest backup (${zipFileName}) has been downloaded.`,
            iconHtml: '<img src="/assets/icons/Success.png">',
            confirmButtonColor: "#1B1212",
        });
    }).catch(error => {
        Swal.fire('Error', 'Failed to download the latest backup.', 'error');
    }).finally(() => {
        isDownloading.value = false;
    });
};

// Clear all the backup databases
const cleanAllBackups = () => {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will permanently delete all backups!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#C00F0C',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete them!',
    }).then((result) => {
        if (result.isConfirmed) {
            isCleaning.value = true;

            // Use Inertia to send a DELETE request
            form.delete(route('backup.clean-all'), {
                onSuccess: () => {
                    Swal.fire({
                        title: "Success!",
                        text: "All backups have been deleted!",
                        iconHtml: '<img src="/assets/icons/Success.png">',
                        confirmButtonColor: "#1B1212",
                    });
                },
                onError: (errors) => {
                    Swal.fire('Error', 'Failed to delete backups.', 'error');
                },
                onFinish: () => {
                    isCleaning.value = false;
                },
            });
        }
    });
};

// Restore database
const uploadAndRestore = () => {
    if (!selectedFile.value) {
        Swal.fire('Error', 'Please select a file to restore.', 'error');
        return;
    }

    Swal.fire({
        title: 'Are you sure?',
        text: 'This will overwrite the current database with the uploaded file.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#C00F0C',
        cancelButtonColor: '#1B1212',
        confirmButtonText: 'Yes, restore it!',
    }).then((result) => {
        if (result.isConfirmed) {
            isUploading.value = true;

            const formData = new FormData();
            formData.append('backup_file', selectedFile.value);

            axios.post(route('backup.restore'), formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            })
                .then(() => {
                    Swal.fire('Success', 'Database restored successfully!', 'success');
                })
                .catch((error) => {
                    Swal.fire('Error', error.response.data.message || 'Failed to restore database.', 'error');
                })
                .finally(() => {
                    isUploading.value = false;
                    // reset the file input
                    selectedFile.value = null;
                });
        }
    });
};
</script>

<template>
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <Input type="file" accept=".sqlite" @change="(e) => selectedFile = e.target.files[0]"
                class="p-2 border rounded" />
            <Button size="sm" variant="outline" class="gap-1 mt-2 h-7" :disabled="isUploading"
                @click="uploadAndRestore">
                <span v-if="isUploading">Restoring...</span>
                <span v-else>Upload & Restore</span>
            </Button>
        </div>
        <div class="flex items-center justify-end gap-2">
            <!-- Backup Database Button -->
            <Button size="sm" variant="outline" class="gap-1 h-7" @click="createBackup" :disabled="isLoading">
                <DatabaseBackup class="h-3.5 w-3.5" />
                <span class="whitespace-nowrap">
                    {{ isLoading ? 'Backing up...' : 'Backup Database' }}
                </span>
            </Button>

            <!-- Download Latest Backup Button -->
            <Button size="sm" class="gap-1 h-7" @click="downloadLatest" :disabled="isDownloading || !backups.length">
                <Download class="h-3.5 w-3.5" />
                <span v-if="!isDownloading">Download Latest</span>
                <span v-else>Downloading...</span>
                <span v-if="isDownloading" class="ml-2 loader"></span>
            </Button>

            <Button size="sm" variant="destructive" class="gap-1 h-7" :disabled="isCleaning || !backups.length"
                @click="cleanAllBackups">
                <Trash class="h-3.5 w-3.5" />
                <span class="whitespace-nowrap">
                    {{ isCleaning ? 'Cleaning...' : 'Clean All Backups' }}
                </span>
            </Button>
        </div>
    </div>

    <div class="mt-4 border rounded-md">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Backups</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="backup in backups" :key="backup">
                    <TableCell class="font-medium">{{ backup }}</TableCell>
                </TableRow>
                <TableRow v-if="!backups.length">
                    <TableCell class="font-medium">No backups available</TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
