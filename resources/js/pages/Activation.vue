<template>
    <div class="fixed inset-0 z-[9999] flex items-center justify-center overflow-hidden bg-[#0F172A]">
        <!-- Premium Background Layers -->
        <div class="absolute inset-0 z-0">
            <!-- Noise Texture -->
            <div class="absolute inset-0 opacity-[0.03] z-10 pointer-events-none mix-blend-overlay" 
                 style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E');">
            </div>

            <!-- Deep Gradient Base -->
            <div class="absolute inset-0 bg-gradient-to-br from-rose-900 via-slate-900 to-black opacity-90"></div>

            <!-- Animated Orbs -->
            <div class="absolute top-[-10%] left-[-10%] w-[50vh] h-[50vh] bg-rose-600/30 rounded-full blur-[120px] animate-blob"></div>
            <div class="absolute top-[-20%] right-[-10%] w-[60vh] h-[60vh] bg-orange-600/30 rounded-full blur-[120px] animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-[-20%] left-[20%] w-[60vh] h-[60vh] bg-amber-600/20 rounded-full blur-[120px] animate-blob animation-delay-4000"></div>
        </div>
        
        <!-- Content -->
        <div class="relative z-20 w-full max-w-lg mx-auto p-6">
            <!-- Card -->
            <div class="bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl p-8 shadow-2xl">
                <!-- Logo -->
                <div class="text-center mb-8">
                    <div class="inline-block bg-white/10 rounded-2xl p-4 mb-4">
                        <img 
                            src="/kasirku/public/icons/kasirku-logo.png" 
                            alt="KasirKu Logo" 
                            class="w-20 h-20 object-contain"
                        />
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">Aktivasi KasirKu</h1>
                    <p class="text-slate-400 text-sm">Masukkan kode aktivasi untuk menggunakan aplikasi</p>
                </div>

                <!-- Status Warning (shown when revoked/expired) -->
                <div v-if="statusReason" class="mb-6 p-4 bg-amber-900/30 border border-amber-700 rounded-xl">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">⚠️</span>
                        <div>
                            <p class="text-amber-300 font-medium">{{ statusReason }}</p>
                            <p class="text-amber-400/70 text-sm mt-1">Silakan minta kode aktivasi baru ke admin</p>
                        </div>
                    </div>
                </div>

                <!-- Machine ID Display -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Machine ID</label>
                    <div class="flex items-center gap-2">
                        <input 
                            type="text" 
                            :value="machineId" 
                            readonly 
                            class="flex-1 bg-slate-800/50 border border-slate-700 text-slate-300 rounded-xl px-4 py-3 text-sm font-mono"
                        />
                        <button 
                            @click="copyMachineId" 
                            class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-3 rounded-xl transition-colors"
                            :title="copied ? 'Tersalin!' : 'Salin'"
                        >
                            {{ copied ? '✓' : '📋' }}
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Kirim Machine ID ini ke admin untuk mendapatkan kode aktivasi</p>
                    
                    <!-- Regenerate Machine ID Button -->
                    <button 
                        @click="regenerateMachineId"
                        class="mt-3 w-full bg-slate-700/50 hover:bg-slate-600/50 border border-slate-600 text-slate-300 py-2 px-4 rounded-xl text-sm transition-colors flex items-center justify-center gap-2"
                        :disabled="regenerating"
                    >
                        <span v-if="regenerating" class="inline-block w-4 h-4 border-2 border-slate-400/30 border-t-slate-400 rounded-full animate-spin"></span>
                        <span>🔄</span>
                        <span>{{ regenerating ? 'Generating...' : 'Generate Machine ID Baru' }}</span>
                    </button>
                    <p class="text-xs text-slate-500/70 mt-1 text-center">Jika Machine ID lama sudah tidak bisa digunakan</p>
                </div>

                <!-- Activation Code Input -->
                <form @submit.prevent="handleActivation" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Kode Aktivasi</label>
                        <input 
                            v-model="activationCode"
                            type="text" 
                            placeholder="XXXX-XXXX-XXXX-XXXX-YYMMDD"
                            class="w-full bg-slate-800/50 border border-slate-700 text-white rounded-xl px-4 py-3 text-center font-mono text-lg tracking-wider focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all"
                            :class="{ 'border-red-500': error }"
                        />
                    </div>

                    <!-- Error Message -->
                    <div v-if="error" class="p-3 bg-red-900/30 border border-red-800 rounded-xl text-red-400 text-sm text-center">
                        {{ error }}
                    </div>

                    <!-- Success Message -->
                    <div v-if="success" class="p-3 bg-green-900/30 border border-green-800 rounded-xl text-green-400 text-sm text-center">
                        {{ success }}
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-rose-600 to-orange-600 hover:from-rose-500 hover:to-orange-500 text-white font-semibold py-3 px-6 rounded-xl transition-all shadow-lg shadow-rose-600/25"
                        :disabled="loading"
                    >
                        <span v-if="loading" class="inline-block w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2"></span>
                        {{ loading ? 'Memvalidasi...' : 'Aktivasi Sekarang' }}
                    </button>
                </form>

                <!-- Footer -->
                <div class="mt-8 pt-6 border-t border-slate-700/50 text-center">
                    <p class="text-slate-500 text-xs">
                        Hubungi admin jika Anda belum memiliki kode aktivasi
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useActivationStore } from '@/stores/activation';

const router = useRouter();
const route = useRoute();
const activationStore = useActivationStore();

const machineId = ref('');
const activationCode = ref('');
const loading = ref(false);
const regenerating = ref(false);
const error = ref('');
const success = ref('');
const copied = ref(false);
const statusReason = ref('');

const copyMachineId = async () => {
    try {
        await navigator.clipboard.writeText(machineId.value);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch (e) {
        // Fallback
        const input = document.createElement('input');
        input.value = machineId.value;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    }
};

/**
 * Regenerate a new Machine ID
 * This clears the old Machine ID and generates a fresh one
 */
const regenerateMachineId = async () => {
    regenerating.value = true;
    
    try {
        // Clear old machine ID from localStorage
        localStorage.removeItem('kasirku_machine_id');
        
        // Clear activation status
        activationStore.resetActivation();
        
        // Generate new Machine ID
        machineId.value = await activationStore.getMachineId();
        
        // Show success feedback
        success.value = 'Machine ID baru berhasil di-generate!';
        setTimeout(() => { success.value = ''; }, 3000);
    } catch (e) {
        error.value = 'Gagal generate Machine ID baru';
    } finally {
        regenerating.value = false;
    }
};

const handleActivation = async () => {
    if (!activationCode.value.trim()) {
        error.value = 'Masukkan kode aktivasi';
        return;
    }

    loading.value = true;
    error.value = '';
    success.value = '';

    try {
        await activationStore.activate(machineId.value, activationCode.value);
        success.value = 'Aktivasi berhasil! Mengalihkan...';
        statusReason.value = ''; // Clear status reason on success
        
        setTimeout(() => {
            router.replace({ name: 'setup' });
        }, 1500);
    } catch (e) {
        error.value = e.response?.data?.message || 'Kode aktivasi tidak valid';
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    // Check if redirected due to revocation/expiration
    const reason = route.query.reason || sessionStorage.getItem('activation_reason');
    if (reason) {
        statusReason.value = reason;
        sessionStorage.removeItem('activation_reason');
    }
    
    // Get machine ID from store
    machineId.value = await activationStore.getMachineId();
    
    // Check if already activated
    const isActivated = await activationStore.checkActivation(machineId.value);
    if (isActivated) {
        // Check if setup is completed
        const isSetupCompleted = localStorage.getItem('kasirku_setup_completed') === 'true';
        if (isSetupCompleted) {
            router.replace({ name: 'login' });
        } else {
            router.replace({ name: 'setup' });
        }
    }
});
</script>

<style scoped>
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>

