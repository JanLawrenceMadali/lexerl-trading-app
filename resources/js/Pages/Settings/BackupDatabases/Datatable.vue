<script setup>
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import UploadRestore from './Partial/UploadRestore.vue';
import ManualBackup from './Partial/ManualBackup.vue';
import CleanAllBackup from './Partial/CleanAllBackup.vue';
import DownloadDatabase from './Partial/DownloadDatabase.vue';
import DeleteDatabase from './Partial/DeleteDatabase.vue';
import RestoreDatabase from './Partial/RestoreDatabase.vue';
import PurgeTransaction from './Partial/PurgeTransaction.vue';

const props = defineProps({
    backups: { type: Array, required: true },
});

</script>

<template>
    <div class="flex items-center justify-end gap-2">
        <!-- Upload Database Button -->
        <UploadRestore />
        <!-- Manual Backup Database Button -->
        <ManualBackup />
        <!-- Clear All Backups Button -->
        <CleanAllBackup />
        <!-- Purge Transactions -->
        <PurgeTransaction />
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
                    <TableCell class="space-x-2 text-right">
                        <!-- restore database -->
                        <RestoreDatabase :backup="backup" />
                        <!-- download database -->
                        <DownloadDatabase :backup="backup" />
                        <!-- delete database -->
                        <DeleteDatabase :backup="backup" />
                    </TableCell>
                </TableRow>
                <TableRow v-if="!backups.length">
                    <TableCell class="font-medium">No backups available</TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
