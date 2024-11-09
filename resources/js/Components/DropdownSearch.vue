<script setup>
import { ref, computed, onMounted, onUnmounted, Transition } from 'vue';
import { Input } from './ui/input';
import { XIcon } from 'lucide-vue-next';

const props = defineProps({
    items: {
        type: Array,
        required: true
    },
    labelKey: {
        type: String,
        default: 'name'
    },
    valueKey: {
        type: String,
        default: 'id'
    },
    disabled: {
        type: Boolean,
        default: false
    },
    placeholder: {
        type: String,
        default: 'Search...'
    },
    hasError: {
        type: String,
        required: false
    }
});

const emit = defineEmits(['update:modelValue', 'select']);

const search = ref('');
const showDropdown = ref(false);
const isItemSelected = ref(false);

const filteredItems = computed(() => {
    return props.items.filter(item =>
        item[props.labelKey].toLowerCase().includes(search.value.toLowerCase())
    );
});

const selectItem = (item) => {
    search.value = item[props.labelKey];
    emit('update:modelValue', item[props.valueKey]);
    emit('select', item);
    showDropdown.value = false;
    isItemSelected.value = true;
};

const clearSelection = () => {
    search.value = '';
    emit('update:modelValue', null);
    isItemSelected.value = false;
};

const closeDropdown = (e) => {
    if (!e.target.closest('.dropdown-search')) {
        showDropdown.value = false;
        if (!isItemSelected.value) {
            clearSelection();
        }
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});

</script>

<template>
    <div class="relative col-span-3 dropdown-search">
        <div class="relative">
            <Input type="text" v-model="search" @focus="showDropdown = true" :placeholder="placeholder"
                :disabled="disabled" :class="{ 'border-red-600 focus-visible:ring-red-500': hasError }" />
            <button v-if="isItemSelected" @click="clearSelection"
                class="absolute transform -translate-y-1/2 right-3 top-1/2">
                <XIcon class="text-red-600 size-4" />
            </button>
        </div>
        <Transition enter-active-class="transition duration-150 ease-in" enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100">
            <div v-if="showDropdown"
                class="absolute z-10 w-full p-2 mt-1 overflow-auto bg-white border rounded-md shadow-lg max-h-60">
                <div v-if="filteredItems.length > 0">
                    <div v-for="item in filteredItems" :key="item[valueKey]" @click="selectItem(item)"
                        class="p-2 text-sm text-left rounded-md cursor-pointer hover:bg-slate-100">
                        {{ item[labelKey] }}
                    </div>
                </div>
                <div v-else class="p-2 text-sm text-center text-gray-500">
                    No items found
                </div>
            </div>
        </Transition>
    </div>
</template>
