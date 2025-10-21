
<script setup>
import { onBeforeUnmount, onMounted, reactive, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { api, tenantPath } from '../../api/client';

const route = useRoute();
const tenant = computed(() => route.params.tenant);

const loading = ref(false);
const error = ref('');
const cart = reactive({
    id: null,
    status: null,
    items: [],
    subtotal: 0,
    total_items: 0,
});

async function fetchCart() {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await api.get(tenantPath(tenant.value, 'cart'));
        Object.assign(cart, data);
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Failed to load cart.';
    } finally {
        loading.value = false;
    }
}

async function onQuantityChange(item, value) {
    if (Number.isNaN(value) || value < 0) {
        return;
    }

    try {
        const { data } = await api.patch(tenantPath(tenant.value, `cart/items/${item.id}`), {
            quantity: value,
        });
        Object.assign(cart, data);
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Failed to update quantity.';
    }
}

async function removeItem(item) {
    try {
        const { data } = await api.delete(tenantPath(tenant.value, `cart/items/${item.id}`));
        Object.assign(cart, data);
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Failed to remove item.';
    }
}

function formatCurrency(value) {
    return Number(value).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
}

function handleCartUpdated(event) {
    void fetchCart();
}

onMounted(() => {
    fetchCart();
    window.addEventListener('cart:updated', handleCartUpdated);
});

onBeforeUnmount(() => {
    window.removeEventListener('cart:updated', handleCartUpdated);
});
</script>
<template>
  <section class="space-y-8">
    <header class="space-y-2">
      <h1 class="text-3xl font-semibold text-slate-900">Shopping Cart</h1>
      <p class="text-sm text-slate-500">
        Manage cart items for tenant <span class="font-mono text-blue-600">{{ tenant }}</span>.
      </p>
    </header>

    <div v-if="loading" class="rounded-2xl border border-slate-200 bg-white p-6 text-slate-500 shadow-sm shadow-blue-100/20">
      Loading cart...
    </div>

    <div v-else class="space-y-6">
      <div v-if="error" class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-600">
        {{ error }}
      </div>

      <div
        v-if="!cart.items.length"
        class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-500 shadow-sm shadow-blue-100/20"
      >
        Your cart is empty. Browse the product catalog to start shopping.
      </div>

      <div v-else class="space-y-4">
        <article
          v-for="item in cart.items"
          :key="item.id"
          class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-blue-100/30 sm:flex-row sm:items-center sm:justify-between"
        >
          <div>
            <h3 class="text-lg font-semibold text-slate-900">{{ item.product.name }}</h3>
            <p class="text-xs uppercase tracking-wide text-slate-400">SKU: {{ item.product.sku }}</p>
            <p class="mt-2 text-sm text-slate-500">Price: {{ formatCurrency(item.unit_price) }}</p>
          </div>

          <div class="flex flex-wrap items-center gap-3 rounded-xl bg-slate-50 px-4 py-3">
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Quantity</label>
            <input
              :value="item.quantity"
              type="number"
              min="0"
              class="w-20 rounded-lg border border-slate-200 bg-white px-3 py-2 text-right text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
              @change="(event) => onQuantityChange(item, Number(event.target.value))"
            />
            <p class="text-sm font-semibold text-blue-600">{{ formatCurrency(item.subtotal) }}</p>
            <button
              type="button"
              class="rounded-full bg-rose-500 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-rose-600"
              @click="removeItem(item)"
            >
              Remove
            </button>
          </div>
        </article>
      </div>

      <footer class="flex flex-col items-end gap-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-blue-100/30">
        <p class="text-sm text-slate-500">Total items: {{ cart.total_items }}</p>
        <p class="text-xl font-semibold text-blue-600">Subtotal: {{ formatCurrency(cart.subtotal) }}</p>
      </footer>
    </div>
  </section>
</template>
