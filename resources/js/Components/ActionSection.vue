<script setup>
import SectionTitle from './SectionTitle.vue';
</script>

<template>
    <div class="grid gap-5 lg:grid-cols-[16rem,minmax(0,1fr)] lg:gap-8">
        <SectionTitle>
            <template #title>
                <slot name="title" />
            </template>
            <template #description>
                <slot name="description" />
            </template>
        </SectionTitle>

        <div>
            <div class="border border-[var(--border-subtle)] bg-[var(--surface-panel)] px-4 py-5 sm:p-6">
                <slot name="content" />
            </div>
        </div>
    </div>
</template>
