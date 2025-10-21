<template>
  <section class="mx-auto mt-12 max-w-md space-y-6 rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-blue-100/50">
    <header class="space-y-2 text-center">
      <h1 class="text-2xl font-semibold text-blue-600">Masuk Admin</h1>
      <p class="text-sm text-slate-500">Gunakan akun admin pusat untuk mengelola tenant.</p>
    </header>

    <form class="space-y-4" @submit.prevent="submit">
      <div class="grid gap-1">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</label>
        <input
          v-model="form.email"
          type="email"
          required
          class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
        />
      </div>
      <div class="grid gap-1">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Password</label>
        <input
          v-model="form.password"
          type="password"
          required
          class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
        />
      </div>
      <div class="grid gap-1">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Perangkat</label>
        <input
          v-model="form.device_name"
          type="text"
          placeholder="Misal: Admin Console"
          class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
        />
      </div>

      <button
        type="submit"
        class="w-full rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
        :disabled="loading"
      >
        {{ loading ? 'Memproses...' : 'Masuk' }}
      </button>
    </form>

    <p v-if="error" class="text-center text-sm text-rose-500">{{ error }}</p>
  </section>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAdminAuthStore } from '../../stores/adminAuth';
import { useFeedbackStore } from '../../stores/feedback';

const router = useRouter();
const adminAuthStore = useAdminAuthStore();
const feedbackStore = useFeedbackStore();

const form = reactive({
    email: '',
    password: '',
    device_name: 'Admin SPA',
});

const loading = ref(false);
const error = ref('');

async function submit() {
    loading.value = true;
    error.value = '';

    try {
        await adminAuthStore.login(form);
        feedbackStore.showSuccess('Berhasil masuk sebagai admin.');
        router.push({ name: 'admin-tenants' });
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Login gagal. Periksa kredensial Anda.';
    } finally {
        loading.value = false;
    }
}
</script>
