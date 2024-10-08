<template>
    <div>
        <ul class="mt-1 text-sm">
            <li v-for="(message, index) in messages" :key="index">
                {{ message }}
            </li>
        </ul>
    </div>
</template>

<script setup>
defineProps({
    messages: {
        type: Array,
        required: true
    }
})
</script>