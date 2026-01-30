import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useActivationStore = defineStore('activation', () => {
    const isActivated = ref(false);
    const expiredAt = ref(null);
    const machineId = ref(null);

    /**
     * Generate or get Machine ID
     * Menggunakan kombinasi user agent, screen resolution, dan random ID yang disimpan
     */
    const getMachineId = async () => {
        // Cek apakah sudah ada machine ID tersimpan
        let storedId = localStorage.getItem('kasirku_machine_id');

        if (!storedId) {
            // Generate machine ID baru berdasarkan hardware/browser fingerprint
            const fingerprint = await generateFingerprint();
            storedId = fingerprint;
            localStorage.setItem('kasirku_machine_id', storedId);
        }

        machineId.value = storedId;
        return storedId;
    };

    /**
     * Generate fingerprint untuk Machine ID
     */
    const generateFingerprint = async () => {
        const components = [];

        // Screen info
        components.push(screen.width + 'x' + screen.height);
        components.push(screen.colorDepth);

        // Timezone
        components.push(Intl.DateTimeFormat().resolvedOptions().timeZone);

        // Language
        components.push(navigator.language);

        // Platform
        components.push(navigator.platform);

        // Hardware concurrency
        components.push(navigator.hardwareConcurrency || 'unknown');

        // Random component removed for stability (agar ID tidak berubah saat cache dihapus)
        // const randomPart = Math.random().toString(36).substring(2, 10).toUpperCase();
        // components.push(randomPart);

        // Hash semua komponen
        const data = components.join('|');
        const hash = await hashString(data);

        // Format sebagai Machine ID (12 karakter uppercase)
        return hash.substring(0, 12).toUpperCase();
    };

    /**
     * Simple hash function menggunakan Web Crypto API
     */
    const hashString = async (str) => {
        const encoder = new TextEncoder();
        const data = encoder.encode(str);

        try {
            const hashBuffer = await crypto.subtle.digest('SHA-256', data);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        } catch (e) {
            // Fallback untuk browser yang tidak support crypto.subtle
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash;
            }
            return Math.abs(hash).toString(16).padStart(12, '0');
        }
    };

    /**
     * Cek status aktivasi
     */
    const checkActivation = async (mId) => {
        try {
            const response = await axios.get('/activation/status', {
                params: { machine_id: mId }
            });

            isActivated.value = response.data.activated;
            expiredAt.value = response.data.expired_at;

            // Fix: Persist to localStorage so Router Guard knows we are active
            if (response.data.activated) {
                localStorage.setItem('kasirku_activated', 'true');
                if (response.data.expired_at) {
                    localStorage.setItem('kasirku_expired_at', response.data.expired_at);
                }
            } else {
                // If server says not activated, clear local logic
                localStorage.removeItem('kasirku_activated');
                localStorage.removeItem('kasirku_expired_at');
            }

            return response.data.activated;
        } catch (e) {
            console.error('Failed to check activation:', e);
            return false;
        }
    };

    /**
     * Aktivasi dengan kode
     */
    const activate = async (mId, code) => {
        const response = await axios.post('/activation/activate', {
            machine_id: mId,
            activation_code: code
        });

        if (response.data.success) {
            isActivated.value = true;
            expiredAt.value = response.data.expired_at;

            // Simpan status aktivasi di localStorage
            localStorage.setItem('kasirku_activated', 'true');
            localStorage.setItem('kasirku_expired_at', response.data.expired_at);
        }

        return response.data;
    };

    /**
     * Cek apakah sudah aktivasi (dari localStorage dulu, lalu verify ke server)
     */
    const isAppActivated = async () => {
        const localActivated = localStorage.getItem('kasirku_activated');
        const localExpired = localStorage.getItem('kasirku_expired_at');

        if (localActivated === 'true' && localExpired) {
            // Cek expired
            const now = new Date().toISOString().split('T')[0];
            if (now <= localExpired) {
                isActivated.value = true;
                expiredAt.value = localExpired;
                return true;
            }
        }

        // Verify dengan server
        const mId = await getMachineId();
        return await checkActivation(mId);
    };

    /**
     * Reset aktivasi (untuk testing)
     */
    const resetActivation = () => {
        localStorage.removeItem('kasirku_activated');
        localStorage.removeItem('kasirku_expired_at');
        isActivated.value = false;
        expiredAt.value = null;
    };

    /**
     * Start periodic activation check
     * Cek status aktivasi ke server setiap 5 detik (Instant Check)
     */
    let checkInterval = null;

    const startPeriodicCheck = () => {
        // Clear any existing interval
        if (checkInterval) {
            clearInterval(checkInterval);
        }

        // Check every 5 seconds for instant revocation detection
        checkInterval = setInterval(async () => {
            const mId = localStorage.getItem('kasirku_machine_id');
            if (!mId) return;

            try {
                const response = await axios.get('/activation/status', {
                    params: { machine_id: mId }
                });

                // Check if not activated or expired
                if (!response.data.activated) {
                    const reason = response.data.expired ? 'Lisensi sudah expired' : 'Lisensi dicabut atau tidak valid';
                    handleRevocation(reason);
                }
            } catch (e) {
                console.error('Periodic activation check failed:', e);
                // Don't auto-logout on network errors, only on explicit revocation
            }
        }, 5000); // 5 seconds
    };

    /**
     * Handle Revocation Action - Force logout and redirect to activation page
     */
    const handleRevocation = async (reason = 'Lisensi tidak valid') => {
        console.warn('License revoked or expired. Forcing logout...', reason);

        // Stop the periodic check to prevent multiple redirects
        if (checkInterval) {
            clearInterval(checkInterval);
            checkInterval = null;
        }

        // Reset activation status
        resetActivation();

        // Try to call logout API to clear server session
        try {
            await axios.post('/logout');
        } catch (e) {
            // Ignore errors, we're logging out anyway
            console.warn('Logout API call failed during revocation:', e);
        }

        // Clear all session data
        sessionStorage.removeItem('user');
        sessionStorage.removeItem('auth_token');
        sessionStorage.removeItem('permissions');

        // Save reason to sessionStorage so Activation page can display it
        sessionStorage.setItem('activation_reason', reason);

        // Show alert to user
        alert(`⚠️ ${reason}\n\nAnda akan dialihkan ke halaman aktivasi untuk memasukkan kode baru.`);

        // Redirect to activation page
        window.location.href = '/kasirku/public/activation';
    };

    /**
     * Axios Interceptor IDs
     */
    let reqInterceptor = null;
    let resInterceptor = null;

    /**
     * Setup Axios Interceptors for Global Protection
     */
    const setupInterceptors = () => {
        // Avoid duplicate interceptors
        if (reqInterceptor !== null || resInterceptor !== null) return;

        // REQUEST: Attach X-Machine-ID header
        reqInterceptor = axios.interceptors.request.use(async (config) => {
            let mId = localStorage.getItem('kasirku_machine_id');

            // If ID is missing (first load), try to get/generate it
            if (!mId) {
                try {
                    mId = await getMachineId();
                } catch (e) {
                    console.error('Failed to generate Machine ID in interceptor', e);
                }
            }

            if (mId) {
                config.headers['X-Machine-ID'] = mId;
            }
            return config;
        });

        // RESPONSE: Handle license errors from middleware
        resInterceptor = axios.interceptors.response.use(
            (response) => response,
            (error) => {
                if (error.response && error.response.status === 403) {
                    const errData = error.response.data;
                    if (errData.error === 'LICENSE_REVOKED') {
                        handleRevocation('Lisensi dicabut oleh admin');
                    } else if (errData.error === 'LICENSE_NOT_FOUND') {
                        handleRevocation('Machine ID belum teraktivasi');
                    } else if (errData.error === 'LICENSE_EXPIRED') {
                        handleRevocation('Lisensi sudah expired pada ' + (errData.expired_at || ''));
                    } else if (errData.error === 'LICENSE_MISSING_ID') {
                        handleRevocation('Machine ID tidak ditemukan');
                    }
                }
                return Promise.reject(error);
            }
        );
    };

    /**
     * Cleanup Interceptors
     */
    const cleanupInterceptors = () => {
        if (reqInterceptor !== null) {
            axios.interceptors.request.eject(reqInterceptor);
            reqInterceptor = null;
        }
        if (resInterceptor !== null) {
            axios.interceptors.response.eject(resInterceptor);
            resInterceptor = null;
        }
    };

    /**
     * Stop periodic check
     */
    const stopPeriodicCheck = () => {
        if (checkInterval) {
            clearInterval(checkInterval);
            checkInterval = null;
        }
        cleanupInterceptors();
    };

    return {
        isActivated,
        expiredAt,
        machineId,
        getMachineId,
        checkActivation,
        activate,
        isAppActivated,
        resetActivation,
        startPeriodicCheck,
        stopPeriodicCheck,
        setupInterceptors
    };
});
