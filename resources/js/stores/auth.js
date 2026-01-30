import { defineStore } from 'pinia';
import { ref, computed, onMounted, onUnmounted } from 'vue';

export const useAuthStore = defineStore('auth', () => {
    // Gunakan sessionStorage agar data login terhapus otomatis saat tab/browser ditutup
    const user = ref(JSON.parse(sessionStorage.getItem('user') || 'null'));
    const token = ref(sessionStorage.getItem('auth_token') || null);
    const permissions = ref(JSON.parse(sessionStorage.getItem('permissions') || '[]'));

    const isAuthenticated = computed(() => !!token.value);
    const isSuperAdmin = computed(() => user.value?.role === 'superadmin');
    const isAdmin = computed(() => user.value?.role === 'admin');
    const isKasir = computed(() => user.value?.role === 'kasir');

    const hasPermission = (permission) => {
        // Super Admin has all permissions
        if (isSuperAdmin.value) return true;

        // Admin has all except superadmin-only permissions
        if (isAdmin.value) {
            const superadminOnly = ['users', 'activations'];
            return !superadminOnly.includes(permission);
        }

        return permissions.value.includes(permission);
    };

    const login = async (email, password) => {
        const response = await axios.post('/auth/login', { email, password });
        const { user: userData, token: authToken, permissions: userPermissions } = response.data;

        user.value = userData;
        token.value = authToken;
        permissions.value = userPermissions || [];

        sessionStorage.setItem('user', JSON.stringify(userData));
        sessionStorage.setItem('auth_token', authToken);
        sessionStorage.setItem('permissions', JSON.stringify(userPermissions || []));
        axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;

        return response.data;
    };

    const logout = async () => {
        try {
            await axios.post('/auth/logout');
        } catch (e) {
            // Ignore logout errors
        }

        user.value = null;
        token.value = null;
        permissions.value = [];
        sessionStorage.removeItem('user');
        sessionStorage.removeItem('auth_token');
        sessionStorage.removeItem('permissions');
        delete axios.defaults.headers.common['Authorization'];
    };

    // Fungsi untuk melakukan logout saat aplikasi ditutup
    const logoutOnClose = () => {
        // Gunakan navigator.sendBeacon untuk mengirim request logout
        // karena beforeunload tidak menjamin async request berhasil
        if (token.value) {
            const logoutUrl = '/api/auth/logout-beacon';
            const data = new Blob([JSON.stringify({})], { type: 'application/json' });

            // Set header authorization via URL param atau gunakan beacon
            navigator.sendBeacon(logoutUrl + '?_token=' + encodeURIComponent(token.value), data);

            // Bersihkan sessionStorage
            sessionStorage.removeItem('user');
            sessionStorage.removeItem('auth_token');
            sessionStorage.removeItem('permissions');
        }
    };

    // Setup event listener untuk auto-logout saat tab/browser ditutup
    const setupAutoLogout = () => {
        window.addEventListener('beforeunload', logoutOnClose);
    };

    // Cleanup event listener
    const cleanupAutoLogout = () => {
        window.removeEventListener('beforeunload', logoutOnClose);
    };

    const updateUser = (userData) => {
        user.value = userData;
        sessionStorage.setItem('user', JSON.stringify(userData));
    };

    const setUser = (userData) => {
        user.value = userData;
        sessionStorage.setItem('user', JSON.stringify(userData));
    };

    const setToken = (authToken) => {
        token.value = authToken;
        sessionStorage.setItem('auth_token', authToken);
        axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
    };

    const setPermissions = (userPermissions) => {
        permissions.value = userPermissions || [];
        sessionStorage.setItem('permissions', JSON.stringify(userPermissions || []));
    };

    return {
        user,
        token,
        permissions,
        isAuthenticated,
        isSuperAdmin,
        isAdmin,
        isKasir,
        hasPermission,
        login,
        logout,
        updateUser,
        setUser,
        setToken,
        setPermissions,
        setupAutoLogout,
        cleanupAutoLogout
    };
});

