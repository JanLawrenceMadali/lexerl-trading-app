<script setup>
import { ref, computed, watch } from 'vue';
import { Input } from './ui/input';

const props = defineProps({
    modelValue: {
        type: [Number, String],
        default: 0
    }
});

const emit = defineEmits(['update:modelValue']);

const inputValue = ref(formatNumber(props.modelValue));

const formattedValue = computed(() => {
    return formatNumber(props.modelValue);
});

watch(() => props.modelValue, (newValue) => {
    if (newValue !== parseFloat(inputValue.value.replace(/[^\d.]/g, ''))) {
        inputValue.value = formatNumber(newValue);
    }
});

function formatNumber(num) {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num);
}

function updateValue(event) {
    let value = event.target.value;

    // Allow the user to type freely
    inputValue.value = value;

    // Remove non-numeric characters for parsing
    const numericValue = parseFloat(value.replace(/[^\d.]/g, ''));

    if (!isNaN(numericValue)) {
        emit('update:modelValue', numericValue);
    } else {
        emit('update:modelValue', 0);
    }
}

function onBlur() {
    inputValue.value = formattedValue.value;
}
</script>

<template>
    <Input v-model="inputValue" @input="updateValue" @blur="onBlur" type="text" :class="$attrs.class" />
</template>
