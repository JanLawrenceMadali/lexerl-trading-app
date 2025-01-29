<script setup>
const props = defineProps({
    value: Number
})

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};
</script>

<template>
    <div :title="formatCurrency(value)" class="text-3xl text-[#772E25] truncate">
        <sup>PHP</sup> <span class="font-semibold">{{ formatCurrency(value) }}</span>
    </div>
</template>
