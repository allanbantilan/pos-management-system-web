<script setup>
import { computed, useSlots } from 'vue';
import SectionTitle from './SectionTitle.vue';

defineEmits(['submitted']);

const hasActions = computed(() => !! useSlots().actions);
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
            <form @submit.prevent="$emit('submitted')">
                <div
                    class="border border-[var(--border-subtle)] bg-[var(--surface-panel)] px-4 py-5 sm:p-6"
                >
                    <div class="grid grid-cols-6 gap-6">
                        <slot name="form" />
                    </div>
                </div>

                <div v-if="hasActions" class="flex items-center justify-end border-x border-b border-[var(--border-subtle)] bg-[var(--surface-muted)] px-4 py-3 text-end sm:px-6">
                    <slot name="actions" />
                </div>
            </form>
        </div>
    </div>
</template>
