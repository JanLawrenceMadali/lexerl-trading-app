<script setup>
import { Copy, SquareArrowOutUpRight } from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from '@/Components/ui/dialog'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="ghost" size="xs" title="View">
                <SquareArrowOutUpRight :size="18" />
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Share link</DialogTitle>
                <DialogDescription>
                    Anyone who has this link will be able to view this.
                </DialogDescription>
            </DialogHeader>
            <div class="flex items-center space-x-2">
                <div class="grid flex-1 gap-2">
                    <Label for="link" class="sr-only">
                        Link
                    </Label>
                    <Input id="link" default-value="https://shadcn-vue.com/docs/installation" read-only />
                </div>
                <Button type="submit" size="sm" class="px-3">
                    <span class="sr-only">Copy</span>
                    <Copy class="w-4 h-4" />
                </Button>
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