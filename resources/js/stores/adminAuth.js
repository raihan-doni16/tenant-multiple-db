import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import { adminApi } from '../api/client';

const TOKEN_KEY = 'volantis_admin_token';

export const useAdminAuthStore = defineStore('admin-auth', () => {
    const token = ref(localStorage.getItem(TOKEN_KEY));
    const user = ref(null);
    const userLoaded = ref(false);

    const isAuthenticated = computed(() => Boolean(token.value));

    watch(token, (value) => {
        if (value) {
            adminApi.defaults.headers.common.Authorization = `Bearer ${value}`;
            localStorage.setItem(TOKEN_KEY, value);
        } else {
            delete adminApi.defaults.headers.common.Authorization;
            localStorage.removeItem(TOKEN_KEY);
        }
    }, { immediate: true });

    async function login(payload) {
        const { data } = await adminApi.post('/admin/auth/login', payload);
        token.value = data.token;
        user.value = data.user;
        userLoaded.value = true;
        return user.value;
    }

    async function fetchUser() {
        if (!token.value) {
            return null;
        }

        const { data } = await adminApi.get('/admin/auth/me');
        user.value = data.user;
        userLoaded.value = true;
        return user.value;
    }

    async function logout() {
        if (token.value) {
            try {
                await adminApi.post('/admin/auth/logout');
            } catch (error) {
                console.warn('Failed to revoke admin token', error);
            }
        }

        token.value = null;
        user.value = null;
        userLoaded.value = false;
    }

    return {
        token,
        user,
        userLoaded,
        isAuthenticated,
        login,
        fetchUser,
        logout,
    };
});
