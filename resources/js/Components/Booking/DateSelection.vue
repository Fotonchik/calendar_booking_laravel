<template>
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h2 class="h4 mb-0 text-center text-dark">Выберите дату</h2>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-success">
                        <strong>Выбрана услуга:</strong> {{ selectedService.name }} 
                        ({{ selectedService.duration }} минут + 30 минут подготовка)
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded">
                        <button @click="previousWeek" class="btn btn-outline-secondary">
                            ← Предыдущая неделя
                        </button>
                        <h5 class="mb-0 text-dark">{{ weekRange }}</h5>
                        <button @click="nextWeek" class="btn btn-outline-secondary">
                            Следующая неделя →
                        </button>
                    </div>
                    <div class="row g-3 mb-4">
                        <div v-for="day in weekDays" 
                             :key="day.date"
                             class="col-6 col-md">
                            <div :class="['card h-100 day-card', getDayCardClass(day)]"
                                 @click="selectDate(day)">
                                <div class="card-body text-center p-3">
                                    <div class="fw-bold text-uppercase small text-dark">{{ day.dayName }}</div>
                                    <div class="h5 mb-2 text-dark">{{ day.formattedDate }}</div>
                                    <div :class="getStatusClass(day)">
                                        {{ getStatusText(day) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 mb-4">
                        <div class="col text-center">
                            <span class="badge bg-success me-2">Свободный день</span>
                            <span class="badge bg-danger me-2">Занято полностью</span>
                            <span class="badge bg-secondary me-2">Выходной</span>
                            <span class="badge bg-warning me-2">Прошедшая дата</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button @click="$emit('back')" class="btn btn-outline-secondary">
                            ← Назад к услугам
                        </button>
                        <button v-if="selectedDate" 
                                @click="handleContinue" 
                                class="btn btn-success"
                                :disabled="!selectedDate">
                            Продолжить → Выбрать время
                        </button>
                    </div>

                    <div v-if="errors.date" class="alert alert-danger mt-3">
                        {{ errors.date }}
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
    }
});

const emit = defineEmits(['date-selected', 'back']);

const { apiRequest } = useApi();

const currentWeekStart = ref(new Date());
const selectedDate = ref(null);
const weekDays = ref([]);
const errors = ref({});

const weekRange = computed(() => {
    const start = new Date(currentWeekStart.value);
    const end = new Date(start);
    end.setDate(start.getDate() + 6);
    return `${start.toLocaleDateString('ru-RU')} - ${end.toLocaleDateString('ru-RU')}`;
});

const generateWeekDays = () => {
    const days = [];
    const start = new Date(currentWeekStart.value);
    const today = new Date().setHours(0, 0, 0, 0);

    for (let i = 0; i < 7; i++) {
        const date = new Date(start);
        date.setDate(start.getDate() + i);
        const dateWithoutTime = new Date(date.getFullYear(), date.getMonth(), date.getDate()).getTime();
        
        days.push({
            date: date.toISOString().split('T')[0],
            dayName: date.toLocaleDateString('ru-RU', { weekday: 'long' }),
            formattedDate: date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' }),
            isSunday: date.getDay() === 0,
            isPast: dateWithoutTime < today,
            isAvailable: true,
            isFullyBooked: false
        });
    }
    return days;
};

const checkWeekAvailability = async () => {
    if (!props.selectedService) return;
    
    try {
        const data = await apiRequest('/get-week-availability', {
            method: 'POST',
            body: JSON.stringify({
                service_id: props.selectedService.id,
                week_start: currentWeekStart.value.toISOString().split('T')[0]
            })
        });
        
        weekDays.value.forEach(day => {
            const dayData = data.week_days.find(d => d.date === day.date);
            if (dayData) {
                day.isAvailable = dayData.is_available;
                day.isFullyBooked = dayData.is_fully_booked;
            }
        });
    } catch (error) {
        console.error('Ошибка проверки доступности:', error);
        weekDays.value.forEach(day => {
            if (!day.isSunday && !day.isPast) {
                day.isAvailable = true;
                day.isFullyBooked = false;
            }
        });
    }
};

const previousWeek = () => {
    const newDate = new Date(currentWeekStart.value);
    newDate.setDate(newDate.getDate() - 7);
    currentWeekStart.value = newDate;
    selectedDate.value = null;
    updateWeekDays();
};

const nextWeek = () => {
    const newDate = new Date(currentWeekStart.value);
    newDate.setDate(newDate.getDate() + 7);
    currentWeekStart.value = newDate;
    selectedDate.value = null;
    updateWeekDays();
};

const updateWeekDays = () => {
    weekDays.value = generateWeekDays();
    checkWeekAvailability();
};

const selectDate = (day) => {
    if (day.isSunday || day.isPast || day.isFullyBooked || !day.isAvailable) return;
    
    selectedDate.value = day.date;
    errors.value.date = '';
    emit('date-selected', day.date);
};

const handleContinue = () => {
    if (selectedDate.value) {
        emit('date-selected', selectedDate.value);
    }
};

const getDayCardClass = (day) => {
    if (day.isSunday) return 'bg-light text-muted';
    if (day.isPast) return 'bg-light text-muted';
    if (day.isFullyBooked || !day.isAvailable) return 'bg-danger text-white';
    if (selectedDate.value === day.date) return 'border-success';
    return 'bg-white';
};

const getStatusClass = (day) => {
    if (day.isSunday || day.isPast) return 'text-muted';
    if (day.isFullyBooked || !day.isAvailable) return 'text-white';
    return 'text-success';
};

const getStatusText = (day) => {
    if (day.isSunday) return 'Выходной';
    if (day.isPast) return 'Прошедшая дата';
    if (day.isFullyBooked || !day.isAvailable) return 'Занято полностью';
    return 'Свободный день';
};

onMounted(() => {
    const today = new Date();
    const dayOfWeek = today.getDay();
    const diff = today.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
    currentWeekStart.value = new Date(today.setDate(diff));
    updateWeekDays();
});

watch(currentWeekStart, updateWeekDays);
</script>
