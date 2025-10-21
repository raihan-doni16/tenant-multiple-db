<template>
  <section class="space-y-8">
    <header class="flex flex-col gap-3 rounded-2xl bg-white p-6 shadow-sm shadow-blue-100/30 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-3xl font-semibold text-slate-900">Dashboard Produk</h1>
        <p class="text-sm text-slate-500">
          Kelola produk untuk tenant <span class="font-mono text-blue-600">{{ tenant }}</span>.
        </p>
      </div>
      <button
        type="button"
        class="rounded-full border border-blue-200 bg-white px-5 py-2 text-sm font-semibold text-blue-600 transition hover:border-blue-400 hover:text-blue-700"
        @click="resetForm"
      >
        Produk Baru
      </button>
    </header>

    <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-slate-900">Daftar Produk</h2>
          <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-blue-600">
            {{ products.length }} produk
          </span>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-blue-100/30">
          <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3 text-left">Nama</th>
                <th class="px-4 py-3 text-left">Harga</th>
                <th class="px-4 py-3 text-left">Stok</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="product in products" :key="product.id" class="transition hover:bg-blue-50/50">
                <td class="px-4 py-3">
                  <p class="font-semibold text-slate-900">{{ product.name }}</p>
                  <p class="text-xs uppercase tracking-wide text-slate-400">SKU: {{ product.sku }}</p>
                </td>
                <td class="px-4 py-3 text-sm text-slate-500">{{ formatCurrency(product.price) }}</td>
                <td class="px-4 py-3 text-slate-500">{{ product.stock }}</td>
                <td
                  class="px-4 py-3 text-xs font-semibold uppercase tracking-wide"
                  :class="product.is_active ? 'text-blue-600' : 'text-slate-400'"
                >
                  {{ product.is_active ? 'Aktif' : 'Draft' }}
                </td>
                <td class="px-4 py-3 text-right space-x-2">
                  <button
                    type="button"
                    class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-600 transition hover:bg-blue-100 hover:text-blue-700"
                    @click="editProduct(product)"
                  >
                    Edit
                  </button>
                  <button
                    type="button"
                    class="rounded-full bg-rose-500 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-rose-600"
                    @click="deleteProduct(product)"
                  >
                    Hapus
                  </button>
                </td>
              </tr>
              <tr v-if="!products.length">
                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-400">Belum ada produk.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <aside class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-lg shadow-blue-100/40">
        <h2 class="text-lg font-semibold text-slate-900">{{ form.id ? 'Perbarui Produk' : 'Produk Baru' }}</h2>

        <form class="grid gap-4" @submit.prevent="saveProduct">
          <div class="grid gap-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nama</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
            />
          </div>
          <div class="grid gap-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">SKU</label>
            <input
              v-model="form.sku"
              type="text"
              class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
            />
          </div>
          <div class="grid gap-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Slug</label>
            <input
              v-model="form.slug"
              type="text"
              class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
            />
          </div>
          <div class="grid gap-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Deskripsi</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
            ></textarea>
          </div>
          <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-1">
              <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Harga</label>
              <input
                v-model.number="form.price"
                type="number"
                min="0"
                step="0.01"
                required
                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
              />
            </div>
            <div class="grid gap-1">
              <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Stok</label>
              <input
                v-model.number="form.stock"
                type="number"
                min="0"
                required
                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
              />
            </div>
          </div>
          <label class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border border-slate-300 text-blue-600 focus:ring-blue-500" />
            Produk aktif
          </label>

          <button
            type="submit"
            class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
            :disabled="saving"
          >
            {{ saving ? 'Menyimpan...' : form.id ? 'Perbarui' : 'Simpan' }}
          </button>
        </form>

        <p v-if="formError" class="text-sm text-rose-500">{{ formError }}</p>
      </aside>
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { api, tenantPath } from '../../api/client';
import { useFeedbackStore } from '../../stores/feedback';

const route = useRoute();
const tenant = computed(() => route.params.tenant);

const products = ref([]);
const saving = ref(false);
const formError = ref('');
const feedbackStore = useFeedbackStore();

const form = reactive({
    id: null,
    name: '',
    sku: '',
    slug: '',
    description: '',
    price: 0,
    stock: 0,
    is_active: true,
    attributes: {},
});

async function fetchProducts() {
    const { data } = await api.get(tenantPath(tenant.value, 'products'), {
        params: {
            active: false,
            per_page: 100,
        },
    });

    products.value = data.data ?? data;
}

function editProduct(product) {
    Object.assign(form, {
        id: product.id,
        name: product.name,
        sku: product.sku,
        slug: product.slug,
        description: product.description,
        price: Number(product.price),
        stock: product.stock,
        is_active: product.is_active,
    });
    formError.value = '';
}

function resetForm() {
    Object.assign(form, {
        id: null,
        name: '',
        sku: '',
        slug: '',
        description: '',
        price: 0,
        stock: 0,
        is_active: true,
        attributes: {},
    });
    formError.value = '';
}

async function saveProduct() {
    saving.value = true;
    formError.value = '';
    const isNewProduct = !form.id;

    const payload = {
        name: form.name,
        sku: form.sku || null,
        slug: form.slug || null,
        description: form.description || null,
        price: form.price,
        stock: form.stock,
        is_active: form.is_active,
        attributes: form.attributes,
    };

    try {
        if (form.id) {
            await api.patch(tenantPath(tenant.value, `products/${form.id}`), payload);
        } else {
            const { data } = await api.post(tenantPath(tenant.value, 'products'), payload);
            editProduct(data);
        }

        if (isNewProduct) {
            feedbackStore.showSuccess('Produk baru berhasil ditambahkan.');
        }

        await fetchProducts();
    } catch (err) {
        formError.value = err.response?.data?.message ?? 'Gagal menyimpan produk.';
    } finally {
        saving.value = false;
    }
}

async function deleteProduct(product) {
    if (!confirm(`Hapus produk ${product.name}?`)) {
        return;
    }

    try {
        await api.delete(tenantPath(tenant.value, `products/${product.id}`));
        if (form.id === product.id) {
            resetForm();
        }
        await fetchProducts();
    } catch (err) {
        formError.value = err.response?.data?.message ?? 'Gagal menghapus produk.';
    }
}

function formatCurrency(value) {
    return Number(value).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
}

onMounted(fetchProducts);
</script>
