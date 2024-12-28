<script setup>
import { Button } from '@/Components/ui/button'

import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger, } from '@/Components/ui/dropdown-menu'
import { Bell, PlusCircle, RefreshCcw, } from 'lucide-vue-next'
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="w-8 h-8 ml-auto">
                <Bell class="w-4 h-4" />
                <span class="sr-only">Toggle notifications</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent>
            <DropdownMenuLabel>Notifications</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem>
                    <PlusCircle class="mr-2 text-green-600 size-5" />
                    <div class="grid">
                        <span>superadmin created a user</span>
                        <span class="text-xs text-muted-foreground">December 1, 2024 at 10:30 AM</span>
                    </div>
                </DropdownMenuItem>
                <DropdownMenuItem>
                    <RefreshCcw class="mr-2 text-yellow-600 size-5" />
                    <div class="grid">
                        <span>superadmin updated a user</span>
                        <span class="text-xs text-muted-foreground">December 1, 2024 at 10:30 AM</span>
                    </div>
                </DropdownMenuItem>
            </DropdownMenuGroup>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
