<script setup>
import { Eye } from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog'
import { ref, watch } from 'vue';

const props = defineProps({ categories: Object })

const data = ref(props.categories)

watch(() => props.categories, (newCategories) => {
    data.value = newCategories
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
                    Category details
                </DialogTitle>
                <DialogDescription>
                    View category details
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-3 text-sm">
                <ul class="grid gap-3">
                    <li class="flex items-center justify-between">
                        <span class="text-muted-foreground">Name</span> <span>{{ data.name }}</span>
                    </li>
                </ul>
            </div>
            <DialogFooter>
                <DialogClose as-child>
                    <Button type="button" class="gap-1 h-7" variant="outline">
                        Close
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
