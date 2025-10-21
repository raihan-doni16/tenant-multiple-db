<template>
  <div class="flex min-h-screen flex-col bg-slate-100 text-slate-800">
    <AppHeader />

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:py-12">
      <RouterView />
    </main>

    <AppFooter />
    <SuccessModal />
  </div>
</template>

<script setup>
import AppFooter from './components/AppFooter.vue';
import AppHeader from './components/AppHeader.vue';
import SuccessModal from './components/SuccessModal.vue';
</script>
