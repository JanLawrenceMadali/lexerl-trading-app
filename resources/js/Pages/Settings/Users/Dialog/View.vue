<script setup>
import { Eye } from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog'
import { ref } from 'vue';

const props = defineProps({ users: Object })

const data = ref(props.users)

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
                    User details
                </DialogTitle>
                <DialogDescription>
                    View user details
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-3 text-sm">
                <ul class="grid gap-3">
                    <li class="flex items-center justify-between">
                        <span class="text-muted-foreground">Name</span> <span>{{ data.username }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-muted-foreground">Email</span> <span>{{ data.email }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-muted-foreground">Role</span> <span>{{ data.role }}</span>
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
