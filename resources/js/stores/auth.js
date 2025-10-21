import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import { api, tenantPath } from '../api/client';

const TOKEN_KEY = 'volantis_token';
const TENANT_KEY = 'volantis_tenant';

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem(TOKEN_KEY));
    const tenant = ref(localStorage.getItem(TENANT_KEY));
    const user = ref(null);
    const userLoaded = ref(false);

    const isAuthenticated = computed(() => Boolean(token.value));
    const role = computed(() => user.value?.metadata?.role ?? null);
    const isOwner = computed(() => role.value === 'owner');
    const isCustomer = computed(() => role.value === 'customer');

    watch(token, (value) => {
        if (value) {
            api.defaults.headers.common.Authorization = `Bearer ${value}`;
            localStorage.setItem(TOKEN_KEY, value);
        } else {
            delete api.defaults.headers.common.Authorization;
            localStorage.removeItem(TOKEN_KEY);
        }
    }, { immediate: true });

    watch(tenant, (value) => {
        if (value) {
            localStorage.setItem(TENANT_KEY, value);
        } else {
            localStorage.removeItem(TENANT_KEY);
        }
    }, { immediate: true });

    function setTenant(slug) {
        if (tenant.value !== slug) {
            tenant.value = slug;
            resetSession();
        }
    }

    function clearTenant() {
        tenant.value = null;
        resetSession();
    }

    function resetSession() {
        token.value = null;
        user.value = null;
        userLoaded.value = false;
    }

    async function register(payload) {
        if (! tenant.value) {
            throw new Error('Tenant is not set.');
        }

        const { data } = await api.post(tenantPath(tenant.value, 'auth/register'), payload);
        api.defaults.headers.common.Authorization = `Bearer ${data.token}`;
        token.value = data.token;
        user.value = data.user;
        userLoaded.value = true;

        return data.user;
    }

    async function login(payload) {
        if (! tenant.value) {
            throw new Error('Tenant is not set.');
        }

        const { data } = await api.post(tenantPath(tenant.value, 'auth/login'), payload);
        api.defaults.headers.common.Authorization = `Bearer ${data.token}`;
        token.value = data.token;
        user.value = data.user;
        userLoaded.value = true;

        return data.user;
    }

    async function fetchUser() {
        if (! tenant.value || ! token.value) {
            return null;
        }

        const { data } = await api.get(tenantPath(tenant.value, 'auth/me'));
        user.value = data.user;
        userLoaded.value = true;

        return data.user;
    }

    async function logout() {
        if (tenant.value && token.value) {
            try {
                await api.post(tenantPath(tenant.value, 'auth/logout'));
            } catch (error) {
                console.warn('Failed to revoke token', error);
            }
        }

        resetSession();
    }

    return {
        tenant,
        token,
        user,
        userLoaded,
        isAuthenticated,
        role,
        isOwner,
        isCustomer,
        setTenant,
        clearTenant,
        register,
        login,
        logout,
        fetchUser,
    };
});
