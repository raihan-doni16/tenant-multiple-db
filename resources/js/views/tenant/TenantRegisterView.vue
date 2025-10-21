<template>
  <section class="mx-auto max-w-xl space-y-6 rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-blue-100/50">
    <header class="space-y-2 text-center">
      <h1 class="text-2xl font-semibold text-blue-600">Daftar Akun</h1>
      <p class="text-sm text-slate-500">
        Buat akun untuk tenant <span class="font-mono text-blue-600">{{ tenant }}</span>.
      </p>
    </header>

    <form class="grid gap-4" @submit.prevent="submit">
      <div class="grid gap-1">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Lengkap</label>
        <input
          v-model="form.name"
          type="text"
          required
          class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
        />
      </div>
      <div class="grid gap-1">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</label>
        <input
          v-model="form.email"
          type="email"
          required
          class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
        />
      </div>
      <div class="grid gap-1 sm:grid-cols-2 sm:gap-4">
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
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Konfirmasi Password</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            required
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
          />
        </div>
      </div>

      <button
        type="submit"
        class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
        :disabled="loading"
      >
        {{ loading ? 'Mendaftarkan...' : 'Daftar' }}
      </button>
    </form>

    <p v-if="error" class="text-center text-sm text-rose-500">{{ error }}</p>

    <p class="text-center text-sm text-slate-500">
      Sudah punya akun?
      <RouterLink :to="{ name: 'tenant-login', params: { tenant } }" class="font-semibold text-blue-600 hover:text-blue-700">
        Masuk di sini
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
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    metadata: {},
    device_name: 'SPA',
});

const loading = ref(false);
const error = ref('');

async function submit() {
    if (form.password !== form.password_confirmation) {
        error.value = 'Konfirmasi password tidak cocok.';
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        await authStore.register(form);
        feedbackStore.showSuccess('Registrasi berhasil! Selamat datang.');
        router.push({ name: 'tenant-products', params: { tenant: tenant.value } });
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Registrasi gagal.';
    } finally {
        loading.value = false;
    }
}
</script>
