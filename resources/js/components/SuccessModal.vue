<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="modal.open"
        class="fixed inset-0 z-50 grid place-items-center bg-slate-900/60 px-4 backdrop-blur-sm"
        @click.self="close"
      >
        <Transition name="scale">
          <div
            v-if="modal.open"
            class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-2xl shadow-blue-200/70"
          >
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-blue-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
              </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ modal.title }}</h3>
            <p class="mt-2 text-sm text-slate-500">{{ modal.message }}</p>
            <button
              type="button"
              class="mt-6 inline-flex items-center justify-center rounded-full bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
              @click="close"
            >
              Tutup
            </button>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue';
import { useFeedbackStore } from '../stores/feedback';

const feedbackStore = useFeedbackStore();
const modal = computed(() => feedbackStore.successModal);

function close() {
    feedbackStore.closeSuccess();
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 200ms ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.scale-enter-active,
.scale-leave-active {
  transition: transform 200ms ease, opacity 200ms ease;
}

.scale-enter-from,
.scale-leave-to {
  opacity: 0;
  transform: scale(0.9);
}
</style>
