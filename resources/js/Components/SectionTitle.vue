<template>
    <div class="flex justify-between">
        <div class="px-4 sm:px-0">
            <h3 class="font-display text-lg font-bold text-[var(--text-primary)]">
                <slot name="title" />
            </h3>

            <p class="mt-1 text-sm text-[var(--text-secondary)]">
                <slot name="description" />
            </p>
        </div>

        <div class="px-4 sm:px-0">
            <slot name="aside" />
        </div>
    </div>
</template>
