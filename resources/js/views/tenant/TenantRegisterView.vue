<template>
  <section class="mx-auto max-w-xl space-y-6 rounded border border-slate-800 bg-slate-900/60 p-6">
    <header class="space-y-1 text-center">
      <h1 class="text-2xl font-semibold text-emerald-400">Daftar Akun</h1>
      <p class="text-sm text-slate-400">Buat akun untuk tenant <span class="font-mono text-emerald-300">{{ tenant }}</span>.</p>
    </header>

    <form class="grid gap-4" @submit.prevent="submit">
      <div class="grid gap-1">
        <label class="text-xs uppercase tracking-wide text-slate-400">Nama Lengkap</label>
        <input v-model="form.name" type="text" required class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
      </div>
      <div class="grid gap-1">
        <label class="text-xs uppercase tracking-wide text-slate-400">Email</label>
        <input v-model="form.email" type="email" required class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
      </div>
      <div class="grid gap-1 sm:grid-cols-2 sm:gap-4">
        <div class="grid gap-1">
          <label class="text-xs uppercase tracking-wide text-slate-400">Password</label>
          <input v-model="form.password" type="password" required class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
        </div>
        <div class="grid gap-1">
          <label class="text-xs uppercase tracking-wide text-slate-400">Konfirmasi Password</label>
          <input v-model="form.password_confirmation" type="password" required class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
        </div>
      </div>

      <button type="submit" class="rounded bg-emerald-500 px-4 py-2 font-semibold text-slate-950 hover:bg-emerald-400" :disabled="loading">
        {{ loading ? 'Mendaftarkan...' : 'Daftar' }}
      </button>
    </form>

    <p v-if="error" class="text-center text-sm text-rose-400">{{ error }}</p>

    <p class="text-center text-sm text-slate-400">
      Sudah punya akun?
      <RouterLink :to="{ name: 'tenant-login', params: { tenant } }" class="text-emerald-400 hover:text-emerald-300">
        Masuk di sini
      </RouterLink>
    </p>
  </section>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

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
        router.push({ name: 'tenant-products', params: { tenant: tenant.value } });
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Registrasi gagal.';
    } finally {
        loading.value = false;
    }
}
</script>
