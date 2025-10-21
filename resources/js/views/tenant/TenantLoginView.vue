<template>
  <section class="mx-auto max-w-md space-y-6 rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-blue-100/50">
    <header class="space-y-2 text-center">
      <h1 class="text-2xl font-semibold text-blue-600">Masuk</h1>
      <p class="text-sm text-slate-500">Masuk untuk mengelola toko atau melanjutkan belanja.</p>
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
          placeholder="Misal: Browser Utama"
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

    <p class="text-center text-sm text-slate-500">
      Belum punya akun?
      <RouterLink :to="{ name: 'tenant-register', params: { tenant } }" class="font-semibold text-blue-600 hover:text-blue-700">
        Daftar sekarang
      </RouterLink>
    </p>
  </section>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useFeedbackStore } from '../../stores/feedback';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const feedbackStore = useFeedbackStore();

const tenant = computed(() => route.params.tenant);

const form = reactive({
    email: '',
    password: '',
    device_name: 'SPA',
});

const loading = ref(false);
const error = ref('');

async function submit() {
    loading.value = true;
    error.value = '';

    try {
        await authStore.login(form);
        feedbackStore.showSuccess('Login berhasil! Selamat berbelanja.');
        router.push({ name: 'tenant-products', params: { tenant: tenant.value } });
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Login gagal. Periksa kredensial Anda.';
    } finally {
        loading.value = false;
    }
}
</script>
