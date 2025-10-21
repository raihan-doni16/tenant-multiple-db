<template>
  <section class="space-y-8">
    <header class="space-y-2">
      <h1 class="text-3xl font-semibold text-slate-900">Manajemen Tenant</h1>
      <p class="text-sm text-slate-500">Buat toko baru dan kelola basis data multi-tenant Anda.</p>
    </header>

    <form class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-blue-100/40" @submit.prevent="createTenant">
      <div class="grid gap-1">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Tenant</label>
        <input
          v-model="form.name"
          type="text"
          required
          class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
          placeholder="Contoh: Toko Volantis"
        />
      </div>
      <div class="grid gap-1">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Slug</label>
        <input
          v-model="form.slug"
          type="text"
          required
          class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
          placeholder="Contoh: volantis"
        />
        <p class="text-xs text-slate-400">Slug digunakan pada URL: <code class="rounded bg-slate-100 px-1 py-0.5">/[slug]</code></p>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-1">
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Admin</label>
          <input
            v-model="form.admin_name"
            type="text"
            required
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
          />
        </div>
        <div class="grid gap-1">
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email Admin</label>
          <input
            v-model="form.admin_email"
            type="email"
            required
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
          />
        </div>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-1">
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Password Admin</label>
          <input
            v-model="form.admin_password"
            type="password"
            required
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
          />
        </div>
        <div class="grid gap-1">
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Konfirmasi Password</label>
          <input
            v-model="form.admin_password_confirmation"
            type="password"
            required
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
          />
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button
          type="submit"
          class="rounded-lg bg-blue-600 px-5 py-2 font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
          :disabled="loading"
        >
          {{ loading ? 'Membuat...' : 'Buat Tenant' }}
        </button>
        <p v-if="error" class="text-sm text-rose-500">{{ error }}</p>
      </div>
    </form>

    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-slate-900">Daftar Tenant</h2>
      <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-blue-100/30">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
          <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
              <th class="px-4 py-3 text-left">Slug</th>
              <th class="px-4 py-3 text-left">Nama</th>
              <th class="px-4 py-3 text-left">Database</th>
              <th class="px-4 py-3 text-left">Dibuat</th>
              <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="tenant in tenants" :key="tenant.id" class="transition hover:bg-blue-50/70">
              <td class="px-4 py-3 font-mono text-sm text-blue-600">{{ tenant.id }}</td>
              <td class="px-4 py-3">{{ tenant.name ?? '-' }}</td>
              <td class="px-4 py-3 font-mono text-xs text-slate-400">{{ tenant.database }}</td>
              <td class="px-4 py-3 text-slate-500">{{ formatDate(tenant.created_at) }}</td>
              <td class="px-4 py-3 text-right">
                <RouterLink
                  :to="{ name: 'tenant-login', params: { tenant: tenant.id } }"
                  class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-600 transition hover:bg-blue-100 hover:text-blue-700"
                >
                  Buka Store
                </RouterLink>
              </td>
            </tr>
            <tr v-if="!tenants.length">
              <td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada tenant terdaftar.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { adminApi } from '../../api/client';
import { useFeedbackStore } from '../../stores/feedback';

const tenants = ref([]);
const loading = ref(false);
const error = ref('');
const feedbackStore = useFeedbackStore();

const form = reactive({
    name: '',
    slug: '',
    admin_name: '',
    admin_email: '',
    admin_password: '',
    admin_password_confirmation: '',
});

async function fetchTenants() {
    try {
        const { data } = await adminApi.get('/admin/tenants');
        tenants.value = data;
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Gagal memuat tenant.';
    }
}

async function createTenant() {
    if (form.admin_password !== form.admin_password_confirmation) {
        error.value = 'Konfirmasi password tidak cocok.';
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        await adminApi.post('/admin/tenants', form);
        Object.assign(form, {
            name: '',
            slug: '',
            admin_name: '',
            admin_email: '',
            admin_password: '',
            admin_password_confirmation: '',
        });
        feedbackStore.showSuccess('Tenant baru berhasil dibuat.');
        await fetchTenants();
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Gagal membuat tenant.';
    } finally {
        loading.value = false;
    }
}

function formatDate(value) {
    if (!value) return '-';
    return new Date(value).toLocaleString('id-ID');
}

onMounted(fetchTenants);
</script>
