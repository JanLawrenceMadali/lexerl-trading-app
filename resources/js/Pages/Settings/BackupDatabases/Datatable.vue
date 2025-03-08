<script setup>
import { computed } from 'vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import UploadRestore from './Partial/UploadRestore.vue';
import ManualBackup from './Partial/ManualBackup.vue';
import CleanAllBackup from './Partial/CleanAllBackup.vue';
import DownloadDatabase from './Partial/DownloadDatabase.vue';
import DeleteDatabase from './Partial/DeleteDatabase.vue';
import RestoreDatabase from './Partial/RestoreDatabase.vue';
import PurgeTransaction from './Partial/PurgeTransaction.vue';

const props = defineProps({
    backups: { type: Array, required: true }, // Now includes "name" & "modified"
});

// Function to format the date and add relative time
const formatDate = (timestamp) => {
    if (!timestamp) return '';

    const date = new Date(timestamp * 1000); // Convert Unix timestamp to milliseconds
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000); // Difference in seconds
    const diffInDays = Math.floor(diffInSeconds / 86400);
    const diffInMonths = Math.floor(diffInDays / 30);
    const diffInYears = Math.floor(diffInDays / 365);

    // Format full date
    const formattedDate = new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    }).format(date);

    // Calculate relative time
    let relativeTime;
    if (diffInSeconds < 60) {
        relativeTime = 'Just now';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        relativeTime = `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        relativeTime = `${hours} hour${hours > 1 ? 's' : ''} ago`;
    } else if (diffInDays === 1) {
        relativeTime = 'Yesterday';
    } else if (diffInDays < 30) {
        relativeTime = `${diffInDays} days ago`;
    } else if (diffInMonths < 12) {
        relativeTime = `${diffInMonths} month${diffInMonths > 1 ? 's' : ''} ago`;
    } else {
        relativeTime = ''; // If it's older than a year, just show the full date
    }

    return relativeTime ? `${formattedDate} (${relativeTime})` : formattedDate;
};

// Computed backups with formatted dates
const formattedBackups = computed(() =>
    props.backups.map(backup => ({
        name: backup.name,
        date: formatDate(backup.modified) // Use modification time
    }))
);
</script>

<template>
    <div class="flex items-center justify-end gap-2">
        <UploadRestore />
        <ManualBackup />
        <CleanAllBackup />
        <PurgeTransaction />
    </div>

    <div class="mt-4 border rounded-md">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Backups</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="(backup, index) in formattedBackups" :key="index">
                    <TableCell class="font-medium">
                        {{ backup.name }}
                        <span v-if="backup.date" class="ml-2 text-xs text-gray-500">
                            {{ backup.date }}
                        </span>
                    </TableCell>
                    <TableCell class="space-x-2 text-right">
                        <RestoreDatabase :backup="backup.name" />
                        <DownloadDatabase :backup="backup.name" />
                        <DeleteDatabase :backup="backup.name" />
                    </TableCell>
                </TableRow>
                <TableRow v-if="!backups.length">
                    <TableCell colspan="3" class="font-medium text-center">No backups available</TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
