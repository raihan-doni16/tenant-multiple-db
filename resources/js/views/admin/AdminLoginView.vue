<template>
  <section class="mx-auto mt-12 max-w-md space-y-6 rounded border border-slate-800 bg-slate-900/60 p-6">
    <header class="space-y-1 text-center">
      <h1 class="text-2xl font-semibold text-emerald-400">Masuk Admin</h1>
      <p class="text-sm text-slate-400">Gunakan akun admin pusat untuk mengelola tenant.</p>
    </header>

    <form class="space-y-4" @submit.prevent="submit">
      <div class="grid gap-1">
        <label class="text-xs uppercase tracking-wide text-slate-400">Email</label>
        <input v-model="form.email" type="email" required class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
      </div>
      <div class="grid gap-1">
        <label class="text-xs uppercase tracking-wide text-slate-400">Password</label>
        <input v-model="form.password" type="password" required class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
      </div>
      <div class="grid gap-1">
        <label class="text-xs uppercase tracking-wide text-slate-400">Nama Perangkat</label>
        <input v-model="form.device_name" type="text" placeholder="Misal: Admin Console" class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
      </div>

      <button type="submit" class="w-full rounded bg-emerald-500 px-4 py-2 font-semibold text-slate-950 hover:bg-emerald-400" :disabled="loading">
        {{ loading ? 'Memproses...' : 'Masuk' }}
      </button>
    </form>

    <p v-if="error" class="text-center text-sm text-rose-400">{{ error }}</p>
  </section>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAdminAuthStore } from '../../stores/adminAuth';

const router = useRouter();
const adminAuthStore = useAdminAuthStore();

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
        router.push({ name: 'admin-tenants' });
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Login gagal. Periksa kredensial Anda.';
    } finally {
        loading.value = false;
    }
}
</script>
