<template>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card booking-card">
                <div class="card-header">
                    <h2 class="h4 mb-0 text-center text-dark">Выберите услугу</h2>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div v-for="service in services" 
                             :key="service.id"
                             class="col-md-6">
                            <div :class="['card service-card', 
                                         { 'border-success': selectedService?.id === service.id }]"
                                 @click="selectService(service)">
                                <div class="card-body text-center p-4">
                                    <h5 class="card-title text-dark mb-2">{{ service.name }}</h5>
                                    <p class="card-text text-muted mb-0">
                                        Продолжительность: {{ service.duration }} минут
                                    </p>
                                    <small class="text-muted">+ 30 минут подготовка</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4" v-if="selectedService">
                        <button @click="handleContinue" class="btn btn-success btn-lg px-5">
                            Продолжить → Выбрать дату
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    services: {
        type: Array,
        required: true
    }
});

const emit = defineEmits(['service-selected']);

const selectedService = ref(null);

const selectService = (service) => {
    selectedService.value = service;
};

const handleContinue = () => {
    if (selectedService.value) {
        emit('service-selected', selectedService.value);
    }
};
</script>