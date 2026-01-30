<template>
    <div>
        <!-- Tabs -->
        <div class="flex mb-6 border-b border-dark-200 dark:border-dark-700">
            <button 
                @click="loginMode = 'email'"
                :class="[
                    'flex-1 py-3 text-center font-medium transition-colors',
                    loginMode === 'email' 
                        ? 'text-primary-500 border-b-2 border-primary-500' 
                        : 'text-dark-500 hover:text-dark-700 dark:text-dark-400'
                ]"
            >
                📧 Email
            </button>
            <button 
                @click="loginMode = 'qrcode'"
                :class="[
                    'flex-1 py-3 text-center font-medium transition-colors',
                    loginMode === 'qrcode' 
                        ? 'text-primary-500 border-b-2 border-primary-500' 
                        : 'text-dark-500 hover:text-dark-700 dark:text-dark-400'
                ]"
            >
                📱 QR Code
            </button>
        </div>

        <!-- Email Login Form -->
        <div v-if="loginMode === 'email'">
            <h2 class="text-2xl font-bold text-dark-900 dark:text-white mb-6">Login dengan Email</h2>
            
            <form @submit.prevent="handleLogin" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Email</label>
                    <input 
                        v-model="form.email" 
                        type="email" 
                        class="input" 
                        placeholder="kasirku@kasirku.com"
                        required
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Password</label>
                    <input 
                        v-model="form.password" 
                        type="password" 
                        class="input" 
                        placeholder="••••••••"
                        required
                    />
                </div>
                
                <div v-if="error" class="p-3 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-400 text-sm">
                    {{ error }}
                </div>
                
                <button 
                    type="submit" 
                    class="btn-primary w-full"
                    :disabled="loading"
                >
                    <span v-if="loading" class="spinner mr-2"></span>
                    {{ loading ? 'Memproses...' : 'Masuk' }}
                </button>
            </form>
            
            <div class="mt-6 pt-6 border-t border-dark-200 dark:border-dark-600">
                <p class="text-sm text-dark-500 dark:text-dark-400 text-center">
                    Selamat datang di Kasirku, Login dengan email atau QR Code.
                </p>
            </div>
        </div>

        <!-- QR Code Login -->
        <div v-else>
            <h2 class="text-2xl font-bold text-dark-900 dark:text-white mb-4 text-center">Login dengan QR Code</h2>
            <p class="text-dark-500 dark:text-dark-400 text-center mb-6">
                Scan QR Code login Anda untuk masuk
            </p>

            <div v-if="!scannerActive" class="text-center">
                <button 
                    @click="startScanner" 
                    class="btn-primary px-8 py-3"
                >
                    📷 Mulai Scan
                </button>
            </div>

            <div v-else class="space-y-4">
                <div id="qr-reader" class="mx-auto rounded-xl overflow-hidden border-2 border-dark-200 dark:border-dark-700"></div>
                
                <button 
                    @click="stopScanner" 
                    class="btn-ghost w-full"
                >
                    ❌ Batal
                </button>
            </div>

            <div v-if="qrError" class="mt-4 p-3 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-400 text-sm text-center">
                {{ qrError }}
            </div>

            <div v-if="qrLoading" class="mt-4 text-center">
                <div class="spinner mx-auto mb-2"></div>
                <p class="text-dark-500">Memproses login...</p>
            </div>

            <div class="mt-6 pt-6 border-t border-dark-200 dark:border-dark-600">
                <p class="text-sm text-dark-500 dark:text-dark-400 text-center">
                    💡 Minta Admin untuk membuat QR Code login Anda
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { Html5Qrcode } from 'html5-qrcode';

const router = useRouter();
const authStore = useAuthStore();

const loginMode = ref('email');
const form = reactive({
    email: '',
    password: ''
});

const loading = ref(false);
const error = ref('');

// QR Scanner state
const scannerActive = ref(false);
const qrError = ref('');
const qrLoading = ref(false);
let html5QrScanner = null;

const handleLogin = async () => {
    loading.value = true;
    error.value = '';
    
    try {
        await authStore.login(form.email, form.password);
        router.push({ name: 'welcome' });
    } catch (e) {
        error.value = e.response?.data?.message || 'Email atau password salah';
    } finally {
        loading.value = false;
    }
};

const startScanner = async () => {
    scannerActive.value = true;
    qrError.value = '';
    
    await new Promise(resolve => setTimeout(resolve, 100));
    
    try {
        html5QrScanner = new Html5Qrcode('qr-reader');
        await html5QrScanner.start(
            { facingMode: 'environment' },
            {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            },
            onQrScanSuccess,
            () => {} // Ignore scan failures
        );
    } catch (e) {
        qrError.value = 'Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.';
        scannerActive.value = false;
    }
};

const stopScanner = async () => {
    if (html5QrScanner) {
        try {
            await html5QrScanner.stop();
            html5QrScanner.clear();
        } catch (e) {}
        html5QrScanner = null;
    }
    scannerActive.value = false;
};

const onQrScanSuccess = async (decodedText) => {
    await stopScanner();
    qrLoading.value = true;
    qrError.value = '';
    
    try {
        // Call QR login API
        const response = await axios.post('/auth/qr-login', {
            token: decodedText
        });
        
        // Store auth data
        authStore.setUser(response.data.user);
        authStore.setToken(response.data.token);
        authStore.setPermissions(response.data.permissions);
        
        router.push({ name: 'welcome' });
    } catch (e) {
        qrError.value = e.response?.data?.message || 'QR Code tidak valid';
    } finally {
        qrLoading.value = false;
    }
};

onBeforeUnmount(() => {
    stopScanner();
});
</script>
