<script setup>
import { Button } from '@/Components/ui/button'

import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger, } from '@/Components/ui/dropdown-menu'
import { Bell, PlusCircle, RefreshCcw, } from 'lucide-vue-next'
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="w-8 h-8 ml-auto">
                <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M8.66667 41.1668V36.8335H13V21.6668C13 18.6696 13.9028 16.0154 15.7083 13.7043C17.5139 11.3571 19.8611 9.82238 22.75 9.10016V7.5835C22.75 6.68072 23.057 5.92238 23.6708 5.30849C24.3208 4.6585 25.0972 4.3335 26 4.3335C26.9028 4.3335 27.6611 4.6585 28.275 5.30849C28.925 5.92238 29.25 6.68072 29.25 7.5835V9.10016C32.1389 9.82238 34.4861 11.3571 36.2917 13.7043C38.0972 16.0154 39 18.6696 39 21.6668V36.8335H43.3333V41.1668H8.66667ZM26 47.6668C24.8083 47.6668 23.7792 47.2515 22.9125 46.421C22.082 45.5543 21.6667 44.5252 21.6667 43.3335H30.3333C30.3333 44.5252 29.9 45.5543 29.0333 46.421C28.2028 47.2515 27.1917 47.6668 26 47.6668ZM17.3333 36.8335H34.6667V21.6668C34.6667 19.2835 33.8181 17.2432 32.1208 15.546C30.4236 13.8488 28.3833 13.0002 26 13.0002C23.6167 13.0002 21.5764 13.8488 19.8792 15.546C18.1819 17.2432 17.3333 19.2835 17.3333 21.6668V36.8335Z"
                        fill="#1D1B20" />
                </svg>
                <span class="sr-only">Toggle notifications</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="mt-1">
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
