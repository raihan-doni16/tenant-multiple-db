<template>
  <section class="space-y-6">
    <header class="space-y-1">
      <h1 class="text-3xl font-semibold text-emerald-400">Manajemen Tenant</h1>
      <p class="text-sm text-slate-400">Buat toko baru dan kelola basis data multi-tenant Anda.</p>
    </header>

    <form class="grid gap-4 rounded border border-slate-800 bg-slate-900/60 p-4" @submit.prevent="createTenant">
      <div class="grid gap-1">
        <label class="text-xs uppercase tracking-wide text-slate-400">Nama Tenant</label>
        <input v-model="form.name" type="text" required class="rounded border border-slate-700 bg-slate-950 px-3 py-2" placeholder="Contoh: Toko Volantis" />
      </div>
      <div class="grid gap-1">
        <label class="text-xs uppercase tracking-wide text-slate-400">Slug</label>
        <input v-model="form.slug" type="text" required class="rounded border border-slate-700 bg-slate-950 px-3 py-2" placeholder="Contoh: volantis" />
        <p class="text-xs text-slate-500">Slug digunakan pada URL: <code>/[slug]</code></p>
      </div>

      <div class="grid gap-1 sm:grid-cols-2 sm:gap-4">
        <div class="grid gap-1">
          <label class="text-xs uppercase tracking-wide text-slate-400">Nama Admin</label>
          <input v-model="form.admin_name" type="text" required class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
        </div>
        <div class="grid gap-1">
          <label class="text-xs uppercase tracking-wide text-slate-400">Email Admin</label>
          <input v-model="form.admin_email" type="email" required class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
        </div>
      </div>

      <div class="grid gap-1 sm:grid-cols-2 sm:gap-4">
        <div class="grid gap-1">
          <label class="text-xs uppercase tracking-wide text-slate-400">Password Admin</label>
          <input v-model="form.admin_password" type="password" required class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
        </div>
        <div class="grid gap-1">
          <label class="text-xs uppercase tracking-wide text-slate-400">Konfirmasi Password</label>
          <input v-model="form.admin_password_confirmation" type="password" required class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button type="submit" class="rounded bg-emerald-500 px-4 py-2 font-semibold text-slate-950 hover:bg-emerald-400" :disabled="loading">
          {{ loading ? 'Membuat...' : 'Buat Tenant' }}
        </button>
        <p v-if="error" class="text-sm text-rose-400">{{ error }}</p>
        <p v-if="success" class="text-sm text-emerald-400">Tenant berhasil dibuat.</p>
      </div>
    </form>

    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-emerald-300">Daftar Tenant</h2>
      <div class="overflow-hidden rounded border border-slate-800">
        <table class="min-w-full divide-y divide-slate-800 text-sm">
          <thead class="bg-slate-900/70 text-xs uppercase tracking-wide text-slate-400">
            <tr>
              <th class="px-4 py-2 text-left">Slug</th>
              <th class="px-4 py-2 text-left">Nama</th>
              <th class="px-4 py-2 text-left">Database</th>
              <th class="px-4 py-2 text-left">Dibuat</th>
              <th class="px-4 py-2 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800">
            <tr v-for="tenant in tenants" :key="tenant.id" class="hover:bg-slate-900/50">
              <td class="px-4 py-2 font-mono text-emerald-300">{{ tenant.id }}</td>
              <td class="px-4 py-2">{{ tenant.name ?? '-' }}</td>
              <td class="px-4 py-2 font-mono text-xs text-slate-400">{{ tenant.database }}</td>
              <td class="px-4 py-2 text-slate-400">{{ formatDate(tenant.created_at) }}</td>
              <td class="px-4 py-2 text-right">
                <RouterLink
                  :to="{ name: 'tenant-login', params: { tenant: tenant.id } }"
                  class="rounded bg-slate-800 px-3 py-1 text-xs font-semibold uppercase tracking-wide hover:bg-emerald-500 hover:text-slate-950"
                >
                  Buka Store
                </RouterLink>
              </td>
            </tr>
            <tr v-if="!tenants.length">
              <td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada tenant terdaftar.</td>
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

const tenants = ref([]);
const loading = ref(false);
const error = ref('');
const success = ref(false);

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
    success.value = false;

    try {
        await adminApi.post('/admin/tenants', form);
        success.value = true;
        Object.assign(form, {
            name: '',
            slug: '',
            admin_name: '',
            admin_email: '',
            admin_password: '',
            admin_password_confirmation: '',
        });
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
