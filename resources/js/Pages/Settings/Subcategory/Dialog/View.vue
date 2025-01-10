<script setup>
import { Eye } from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog'
import { ref } from 'vue';
import { watch } from 'vue';

const props = defineProps({
    categories: Object,
    subcategories: Object,
})

const data = ref(props.subcategories)

watch(() => props.subcategories, (newSubCategories) => {
    data.value = newSubCategories
}, { immediate: true })
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="ghost" size="xs" title="View">
                <Eye :size="18" />
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>
                    Sub Category details
                </DialogTitle>
                <DialogDescription>
                    View sub category details
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-3 text-sm">
                <ul class="grid gap-3">
                    <li class="flex items-center justify-between">
                        <span class="text-muted-foreground">Name</span> <span>{{ data.name }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-muted-foreground">Category</span> <span>{{ data.categories?.name }}</span>
                    </li>
                </ul>
            </div>
        </DialogContent>
    </Dialog>
</template>
