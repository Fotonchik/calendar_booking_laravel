import { ref } from 'vue';

export function useBooking() {
    const step = ref(1);
    const selectedService = ref(null);
    const selectedDate = ref(null);
    const selectedTime = ref(null);
    const showSuccessModal = ref(false);
    const successBooking = ref({});
    const handleServiceSelected = (service) => {
        selectedService.value = service;
        selectedDate.value = null;
        selectedTime.value = null;
        step.value = 2;
    };
    const handleDateSelected = (date) => {
        selectedDate.value = date;
        step.value = 3;
    };
    const handleTimeSelected = (time) => {
        selectedTime.value = time;
        step.value = 4;
    };
    const handleBookingSubmitted = (bookingData) => {
        successBooking.value = bookingData;
        showSuccessModal.value = true;
    };
    const closeSuccessModal = () => {
        showSuccessModal.value = false;
        selectedService.value = null;
        selectedDate.value = null;
        selectedTime.value = null;
        step.value = 1;
    };
    return {
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
    };
}