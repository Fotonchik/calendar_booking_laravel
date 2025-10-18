<template>
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-success">Бронирование подтверждено!</h5>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="text-success mb-3" style="font-size: 3rem;">✓</div>
                    <p class="lead">Мы ждём вас!</p>
                    <div class="alert alert-light text-start">
                        <p class="mb-2"><strong>Услуга:</strong> {{ booking.service }}</p>
                        <p class="mb-2"><strong>Дата:</strong> {{ booking.date }}</p>
                        <p class="mb-2"><strong>Время:</strong> {{ booking.time }}</p>
                        <p class="mb-0"><strong>Имя:</strong> {{ booking.customer }}</p>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button @click="$emit('close')" class="btn btn-success w-100">
                        Вернуться к бронированию
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    booking: {
        type: Object,
        default: () => ({})
    }
});

defineEmits(['close']);
</script>
