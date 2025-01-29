<script setup>
const props = defineProps({
    value: String
})

</script>

<template>
    <div :title="value" class="text-3xl text-[#772E25] truncate">
        <sup>PHP</sup> <span class="font-semibold">{{ value }}</span>
    </div>
</template>
