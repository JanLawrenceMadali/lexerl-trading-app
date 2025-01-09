<script setup>
import { Button } from '@/Components/ui/button';
import axios from 'axios';
import { Download } from 'lucide-vue-next';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    backup: { type: String, required: true }
});

const isDownloading = ref(false);

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

</script>

<template>
    <Button size="sm" title="download" variant="outline" class="text-blue-500 h-7 hover:text-blue-600" @click="downloadBackup(backup)">
        <Download class="h-3.5 w-3.5" />
    </Button>
</template>