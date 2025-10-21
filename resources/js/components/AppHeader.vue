<template>
  <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">
      <RouterLink to="/" class="text-lg font-semibold tracking-tight text-blue-600">
        Tenant Multi Database
      </RouterLink>

      <nav class="flex items-center gap-3 text-sm font-medium text-slate-600">
        <template v-if="tenantSlug">
          <RouterLink
            v-if="authStore.isAuthenticated"
            :to="{ name: 'tenant-products', params: { tenant: tenantSlug } }"
            class="rounded-full px-3 py-1 transition hover:bg-blue-50 hover:text-blue-600"
          >
            Products
          </RouterLink>
          <RouterLink
            v-if="authStore.isAuthenticated"
            :to="{ name: 'tenant-cart', params: { tenant: tenantSlug } }"
            class="rounded-full px-3 py-1 transition hover:bg-blue-50 hover:text-blue-600"
          >
            Cart
          </RouterLink>

          <RouterLink
            v-if="!authStore.isAuthenticated"
            :to="{ name: 'tenant-login', params: { tenant: tenantSlug } }"
            class="rounded-full px-3 py-1 transition hover:bg-blue-50 hover:text-blue-600"
          >
            Sign In
          </RouterLink>

          <RouterLink
            v-if="!authStore.isAuthenticated"
            :to="{ name: 'tenant-register', params: { tenant: tenantSlug } }"
            class="rounded-full px-3 py-1 transition hover:bg-blue-50 hover:text-blue-600"
          >
            Register
          </RouterLink>

          <RouterLink
            v-if="authStore.isOwner"
            :to="{ name: 'tenant-dashboard-products', params: { tenant: tenantSlug } }"
            class="rounded-full px-3 py-1 transition hover:bg-blue-50 hover:text-blue-600"
          >
            Product Dashboard
          </RouterLink>

          <button
            v-if="authStore.isAuthenticated"
            type="button"
            class="rounded-full bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700"
            @click="logout"
          >
            Sign Out
          </button>
        </template>

        <template v-else>
          <RouterLink
            v-if="!adminAuthStore.isAuthenticated"
            to="/admin/login"
            class="rounded-full px-3 py-1 transition hover:bg-blue-50 hover:text-blue-600"
          >
            Admin Login
          </RouterLink>
          <button
            v-else
            type="button"
            class="rounded-full bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700"
            @click="logoutAdmin"
          >
            Logout Admin
          </button>
        </template>
      </nav>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
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
