import { createRouter, createWebHistory } from 'vue-router';

import AdminLoginView from '../views/admin/AdminLoginView.vue';
import TenantManagementView from '../views/admin/TenantManagementView.vue';
import TenantCartView from '../views/tenant/TenantCartView.vue';
import TenantDashboardProductsView from '../views/tenant/TenantDashboardProductsView.vue';
import TenantLoginView from '../views/tenant/TenantLoginView.vue';
import TenantProductDetailView from '../views/tenant/TenantProductDetailView.vue';
import TenantProductsView from '../views/tenant/TenantProductsView.vue';
import TenantRegisterView from '../views/tenant/TenantRegisterView.vue';

import { useAuthStore } from '../stores/auth';
import { useAdminAuthStore } from '../stores/adminAuth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            redirect: '/admin/tenants',
        },
        {
            path: '/admin/login',
            name: 'admin-login',
            component: AdminLoginView,
        },
        {
            path: '/admin/tenants',
            name: 'admin-tenants',
            component: TenantManagementView,
            meta: {
                requiresAdmin: true,
            },
        },
        {
            path: '/:tenant/login',
            name: 'tenant-login',
            component: TenantLoginView,
        },
        {
            path: '/:tenant/register',
            name: 'tenant-register',
            component: TenantRegisterView,
        },
        {
            path: '/:tenant',
            redirect: { name: 'tenant-products' },
        },
        {
            path: '/:tenant/products',
            name: 'tenant-products',
            component: TenantProductsView,
            meta: {
                requiresAuth: true,
            },
        },
        {
            path: '/:tenant/products/:productId',
            name: 'tenant-product-detail',
            component: TenantProductDetailView,
            props: true,
            meta: {
                requiresAuth: true,
            },
        },
        {
            path: '/:tenant/cart',
            name: 'tenant-cart',
            component: TenantCartView,
            meta: {
                requiresAuth: true,
            },
        },
        {
            path: '/:tenant/dashboard/products',
            name: 'tenant-dashboard-products',
            component: TenantDashboardProductsView,
            meta: {
                requiresAuth: true,
                requiresOwner: true,
            },
        },
    ],
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    const adminAuthStore = useAdminAuthStore();
    const tenant = to.params.tenant ? String(to.params.tenant) : null;

    if (to.path.startsWith('/admin')) {
        if (to.name !== 'admin-login' && adminAuthStore.isAuthenticated && !adminAuthStore.userLoaded) {
            try {
                await adminAuthStore.fetchUser();
            } catch (error) {
                console.error(error);
                await adminAuthStore.logout();
                return next({ name: 'admin-login' });
            }
        }

        if (to.meta.requiresAdmin && !adminAuthStore.isAuthenticated) {
            return next({ name: 'admin-login' });
        }

        if (to.name === 'admin-login' && adminAuthStore.isAuthenticated) {
            return next({ name: 'admin-tenants' });
        }
    }

    if (tenant) {
        authStore.setTenant(tenant);
        try {
            if (authStore.isAuthenticated && !authStore.userLoaded) {
                await authStore.fetchUser();
            }
        } catch (error) {
            console.error(error);
            await authStore.logout();
        }
    } else {
        authStore.clearTenant();
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        if (tenant) {
            return next({ name: 'tenant-login', params: { tenant } });
        }

        return next({ name: 'admin-login' });
    }

    if (to.meta.requiresOwner && !authStore.isOwner) {
        if (tenant) {
            return next({ name: 'tenant-products', params: { tenant } });
        }

        return next({ name: 'admin-login' });
    }

    return next();
});

export default router;
