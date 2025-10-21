import axios from 'axios';

export const api = axios.create({
    baseURL: '/api',
    withCredentials: true,
});

export const adminApi = axios.create({
    baseURL: '/api',
    withCredentials: true,
});

export function tenantPath(tenant, path) {
    const normalizedPath = path.startsWith('/') ? path.slice(1) : path;
    return `/${tenant}/${normalizedPath}`;
}
