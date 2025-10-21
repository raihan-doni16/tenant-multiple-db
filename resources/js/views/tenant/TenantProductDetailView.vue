
<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api, tenantPath } from '../../api/client';
import { useAuthStore } from '../../stores/auth';
import { useFeedbackStore } from '../../stores/feedback';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const feedbackStore = useFeedbackStore();

const tenant = computed(() => route.params.tenant);
const productId = computed(() => route.params.productId);

const product = ref(null);
const loading = ref(false);
const error = ref('');

async function fetchProduct() {
    loading.value = true;

    try {
        const { data } = await api.get(tenantPath(tenant.value, `products/${productId.value}`));
        product.value = data;
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Produk tidak ditemukan';
    } finally {
        loading.value = false;
    }
}

async function addToCart() {
    if (!authStore.isAuthenticated) {
        router.push({ name: 'tenant-login', params: { tenant: tenant.value } });
        return;
    }

    try {
        await api.post(tenantPath(tenant.value, 'cart/items'), {
            product_id: product.value.id,
            quantity: 1,
        });
        feedbackStore.showSuccess('Produk ditambahkan ke keranjang.');
        window.dispatchEvent(new CustomEvent('cart:updated'));
        if (product.value) {
            product.value.stock = Math.max(0, Number(product.value.stock) - 1);
        }
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Gagal menambahkan produk ke keranjang';
    }
}

function formatCurrency(value) {
    return Number(value).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
}

onMounted(fetchProduct);
</script>
<template>
  <section class="space-y-8">
    <RouterLink
      :to="{ name: 'tenant-products', params: { tenant } }"
      class="inline-flex items-center text-sm font-semibold text-blue-600 transition hover:text-blue-700"
    >
      &larr; Kembali ke Produk
    </RouterLink>

    <div v-if="loading" class="rounded-2xl border border-slate-200 bg-white p-6 text-slate-500 shadow-sm shadow-blue-100/20">
      Memuat produk...
    </div>

    <div v-else-if="error" class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-600">
      {{ error }}
    </div>

    <article v-else-if="product" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-blue-100/30">
      <header class="space-y-2">
        <h1 class="text-4xl font-semibold text-slate-900">{{ product.name }}</h1>
        <p class="text-sm text-slate-500">
          SKU:
          <span class="font-mono text-blue-600">{{ product.sku }}</span>
        </p>
      </header>

      <p class="text-slate-600">{{ product.description ?? 'Belum ada deskripsi yang tersedia.' }}</p>

      <div class="flex flex-wrap items-center gap-6">
        <div class="text-3xl font-semibold text-blue-600">{{ formatCurrency(product.price) }}</div>
        <div class="text-sm text-slate-500">Stok tersedia: {{ product.stock }}</div>
      </div>

      <button
        type="button"
        class="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400"
        :disabled="!authStore.isAuthenticated || !product.is_active"
        @click="addToCart"
      >
        {{ authStore.isAuthenticated ? 'Masukkan Keranjang' : 'Masuk untuk Belanja' }}
      </button>
    </article>

    <div v-else class="rounded-2xl border border-rose-200 bg-rose-50 p-6 text-sm text-rose-600">
      Produk tidak ditemukan.
    </div>
  </section>
</template>
