<script setup>
import { Link } from '@inertiajs/vue3';
import { Avatar, AvatarFallback, AvatarImage } from './ui/avatar';
import { Button } from './ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from './ui/dropdown-menu';
import { Separator } from './ui/separator';
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
        <DropdownMenuContent side="bottom" class="w-[200px]">
            <DropdownMenuLabel class="grid gap-2 p-2">
                <div class="flex gap-3">
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
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem>
                <Link :href="route('logout')" method="post" as="button" type="button" class="w-full">
                Logout
                </Link>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
