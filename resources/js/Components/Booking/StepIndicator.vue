<!--  Индикатор шага -->
<template>
    <div :class="['step-indicator', isActive ? 'step-active' : '']">
        <span class="step-number">{{ stepNumber }}</span>
        <span class="step-label">{{ label }}</span>
    </div>
</template>

<script setup>
defineProps({
    stepNumber: {
        type: Number,
        required: true
    },
    label: {
        type: String,
        required: true
    },
    isActive: {
        type: Boolean,
        default: false
    }
});
</script>