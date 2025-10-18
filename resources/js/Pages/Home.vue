<!-- Точка входа (Главная) -->
<template>
    <div class="booking-container">
        <BookingHeader />
        <ProgressSteps :step="step" />
        <main class="container py-4">
            <ServiceSelection 
                v-if="step === 1"
                :services="services"
                @service-selected="handleServiceSelected"
            />
            <DateSelection 
                v-if="step === 2"
                :selected-service="selectedService"
                @date-selected="handleDateSelected"
                @back="step = 1"
            />
            <TimeSelection 
                v-if="step === 3"
                :selected-service="selectedService"
                :selected-date="selectedDate"
                @time-selected="handleTimeSelected"
                @back="step = 2"
            />
            <CustomerData 
                v-if="step === 4"
                :selected-service="selectedService"
                :selected-date="selectedDate"
                :selected-time="selectedTime"
                @booking-submitted="handleBookingSubmitted"
                @back="step = 3"
            />
        </main>
        <SuccessModal 
            v-if="showSuccessModal"
            :booking="successBooking"
            @close="closeSuccessModal"
        />
    </div>
</template>
<script setup>
import { ref } from 'vue';
import { useBooking } from '@/Composables/useBooking';
import BookingHeader from '@/Components/Booking/BookingHeader.vue';
import ProgressSteps from '@/Components/Booking/ProgressSteps.vue';
import ServiceSelection from '@/Components/Booking/ServiceSelection.vue';
import DateSelection from '@/Components/Booking/DateSelection.vue';
import TimeSelection from '@/Components/Booking/TimeSelection.vue';
import CustomerData from '@/Components/Booking/CustomerData.vue';
import SuccessModal from '@/Components/Booking/SuccessModal.vue';
const props = defineProps({
    services: {
        type: Array,
        default: () => []
    }
});
const {
    step,
    selectedService,
    selectedDate,
    selectedTime,
    showSuccessModal,
    successBooking,
    handleServiceSelected,
    handleDateSelected,
    handleTimeSelected,
    handleBookingSubmitted,
    closeSuccessModal
} = useBooking();
</script>