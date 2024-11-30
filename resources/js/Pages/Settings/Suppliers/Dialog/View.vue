<script setup>
import { Eye } from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog'
import { ref } from 'vue';

const props = defineProps({ suppliers: Object })

const data = ref(props.suppliers)

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
                    Supplier Details
                </DialogTitle>
                <DialogDescription>
                    View supplier details
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-3 text-sm">
                <ul class="grid gap-3">
                    <li class="flex items-center justify-between">
                        <span class="text-muted-foreground">Name</span> <span>{{ data.name }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-muted-foreground">Email</span> <span>{{ data.email }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-muted-foreground ">Contact Person</span> <span>{{
                            data.contact_person }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-muted-foreground ">Contact Number</span> <span>{{
                            data.contact_number }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-muted-foreground ">Address 1</span> <span class="w-1/2 text-right">{{
                            data.address1 }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-muted-foreground ">Address 2</span> <span class="w-1/2 text-right">{{
                            data.address2 }}</span>
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
