<template>
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h2 class="h4 mb-0 text-center text-dark">Ваши данные</h2>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-success mb-4">
                        <h6 class="alert-heading">Детали бронирования</h6>
                        <div class="row">
                            <div class="col-12 mb-2"><strong>Услуга:</strong> {{ selectedService.name }}</div>
                            <div class="col-12 mb-2"><strong>Дата:</strong> {{ formattedSelectedDate }}</div>
                            <div class="col-12 mb-2"><strong>Время:</strong> {{ selectedTime.formattedTime }} - {{ selectedTime.endTime }}</div>
                            <div class="col-12"><strong>Общее время:</strong> {{ selectedService.duration + 30 }} минут</div>
                        </div>
                    </div>
                    <form @submit.prevent="submitBooking">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label">Ваше имя *</label>
                                <input type="text" 
                                       id="name" 
                                       v-model="bookingForm.name" 
                                       required 
                                       placeholder="Введите ваше имя"
                                       :class="['form-control', { 'is-invalid': errors.name }]">
                                <div v-if="errors.name" class="invalid-feedback">
                                    {{ errors.name }}
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label for="phone" class="form-label">Телефон *</label>
                                <input type="tel" 
                                       id="phone" 
                                       v-model="bookingForm.phone" 
                                       required 
                                       placeholder="+7 (999) 999-99-99"
                                       :class="['form-control', { 'is-invalid': errors.phone }]">
                                <div v-if="errors.phone" class="invalid-feedback">
                                    {{ errors.phone }}
                                </div>
                            </div>
                        </div>

                        <div v-if="errors.booking" class="alert alert-danger mt-3">
                            {{ errors.booking }}
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" @click="$emit('back')" class="btn btn-outline-secondary">
                                ← Назад ко времени
                            </button>
                            <button type="submit" class="btn btn-success" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                                {{ isSubmitting ? 'Бронируем...' : 'Забронировать' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useApi } from '@/Composables/useApi';

const props = defineProps({
    selectedService: {
        type: Object,
        required: true
    },
    selectedDate: {
        type: String,
        required: true
    },
    selectedTime: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['booking-submitted', 'back']);

const { apiRequest } = useApi();

const bookingForm = ref({
    name: '',
    phone: ''
});
const errors = ref({});
const isSubmitting = ref(false);

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

const validatePhone = (phone) => {
    const phoneRegex = /^(\+7|8)[\s\-]?\(?[0-9]{3}\)?[\s\-]?[0-9]{3}[\s\-]?[0-9]{2}[\s\-]?[0-9]{2}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
};

const formatPhone = (phone) => {
    const cleaned = phone.replace(/\D/g, '');
    if (cleaned.startsWith('7') || cleaned.startsWith('8')) {
        return '+7' + cleaned.slice(1);
    }
    return cleaned;
};

const submitBooking = async () => {
    errors.value = {};
    isSubmitting.value = true;

    if (!bookingForm.value.name.trim()) {
        errors.value.name = 'Введите имя';
        isSubmitting.value = false;
        return;
    }

    if (!bookingForm.value.phone.trim()) {
        errors.value.phone = 'Введите телефон';
        isSubmitting.value = false;
        return;
    }

    if (!validatePhone(bookingForm.value.phone)) {
        errors.value.phone = 'Введите корректный номер телефона';
        isSubmitting.value = false;
        return;
    }

    try {
        const formattedPhone = formatPhone(bookingForm.value.phone);
        
        const data = await apiRequest('/book', {
            method: 'POST',
            body: JSON.stringify({
                service_id: props.selectedService.id,
                date: props.selectedDate,
                time: props.selectedTime.time,
                name: bookingForm.value.name.trim(),
                phone: formattedPhone
            })
        });
        
        if (data.success) {
            emit('booking-submitted', data.booking);
        } else {
            errors.value.booking = data.message || 'Ошибка бронирования';
        }
    } catch (error) {
        console.error('Ошибка бронирования:', error);
        errors.value.booking = 'Ошибка при бронировании';
    } finally {
        isSubmitting.value = false;
    }
};
</script>