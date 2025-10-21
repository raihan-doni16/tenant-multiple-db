<template>
  <div class="min-h-screen bg-slate-950 text-slate-100">
    <AppHeader />

    <main class="mx-auto w-full max-w-6xl px-4 py-6">
      <RouterView />
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import AppFooter from './components/AppFooter.vue';
import AppHeader from './components/AppHeader.vue';
</script>
