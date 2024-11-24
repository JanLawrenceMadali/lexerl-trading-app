<script setup>
import { ref, computed, watch, watchEffect } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from './ui/popover';
import { Button } from './ui/button';
import { Check, ChevronDown } from 'lucide-vue-next';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from './ui/command';
import { cn } from '@/lib/utils';

const props = defineProps({
    items: {
        type: Array,
        required: true,
        default: () => [],
    },
    labelKey: { type: String, default: 'name' },
    valueKey: { type: String, default: 'id' },
    placeholder: { type: String, default: 'Select an item...' },
    modelValue: { required: false, default: null },
    hasError: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    class: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const localValue = ref(props.modelValue ?? null); // Ensure we default to null or a valid value

watch(() => props.modelValue, (newVal) => {
    localValue.value = newVal ?? null;
});

const open = ref(false);
const searchText = ref('');

// Function to handle the search input
const handleSearch = (query) => {
    searchText.value = query;
};

// Clear search text when popover is closed
watch(open, (isOpen) => {
    if (!isOpen) {
        searchText.value = '';
    }
});

const filteredItems = computed(() => {
    if (!props.items || props.items.length === 0) return [];  // Return empty array if items is empty
    if (!searchText.value) return props.items;
    const lowercasedSearchText = searchText.value.toLowerCase();
    return props.items.filter(item =>
        item[props.labelKey]?.toLowerCase().includes(lowercasedSearchText)
    );
});

const handleSelect = (value) => {
    if (value !== undefined && value !== null) {
        localValue.value = value;
        emit('update:modelValue', value);
        open.value = false;
    }
};

const selectedLabel = computed(() => {
    // Safely access the selected item, and ensure it's not null
    const selectedItem = props.items.find(
        (item) => item[props.valueKey] === localValue.value
    );
    return selectedItem ? selectedItem[props.labelKey] : null;
});
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button variant="outline" role="combobox" :aria-expanded="open" :disabled="disabled"
                :class="props.class">
                {{ selectedLabel || props.placeholder }}
                <ChevronDown class="w-4 h-4 ml-2 opacity-50 shrink-0" />
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[418px] p-0">
            <Command>
                <CommandInput type="search" placeholder="Search..." @input="handleSearch($event.target.value)" />
                <slot />
                <CommandEmpty v-if="filteredItems.length === 0" class="grid gap-4">
                    No items found.
                </CommandEmpty>
                <CommandList>
                    <CommandGroup>
                        <CommandItem v-for="item in filteredItems" :key="item[props.valueKey]"
                            :value="item[props.valueKey]" @select="() => handleSelect(item[props.valueKey])">
                            {{ item[props.labelKey] }}
                            <Check :class="cn(
                                'ml-auto h-4 w-4',
                                localValue === item[props.valueKey] ? 'opacity-100' : 'opacity-0'
                            )" />
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
