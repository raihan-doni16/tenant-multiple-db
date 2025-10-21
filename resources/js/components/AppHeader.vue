<template>
  <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
      <RouterLink to="/" class="text-lg font-semibold uppercase tracking-widest text-emerald-400">
        Tenant Commerce
      </RouterLink>

      <nav class="flex items-center gap-4 text-sm font-medium">
        <template v-if="tenantSlug">
          <RouterLink
            v-if="authStore.isAuthenticated"
            :to="{ name: 'tenant-products', params: { tenant: tenantSlug } }"
            class="hover:text-emerald-400"
          >
            Produk
          </RouterLink>
          <RouterLink
            v-if="authStore.isAuthenticated"
            :to="{ name: 'tenant-cart', params: { tenant: tenantSlug } }"
            class="hover:text-emerald-400"
          >
            Keranjang
          </RouterLink>

          <RouterLink
            v-if="!authStore.isAuthenticated"
            :to="{ name: 'tenant-login', params: { tenant: tenantSlug } }"
            class="hover:text-emerald-400"
          >
            Masuk
          </RouterLink>

          <RouterLink
            v-if="!authStore.isAuthenticated"
            :to="{ name: 'tenant-register', params: { tenant: tenantSlug } }"
            class="hover:text-emerald-400"
          >
            Daftar
          </RouterLink>

          <RouterLink
            v-if="authStore.isOwner"
            :to="{ name: 'tenant-dashboard-products', params: { tenant: tenantSlug } }"
            class="hover:text-emerald-400"
          >
            Dashboard Produk
          </RouterLink>

          <button
            v-if="authStore.isAuthenticated"
            type="button"
            class="rounded bg-emerald-500 px-3 py-1 text-slate-950 transition hover:bg-emerald-400"
            @click="logout"
          >
            Keluar
          </button>
        </template>

        <template v-else>
          <RouterLink to="/admin/tenants" class="hover:text-emerald-400">
            Tenant Manager
          </RouterLink>
          <RouterLink
            v-if="!adminAuthStore.isAuthenticated"
            to="/admin/login"
            class="hover:text-emerald-400"
          >
            Masuk Admin
          </RouterLink>
          <button
            v-else
            type="button"
            class="rounded bg-emerald-500 px-3 py-1 text-slate-950 transition hover:bg-emerald-400"
            @click="logoutAdmin"
          >
            Keluar Admin
          </button>
        </template>
      </nav>
    </div>
  </header>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router';
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useAdminAuthStore } from '../stores/adminAuth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const adminAuthStore = useAdminAuthStore();

const tenantSlug = computed(() => route.params.tenant ?? null);

async function logout() {
    await authStore.logout();

    if (tenantSlug.value) {
        router.push({ name: 'tenant-login', params: { tenant: tenantSlug.value } });
    } else {
        router.push('/');
    }
}

async function logoutAdmin() {
    await adminAuthStore.logout();
    router.push({ name: 'admin-login' });
}
</script>
