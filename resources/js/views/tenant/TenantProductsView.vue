
<script setup>
import { reactive, ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api, tenantPath } from '../../api/client';
import { useAuthStore } from '../../stores/auth';
import { useFeedbackStore } from '../../stores/feedback';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const feedbackStore = useFeedbackStore();

const tenant = computed(() => route.params.tenant);

const products = ref([]);
const loading = ref(false);
const error = ref('');
const filters = reactive({
    search: '',
});

async function fetchProducts() {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await api.get(tenantPath(tenant.value, 'products'), {
            params: {
                active: true,
                search: filters.search || undefined,
            },
        });

        products.value = data.data ?? data;
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Failed to load products.';
    } finally {
        loading.value = false;
    }
}

async function addToCart(product) {
    if (!authStore.isAuthenticated) {
        router.push({ name: 'tenant-login', params: { tenant: tenant.value } });
        return;
    }

    try {
        await api.post(tenantPath(tenant.value, 'cart/items'), {
            product_id: product.id,
            quantity: 1,
        });
        feedbackStore.showSuccess('Product added to cart.');
        window.dispatchEvent(new CustomEvent('cart:updated'));
        product.stock = Math.max(0, Number(product.stock) - 1);
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Failed to add product to cart.';
    }
}

function truncate(text, limit = 120) {
    if (!text) return '';
    return text.length > limit ? `${text.slice(0, limit)}...` : text;
}

function formatCurrency(value) {
    return Number(value).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
}

onMounted(() => {
    void fetchProducts();
});
</script>
<template>
  <section class="space-y-8">
    <header class="flex flex-col gap-3 rounded-2xl bg-white p-6 shadow-sm shadow-blue-100/30 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-3xl font-semibold text-slate-900">Product Catalog</h1>
        <p class="text-sm text-slate-500">
          Showing active products for tenant <span class="font-mono text-blue-600">{{ tenant }}</span>.
        </p>
      </div>
      <form class="flex w-full gap-3 sm:w-auto" @submit.prevent="fetchProducts">
        <input
          v-model="filters.search"
          type="search"
          placeholder="Search products..."
          class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:w-64"
        />
        <button
          type="submit"
          class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
        >
          Search
        </button>
      </form>
    </header>

    <div v-if="error" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
      {{ error }}
    </div>

    <div v-if="loading" class="grid place-items-center rounded-2xl border border-slate-200 bg-white py-12 text-slate-500 shadow-sm shadow-blue-100/20">
      Loading products...
    </div>

    <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
      <article
        v-for="product in products"
        :key="product.id"
        class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-blue-100/30 transition hover:-translate-y-1 hover:shadow-lg"
      >
        <div class="space-y-3">
          <RouterLink
            :to="{ name: 'tenant-product-detail', params: { tenant, productId: product.id } }"
            class="text-lg font-semibold text-slate-900 transition hover:text-blue-600"
          >
            {{ product.name }}
          </RouterLink>
          <p class="text-sm text-slate-500">
            {{ product.description ? truncate(product.description) : 'No description yet.' }}
          </p>
        </div>

        <div class="mt-6 flex items-center justify-between text-sm">
          <span class="text-lg font-semibold text-blue-600">{{ formatCurrency(product.price) }}</span>
          <span class="text-slate-500">Stock: {{ product.stock }}</span>
        </div>

        <button
          type="button"
          class="mt-6 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400"
          :disabled="!authStore.isAuthenticated || !product.is_active"
          @click="addToCart(product)"
        >
          {{ authStore.isAuthenticated ? 'Add to Cart' : 'Sign in to shop' }}
        </button>
      </article>

      <p
        v-if="!products.length"
        class="col-span-full rounded-2xl border border-slate-200 bg-white py-10 text-center text-sm text-slate-500 shadow-sm shadow-blue-100/20"
      >
        No products found.
      </p>
    </div>
  </section>
</template>
