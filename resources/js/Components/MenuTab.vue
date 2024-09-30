<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
    icon: {
        type: [Object, Function]
    },
    route: {
        type: String,
        required: false,
        default: null
    }
})

</script>

<template>
    <Link :href="route" :class="['grid place-items-center gap-3 px-3 py-2 transition-all rounded-lg hover:text-primary',
        $page.component === label ? 'text-foreground' : 'text-muted-foreground']">
    <component :is="icon" class="size-8" />
    <span class="text-sm">{{ label }}</span>
    </Link>
</template>