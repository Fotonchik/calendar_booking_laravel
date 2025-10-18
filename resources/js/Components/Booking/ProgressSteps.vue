<template>
    <div class="progress-steps-container">
        <div class="container">
            <div class="row py-3">
                <div class="col">
                    <div class="d-flex justify-content-between align-items-center">
                        <StepIndicator 
                            :step-number="1"
                            label="Услуга"
                            :is-active="step >= 1"
                        />
                        <StepConnector />
                        <StepIndicator 
                            :step-number="2"
                            label="Дата"
                            :is-active="step >= 2"
                        />
                        <StepConnector />
                        <StepIndicator 
                            :step-number="3"
                            label="Время"
                            :is-active="step >= 3"
                        />
                        <StepConnector />
                        <StepIndicator 
                            :step-number="4"
                            label="Данные"
                            :is-active="step >= 4"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import StepIndicator from './StepIndicator.vue';
import StepConnector from './StepConnector.vue';

defineProps({
    step: {
        type: Number,
        required: true
    }
});
</script>