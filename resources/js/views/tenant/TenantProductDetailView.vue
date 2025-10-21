<template>
  <section class="space-y-6">
    <RouterLink :to="{ name: 'tenant-products', params: { tenant } }" class="text-sm text-emerald-400 hover:text-emerald-300">
      &larr; Kembali ke Produk
    </RouterLink>

    <div v-if="loading" class="rounded border border-slate-800 bg-slate-900/40 p-6 text-slate-400">
      Memuat produk...
    </div>

    <div v-else-if="error" class="rounded border border-rose-500/40 bg-rose-500/10 p-4 text-sm text-rose-300">
      {{ error }}
    </div>

    <article v-else-if="product" class="space-y-6 rounded border border-slate-800 bg-slate-900/60 p-6">
      <header>
        <h1 class="text-4xl font-semibold text-emerald-400">{{ product.name }}</h1>
        <p class="mt-2 text-sm text-slate-400">SKU: <span class="font-mono text-emerald-300">{{ product.sku }}</span></p>
      </header>

      <p class="text-slate-200">{{ product.description ?? 'Belum ada deskripsi yang tersedia.' }}</p>

      <div class="flex flex-wrap items-center gap-6">
        <div class="text-3xl font-semibold text-emerald-400">{{ formatCurrency(product.price) }}</div>
        <div class="text-sm text-slate-400">Stok tersedia: {{ product.stock }}</div>
      </div>

      <button
        type="button"
        class="rounded bg-emerald-500 px-6 py-3 font-semibold text-slate-950 transition hover:bg-emerald-400 disabled:cursor-not-allowed disabled:bg-slate-700 disabled:text-slate-400"
        :disabled="!authStore.isAuthenticated || !product.is_active"
        @click="addToCart"
      >
        {{ authStore.isAuthenticated ? 'Masukkan Keranjang' : 'Masuk untuk Belanja' }}
      </button>
    </article>

    <div v-else class="rounded border border-rose-400/40 bg-rose-500/10 p-6 text-sm text-rose-300">
      Produk tidak ditemukan.
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api, tenantPath } from '../../api/client';
import { useAuthStore } from '../../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

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
        window.dispatchEvent(new CustomEvent('cart:updated'));
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Gagal menambahkan produk ke keranjang';
    }
}

function formatCurrency(value) {
    return Number(value).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
}

onMounted(fetchProduct);
</script>
