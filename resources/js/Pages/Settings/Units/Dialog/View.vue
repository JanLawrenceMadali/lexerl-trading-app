<script setup>
import { Eye } from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog'
import { ref, watch } from 'vue';

const props = defineProps({ unit: Object })

const data = ref(props.unit)

watch(() => props.unit, (newUnits) => {
    data.value = newUnits
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
                    Unit details
                </DialogTitle>
                <DialogDescription>
                    View unit details
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-3 text-sm">
                <ul class="grid gap-3">
                    <li class="flex items-center justify-between">
                        <span class="text-muted-foreground">Name</span> <span>{{ data.name }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-muted-foreground">Abbreviation</span> <span>{{ data.abbreviation }}</span>
                    </li>
                </ul>
            </div>
            <DialogFooter>
                <DialogClose as-child>
                    <Button type="button" variant="secondary">
                        Close
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
