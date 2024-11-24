<script setup>
defineProps({
    label: { type: String, required: true },
    icon: { type: [Object, Function] },
    route: { type: String, required: false, default: null },
    customClass: { type: String, required: false, default: null }
})

</script>

<template>
    <Link :href="route"
        :class="[customClass, 'grid place-items-center gap-3 px-3 py-2 mx-1 text-slate-100 hover:bg-slate-100 transition-all rounded hover:text-primary']">
    <component :is="icon" class="size-8" />
    <span class="text-sm">{{ label }}</span>
    </Link>
</template>
