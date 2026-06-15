<script setup>
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Message from "primevue/message";

defineProps({
    open: { type: Boolean, default: false },
});

const emit = defineEmits(["close"]);
</script>

<template>
    <Dialog
        :visible="open"
        modal
        header="Payment not completed"
        class="w-[calc(100vw-2rem)] max-w-md"
        :draggable="false"
        @update:visible="!$event && emit('close')"
    >
        <Message severity="error" :closable="false">
            The payment was not completed. The current sale is still available so you can try again.
        </Message>
        <template #footer>
            <Button label="Return to sale" icon="pi pi-arrow-left" @click="emit('close')" />
        </template>
    </Dialog>
</template>
