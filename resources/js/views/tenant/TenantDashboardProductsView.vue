<template>
  <section class="space-y-8">
    <header class="flex flex-col gap-3 rounded-2xl bg-white p-6 shadow-sm shadow-blue-100/30 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-3xl font-semibold text-slate-900">Product Dashboard</h1>
        <p class="text-sm text-slate-500">
          Manage products for tenant <span class="font-mono text-blue-600">{{ tenant }}</span>.
        </p>
      </div>
      <button
        type="button"
        class="rounded-full border border-blue-200 bg-white px-5 py-2 text-sm font-semibold text-blue-600 transition hover:border-blue-400 hover:text-blue-700"
        @click="resetForm"
      >
        New Product
      </button>
    </header>

    <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
      <div class="space-y-4">
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-blue-100/30 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="text-lg font-semibold text-slate-900">Product List</h2>
            <p class="text-xs text-slate-500">Manage existing products, search, and browse by page.</p>
          </div>
          <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-blue-600">
            {{ pagination.total }} products
          </span>
        </div>

        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-blue-100/30 lg:flex-row lg:items-center lg:justify-between">
          <div class="w-full lg:max-w-xs">
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search Products</label>
            <input
              v-model="search"
              type="search"
              placeholder="Search by name, SKU, or slug..."
              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
              @input="onSearchChange"
            />
          </div>
          <div class="flex w-full flex-col gap-2 lg:w-auto lg:flex-row lg:items-center lg:gap-3">
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 lg:text-right">Rows per page</label>
            <select
              v-model.number="pagination.per_page"
              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 lg:w-32"
              @change="onPerPageChange"
            >
              <option v-for="option in perPageOptions" :key="option" :value="option">
                {{ option }}
              </option>
            </select>
          </div>
        </div>

        <div v-if="listError" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600 shadow-sm shadow-rose-100/30">
          {{ listError }}
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-blue-100/30">
          <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-left">Price</th>
                <th class="px-4 py-3 text-left">Stock</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="listLoading">
                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-400">Loading products...</td>
              </tr>
              <template v-else>
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
                    {{ product.is_active ? 'Active' : 'Draft' }}
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
                      Delete
                    </button>
                  </td>
                </tr>
                <tr v-if="!products.length">
                  <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-400">No products found.</td>
                </tr>
              </template>
            </tbody>
          </table>
          <div
            v-if="pagination.total > 0 && !listLoading"
            class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between"
          >
            <span>Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }} products</span>
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
      </div>

      <aside class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-lg shadow-blue-100/40">
        <h2 class="text-lg font-semibold text-slate-900">{{ form.id ? 'Update Product' : 'New Product' }}</h2>

        <form class="grid gap-4" @submit.prevent="saveProduct">
          <div class="grid gap-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Name</label>
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
              :value="form.sku || ''"
              type="text"
              readonly
              placeholder="Generated automatically"
              class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-500"
            />
            <p class="text-xs text-slate-400">SKU is generated automatically when the product is saved.</p>
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
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
            ></textarea>
          </div>
         
            <div class="grid gap-1">
              <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Price</label>
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
              <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Stock</label>
              <input
                v-model.number="form.stock"
                type="number"
                min="0"
                required
                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
              />
            </div>
         
          <label class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border border-slate-300 text-blue-600 focus:ring-blue-500" />
            Active product
          </label>

          <button
            type="submit"
            class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
            :disabled="saving"
          >
            {{ saving ? 'Saving...' : form.id ? 'Update' : 'Save' }}
          </button>
        </form>

        <p v-if="formError" class="text-sm text-rose-500">{{ formError }}</p>
      </aside>
    </div>
  </section>
</template>

<script setup>
import { onBeforeUnmount, onMounted, reactive, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { api, tenantPath } from '../../api/client';
import { useFeedbackStore } from '../../stores/feedback';

const route = useRoute();
const tenant = computed(() => route.params.tenant);

const products = ref([]);
const listLoading = ref(false);
const listError = ref('');
const saving = ref(false);
const formError = ref('');
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
const perPageOptions = [5, 10, 25, 50, 100];
let searchTimer;

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

async function fetchProducts(page = pagination.current_page) {
    listLoading.value = true;
    listError.value = '';

    try {
        const { data } = await api.get(tenantPath(tenant.value, 'products'), {
            params: {
                active: false,
                page,
                per_page: pagination.per_page,
                search: search.value || undefined,
            },
        });

        products.value = data.data ?? [];
        Object.assign(pagination, {
            current_page: data.current_page ?? 1,
            last_page: data.last_page ?? 1,
            per_page: Number(data.per_page ?? pagination.per_page),
            total: data.total ?? (Array.isArray(data.data) ? data.data.length : 0),
            from: data.from ?? 0,
            to: data.to ?? 0,
        });
    } catch (err) {
        listError.value = err.response?.data?.message ?? 'Failed to load products.';
        products.value = [];
    } finally {
        listLoading.value = false;
    }
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
            feedbackStore.showSuccess('New product added successfully.');
        }

        await fetchProducts(isNewProduct ? 1 : pagination.current_page);
    } catch (err) {
        formError.value = err.response?.data?.message ?? 'Failed to save product.';
    } finally {
        saving.value = false;
    }
}

async function deleteProduct(product) {
    if (!confirm(`Delete product ${product.name}?`)) {
        return;
    }

    try {
        await api.delete(tenantPath(tenant.value, `products/${product.id}`));
        if (form.id === product.id) {
            resetForm();
        }
        await fetchProducts(pagination.current_page);
        if (!products.value.length && pagination.current_page > 1) {
            await fetchProducts(pagination.current_page - 1);
        }
    } catch (err) {
        formError.value = err.response?.data?.message ?? 'Failed to delete product.';
    }
}

function changePage(page) {
    if (page < 1 || page > pagination.last_page || page === pagination.current_page) {
        return;
    }

    void fetchProducts(page);
}

function onPerPageChange() {
    void fetchProducts(1);
}

function onSearchChange() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        void fetchProducts(1);
    }, 300);
}

function formatCurrency(value) {
    return Number(value).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
}

onMounted(() => {
    void fetchProducts(1);
});

onBeforeUnmount(() => {
    clearTimeout(searchTimer);
});
</script>
