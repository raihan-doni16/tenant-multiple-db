<template>
  <section class="space-y-8">
    <header class="space-y-2">
      <h1 class="text-3xl font-semibold text-slate-900">Tenant Management</h1>
      <p class="text-sm text-slate-500">Create new stores and manage your multi-tenant database.</p>
    </header>

    <form class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-blue-100/40" @submit.prevent="createTenant">
      <div class="grid gap-1">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tenant Name</label>
        <input
          v-model="form.name"
          type="text"
          required
          class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
          placeholder="Example: Barokah Store"
        />
      </div>
      <div class="grid gap-1">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Slug</label>
        <input
          v-model="form.slug"
          type="text"
          required
          class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
          placeholder="Example: barokah-store"
        />
        <p class="text-xs text-slate-400">Slug will be used in the URL: <code class="rounded bg-slate-100 px-1 py-0.5">/[slug]</code></p>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-1">
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Admin Name</label>
          <input
            v-model="form.admin_name"
            type="text"
            required
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
          />
        </div>
        <div class="grid gap-1">
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Admin Email</label>
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
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Admin Password</label>
          <input
            v-model="form.admin_password"
            type="password"
            required
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
          />
        </div>
        <div class="grid gap-1">
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Confirm Password</label>
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
          {{ loading ? 'Creating...' : 'Create Tenant' }}
        </button>
        <p v-if="error" class="text-sm text-rose-500">{{ error }}</p>
      </div>
    </form>

    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-slate-900">Tenant List</h2>
      <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-blue-100/30 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search Tenant</label>
          <input
            v-model="search"
            type="search"
            placeholder="Search by name or slug..."
            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
            @input="onSearchChange"
          />
        </div>
        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center sm:gap-3">
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 sm:text-right">Rows per page</label>
          <select
            v-model.number="pagination.per_page"
            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:w-32"
            @change="onPerPageChange"
          >
            <option v-for="option in perPageOptions" :key="option" :value="option">
              {{ option }}
            </option>
          </select>
        </div>
      </div>
      <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-blue-100/30">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
          <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
              <th class="px-4 py-3 text-left">Slug</th>
              <th class="px-4 py-3 text-left">Name</th>
              <th class="px-4 py-3 text-left">Database</th>
              <th class="px-4 py-3 text-left">Created</th>
              <th class="px-4 py-3 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="listLoading">
              <td colspan="5" class="px-4 py-6 text-center text-slate-400">Loading tenant data...</td>
            </tr>
            <template v-else>
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
                <td colspan="5" class="px-4 py-6 text-center text-slate-400">No tenants found.</td>
              </tr>
            </template>
          </tbody>
        </table>
        <div
          v-if="pagination.total > 0 && !listLoading"
          class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between"
        >
          <span>Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }} tenants</span>
          <div class="flex items-center gap-3">
            <button
              class="rounded-full border border-slate-200 px-3 py-1 font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600 disabled:cursor-not-allowed disabled:border-slate-100 disabled:text-slate-300"
              :disabled="pagination.current_page <= 1"
              @click="changePage(pagination.current_page - 1)"
            >
              Previous
            </button>
            <span class="font-semibold text-slate-700">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
            <button
              class="rounded-full border border-slate-200 px-3 py-1 font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600 disabled:cursor-not-allowed disabled:border-slate-100 disabled:text-slate-300"
              :disabled="pagination.current_page >= pagination.last_page"
              @click="changePage(pagination.current_page + 1)"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </section>
  </section>
</template>

<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { adminApi } from '../../api/client';
import { useFeedbackStore } from '../../stores/feedback';

const tenants = ref([]);
const loading = ref(false);
const error = ref('');
const listLoading = ref(false);
const feedbackStore = useFeedbackStore();
const search = ref('');
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    from: 0,
    to: 0,
});
const perPageOptions = [5, 10, 25, 50];
let searchTimer;

const form = reactive({
    name: '',
    slug: '',
    admin_name: '',
    admin_email: '',
    admin_password: '',
    admin_password_confirmation: '',
});

async function fetchTenants(page = pagination.current_page) {
    listLoading.value = true;
    try {
        const { data } = await adminApi.get('/admin/tenants', {
            params: {
                page,
                per_page: pagination.per_page,
                search: search.value || undefined,
            },
        });
        tenants.value = data.data ?? [];
        Object.assign(pagination, {
            current_page: data.current_page ?? 1,
            last_page: data.last_page ?? 1,
            per_page: Number(data.per_page ?? pagination.per_page),
            total: data.total ?? 0,
            from: data.from ?? 0,
            to: data.to ?? 0,
        });
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Failed to load tenants.';
    } finally {
        listLoading.value = false;
    }
}

async function createTenant() {
    if (form.admin_password !== form.admin_password_confirmation) {
        error.value = 'Password confirmation does not match.';
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
        feedbackStore.showSuccess('Tenant created successfully.');
        await fetchTenants(1);
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Failed to create tenant.';
    } finally {
        loading.value = false;
    }
}

function changePage(page) {
    if (page < 1 || page > pagination.last_page || page === pagination.current_page) {
        return;
    }

    fetchTenants(page);
}

function onPerPageChange() {
    fetchTenants(1);
}

function onSearchChange() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        fetchTenants(1);
    }, 300);
}

function formatDate(value) {
    if (!value) return '-';
    return new Date(value).toLocaleString('id-ID');
}

onMounted(fetchTenants);
onBeforeUnmount(() => {
    clearTimeout(searchTimer);
});
</script>
