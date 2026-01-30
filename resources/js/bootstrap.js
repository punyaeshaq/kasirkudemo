import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
// Dynamic API base URL: production (Vercel) vs local (XAMPP)
// In Vercel, routes are registered without /api prefix
// In local XAMPP, routes have /api prefix under /kasirku/public
const isVercel = window.location.hostname.includes('vercel.app');
window.axios.defaults.baseURL = isVercel ? '/' : '/kasirku/public/api';
window.axios.defaults.timeout = 10000; // 10 seconds timeout

// Add auth token to requests (gunakan sessionStorage untuk auto-logout saat tab/browser ditutup)
const token = sessionStorage.getItem('auth_token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Response interceptor for handling auth errors
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            sessionStorage.removeItem('auth_token');
            sessionStorage.removeItem('user');
            sessionStorage.removeItem('permissions');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);
