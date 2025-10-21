<template>
  <section class="space-y-6">
    <header class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-3xl font-semibold text-emerald-400">Katalog Produk</h1>
        <p class="text-sm text-slate-400">Menampilkan produk aktif untuk tenant <span class="font-mono text-emerald-300">{{ tenant }}</span>.</p>
      </div>
      <form class="flex gap-2" @submit.prevent="fetchProducts">
        <input v-model="filters.search" type="search" placeholder="Cari produk..." class="rounded border border-slate-700 bg-slate-950 px-3 py-2 text-sm" />
        <button type="submit" class="rounded bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400">Cari</button>
      </form>
    </header>

    <div v-if="error" class="rounded border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-300">
      {{ error }}
    </div>

    <div v-if="loading" class="grid place-items-center rounded border border-slate-800 bg-slate-900/40 py-12 text-slate-400">
      Memuat produk...
    </div>

    <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <article
        v-for="product in products"
        :key="product.id"
        class="flex flex-col justify-between rounded border border-slate-800 bg-slate-900/50 p-4"
      >
        <div class="space-y-2">
          <RouterLink
            :to="{ name: 'tenant-product-detail', params: { tenant, productId: product.id } }"
            class="text-lg font-semibold text-emerald-300 hover:text-emerald-200"
          >
            {{ product.name }}
          </RouterLink>
          <p class="text-sm text-slate-400">{{ product.description ? truncate(product.description) : 'Belum ada deskripsi.' }}</p>
        </div>

        <div class="mt-4 flex items-center justify-between text-sm">
          <span class="font-semibold text-emerald-400">{{ formatCurrency(product.price) }}</span>
          <span class="text-slate-400">Stok: {{ product.stock }}</span>
        </div>

        <button
          type="button"
          class="mt-4 rounded bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 disabled:cursor-not-allowed disabled:bg-slate-700 disabled:text-slate-400"
          :disabled="!authStore.isAuthenticated || !product.is_active"
          @click="addToCart(product)"
        >
          {{ authStore.isAuthenticated ? 'Tambahkan ke Keranjang' : 'Masuk untuk Belanja' }}
        </button>
      </article>

      <p v-if="!products.length" class="col-span-full rounded border border-slate-800 bg-slate-900/60 py-10 text-center text-sm text-slate-400">
        Tidak ada produk ditemukan.
      </p>
    </div>
  </section>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api, tenantPath } from '../../api/client';
import { useAuthStore } from '../../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

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
        error.value = err.response?.data?.message ?? 'Gagal memuat produk';
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
        window.dispatchEvent(new CustomEvent('cart:updated'));
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Gagal menambahkan produk ke keranjang';
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
