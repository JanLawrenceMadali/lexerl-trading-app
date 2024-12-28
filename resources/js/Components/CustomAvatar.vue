<script setup>
import { Link } from '@inertiajs/vue3';
import { Avatar, AvatarFallback, AvatarImage } from './ui/avatar';
import { Button } from './ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from './ui/dropdown-menu';
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="flex items-center justify-between gap-3 py-2 pl-0 rounded-full">
                <Avatar>
                    <AvatarImage src="/assets/images/Avatar.png" alt="Avatar" />
                    <AvatarFallback>
                        <span class="uppercase">{{ $page.props.auth.user.username[0] }}</span>
                    </AvatarFallback>
                </Avatar>
                <div class="flex flex-col text-left">
                    <span>{{ $page.props.auth.user.username }}</span>
                    <span class="text-xs font-normal">
                        {{ $page.props.auth.user.role_id === 1 ? 'Super Admin' :
                            $page.props.auth.user.role_id === 2 ? 'Admin' : 'Employee' }}
                    </span>
                </div>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuItem>
                <Link :href="route('logout')" method="post" as="button" type="button" class="w-full text-start">
                Logout
                </Link>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
