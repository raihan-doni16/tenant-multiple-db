<template>
  <section class="space-y-6">
    <header class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-3xl font-semibold text-emerald-400">Dashboard Produk</h1>
        <p class="text-sm text-slate-400">Kelola produk untuk tenant <span class="font-mono text-emerald-300">{{ tenant }}</span>.</p>
      </div>
      <button
        type="button"
        class="rounded border border-emerald-500 px-4 py-2 text-sm font-semibold text-emerald-400 hover:bg-emerald-500 hover:text-slate-950"
        @click="resetForm"
      >
        Produk Baru
      </button>
    </header>

    <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-emerald-300">Daftar Produk</h2>
          <span class="text-xs uppercase tracking-widest text-slate-500">{{ products.length }} produk</span>
        </div>

        <div class="overflow-hidden rounded border border-slate-800">
          <table class="min-w-full divide-y divide-slate-800 text-sm">
            <thead class="bg-slate-900/60 text-xs uppercase tracking-wide text-slate-400">
              <tr>
                <th class="px-4 py-2 text-left">Nama</th>
                <th class="px-4 py-2 text-left">Harga</th>
                <th class="px-4 py-2 text-left">Stok</th>
                <th class="px-4 py-2 text-left">Status</th>
                <th class="px-4 py-2 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
              <tr v-for="product in products" :key="product.id" class="hover:bg-slate-900/40">
                <td class="px-4 py-2">
                  <p class="font-semibold text-emerald-300">{{ product.name }}</p>
                  <p class="text-xs text-slate-500">SKU: {{ product.sku }}</p>
                </td>
                <td class="px-4 py-2 text-sm">{{ formatCurrency(product.price) }}</td>
                <td class="px-4 py-2">{{ product.stock }}</td>
                <td class="px-4 py-2 text-xs uppercase tracking-wide" :class="product.is_active ? 'text-emerald-400' : 'text-slate-500'">
                  {{ product.is_active ? 'Aktif' : 'Draft' }}
                </td>
                <td class="px-4 py-2 text-right space-x-2">
                  <button
                    type="button"
                    class="rounded bg-slate-800 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-200 hover:bg-emerald-500 hover:text-slate-950"
                    @click="editProduct(product)"
                  >
                    Edit
                  </button>
                  <button
                    type="button"
                    class="rounded bg-rose-500 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-950 hover:bg-rose-400"
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

      <aside class="space-y-4 rounded border border-slate-800 bg-slate-900/60 p-4">
        <h2 class="text-lg font-semibold text-emerald-300">{{ form.id ? 'Perbarui Produk' : 'Produk Baru' }}</h2>

        <form class="grid gap-4" @submit.prevent="saveProduct">
          <div class="grid gap-1">
            <label class="text-xs uppercase tracking-wide text-slate-400">Nama</label>
            <input v-model="form.name" type="text" required class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
          </div>
          <div class="grid gap-1">
            <label class="text-xs uppercase tracking-wide text-slate-400">SKU</label>
            <input v-model="form.sku" type="text" class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
          </div>
          <div class="grid gap-1">
            <label class="text-xs uppercase tracking-wide text-slate-400">Slug</label>
            <input v-model="form.slug" type="text" class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
          </div>
          <div class="grid gap-1">
            <label class="text-xs uppercase tracking-wide text-slate-400">Deskripsi</label>
            <textarea v-model="form.description" rows="3" class="rounded border border-slate-700 bg-slate-950 px-3 py-2"></textarea>
          </div>
          <div class="grid gap-1 sm:grid-cols-2 sm:gap-4">
            <div class="grid gap-1">
              <label class="text-xs uppercase tracking-wide text-slate-400">Harga</label>
              <input v-model.number="form.price" type="number" min="0" step="0.01" required class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
            </div>
            <div class="grid gap-1">
              <label class="text-xs uppercase tracking-wide text-slate-400">Stok</label>
              <input v-model.number="form.stock" type="number" min="0" required class="rounded border border-slate-700 bg-slate-950 px-3 py-2" />
            </div>
          </div>
          <label class="flex items-center gap-2 text-xs uppercase tracking-wide text-slate-400">
            <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border border-slate-700 bg-slate-950" />
            Produk aktif
          </label>

          <button type="submit" class="rounded bg-emerald-500 px-4 py-2 font-semibold text-slate-950 hover:bg-emerald-400" :disabled="saving">
            {{ saving ? 'Menyimpan...' : form.id ? 'Perbarui' : 'Simpan' }}
          </button>
        </form>

        <p v-if="formError" class="text-sm text-rose-400">{{ formError }}</p>
        <p v-if="success" class="text-sm text-emerald-400">Perubahan disimpan.</p>
      </aside>
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { api, tenantPath } from '../../api/client';

const route = useRoute();
const tenant = computed(() => route.params.tenant);

const products = ref([]);
const saving = ref(false);
const formError = ref('');
const success = ref(false);

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
    success.value = false;
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
    success.value = false;
}

async function saveProduct() {
    saving.value = true;
    formError.value = '';
    success.value = false;

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
            const { data } = await api.patch(tenantPath(tenant.value, `products/${form.id}`), payload);
            success.value = true;
        } else {
            const { data } = await api.post(tenantPath(tenant.value, 'products'), payload);
            success.value = true;
            editProduct(data);
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
