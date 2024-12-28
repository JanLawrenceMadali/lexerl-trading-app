<script setup>
import { Button } from '@/Components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { DatabaseBackup, Download, RotateCcw, Trash, Trash2 } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
    backups: { type: Array, required: true },
});

const isLoading = ref(false);
const isCleaning = ref(false);
const isDownloading = ref(false);
const isUploading = ref(false);

const form = useForm({});

const createBackup = () => {
    isLoading.value = true;
    form.post(route('backup.manual'), {
        onSuccess: (response) => {
            Swal.fire({
                title: "Success!",
                text: response.props.flash.success,
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

const downloadBackup = (backup) => {
    isDownloading.value = true;

    axios.post(route('backup.download', { file: backup }), {}, {
        responseType: 'blob'
    }).then(response => {
        const zipFileName = backup.replace(/\.[^/.]+$/, "") + ".zip"; // Replace extension with .zip
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', zipFileName); // Set the download attribute with the ZIP filename
        document.body.appendChild(link);
        link.click();
        link.parentNode.removeChild(link);

        Swal.fire({
            title: "Success!",
            text: `The backup (${zipFileName}) has been downloaded.`,
            iconHtml: '<img src="/assets/icons/Success.png">',
            confirmButtonColor: "#1B1212",
        });
    }).catch(error => {
        Swal.fire('Error', 'Failed to download the backup.', 'error');
    }).finally(() => {
        isDownloading.value = false;
    });
};

const deleteBackup = (backup) => {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will permanently delete the backup!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#C00F0C',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('backup.destroy', { file: backup }), {
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
                    Swal.fire('Error', 'Failed to delete backup.', 'error');
                },
                onFinish: () => {
                    isCleaning.value = false;
                },
            });
        }
    });
};

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
    <div class="flex items-center justify-between">
        <div class="flex items-center justify-end gap-2">
            <!-- Backup Database Button -->
            <Button size="sm" variant="outline" class="gap-1 h-7" @click="createBackup" :disabled="isLoading">
                <DatabaseBackup class="h-3.5 w-3.5" />
                <span class="whitespace-nowrap">
                    {{ isLoading ? 'Backing up...' : 'Backup Database' }}
                </span>
            </Button>
            <!-- Clear All Backups Button -->
            <Button size="sm" variant="outline" class="gap-1 h-7" @click="cleanAllBackups" :disabled="isCleaning">
                <Trash2 class="h-3.5 w-3.5" />
                <span class="whitespace-nowrap">
                    {{ isCleaning ? 'Clearing...' : 'Clear All Backups' }}
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
                    <TableCell class="text-right">
                        <Button size="sm" title="download" variant="outline" class="h-7"
                            @click="downloadBackup(backup)">
                            <Download class="h-3.5 w-3.5" />
                        </Button>
                        <Button size="sm" title="delete" variant="outline"
                            class="ml-2 text-red-500 hover:text-red-500 h-7" @click="deleteBackup(backup)">
                            <Trash2 class="h-3.5 w-3.5" />
                        </Button>
                        <!-- restore database -->
                        <Button size="sm" title="restore" variant="outline" class="ml-2 h-7"
                            @click="restoreBackup(backup)">
                            <span class="whitespace-nowrap">
                                <RotateCcw class="h-3.5 w-3.5" />
                            </span>
                        </Button>
                    </TableCell>
                </TableRow>
                <TableRow v-if="!backups.length">
                    <TableCell class="font-medium">No backups available</TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
