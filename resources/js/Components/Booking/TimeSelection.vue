<template>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card booking-card">
                <div class="card-header">
                    <h2 class="h4 mb-0 text-center text-dark">Выберите время</h2>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info booking-alert">
                        <div class="row">
                            <div class="col-md-6"><strong>Услуга:</strong> {{ selectedService.name }}</div>
                            <div class="col-md-6"><strong>Дата:</strong> {{ formattedSelectedDate }}</div>
                        </div>
                    </div>
                    <div v-if="isLoading" class="text-center py-4">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                        <p class="mt-2 text-muted">Загружаем доступное время...</p>
                    </div>
                    <div v-else-if="availableSlots.length > 0">
                        <h6 class="mb-3 text-dark">Доступное время:</h6>
                        <div class="row g-2">
                            <div v-for="slot in availableSlots" 
                                 :key="slot.time"
                                 class="col-6 col-md-4 col-lg-3">
                                <button @click="selectTime(slot)"
                                        :class="['btn time-slot mb-2', 
                                                 selectedTime?.time === slot.time ? 'btn-success' : 'btn-outline-success']">
                                    <div class="fw-bold">{{ slot.formattedTime }}</div>
                                    <small class="text-muted">до {{ slot.endTime }}</small>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-4">
                        <p class="text-muted mb-3">На этот день нет доступного времени</p>
                        <button @click="$emit('back')" class="btn btn-outline-secondary">
                            ← Выбрать другую дату
                        </button>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <button @click="$emit('back')" class="btn btn-outline-secondary">
                            ← Назад к датам
                        </button>
                        <button v-if="selectedTime" 
                                @click="handleContinue" 
                                class="btn btn-success">
                            Продолжить → Ваши данные
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useApi } from '@/Composables/useApi';

const props = defineProps({
    selectedService: {
        type: Object,
        required: true
    },
    selectedDate: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['time-selected', 'back']);

const { apiRequest } = useApi();

const availableSlots = ref([]);
const selectedTime = ref(null);
const isLoading = ref(false);

const formattedSelectedDate = computed(() => {
    if (!props.selectedDate) return '';
    const date = new Date(props.selectedDate);
    return date.toLocaleDateString('ru-RU', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
});

const loadAvailableSlots = async () => {
    if (!props.selectedService || !props.selectedDate) return;
    
    isLoading.value = true;
    selectedTime.value = null;
    
    try {
        const data = await apiRequest('/get-slots', {
            method: 'POST',
            body: JSON.stringify({
                service_id: props.selectedService.id,
                date: props.selectedDate
            })
        });
        
        availableSlots.value = data.slots || [];
    } catch (error) {
        console.error('Ошибка загрузки слотов:', error);
        availableSlots.value = [];
    } finally {
        isLoading.value = false;
    }
};

const selectTime = (slot) => {
    selectedTime.value = slot;
};

const handleContinue = () => {
    if (selectedTime.value) {
        emit('time-selected', selectedTime.value);
    }
};

onMounted(() => {
    loadAvailableSlots();
});

watch(() => props.selectedDate, loadAvailableSlots);
</script>