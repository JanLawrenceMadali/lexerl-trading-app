<script setup>
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import { ref } from 'vue';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    chartData: Object,
});

const options = ref({
    responsive: true,
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value) => {
                    return '₱' + value;
                }
            }
        },
    },
    plugins: {
        legend: {
            display: false
        }
    }
});

</script>
<template>
    <div>
        <Bar :data="chartData" :options="options" />
    </div>
</template>