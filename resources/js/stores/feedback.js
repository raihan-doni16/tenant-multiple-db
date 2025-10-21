import { defineStore } from 'pinia';
import { reactive } from 'vue';

export const useFeedbackStore = defineStore('feedback', () => {
    const successModal = reactive({
        open: false,
        title: 'Berhasil',
        message: '',
    });

    let hideTimeoutId = null;

    function closeSuccess() {
        if (hideTimeoutId) {
            clearTimeout(hideTimeoutId);
            hideTimeoutId = null;
        }

        successModal.open = false;
    }

    function showSuccess(message, options = {}) {
        if (hideTimeoutId) {
            clearTimeout(hideTimeoutId);
            hideTimeoutId = null;
        }

        successModal.title = options.title ?? 'Berhasil';
        successModal.message = message;
        successModal.open = true;

        const duration = options.duration ?? 2500;

        if (duration > 0) {
            hideTimeoutId = window.setTimeout(() => {
                closeSuccess();
            }, duration);
        }
    }

    return {
        successModal,
        showSuccess,
        closeSuccess,
    };
});
