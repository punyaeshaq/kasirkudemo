<template>
    <div class="fixed inset-0 z-[9999] flex items-center justify-center overflow-auto bg-[#0F172A]">
        <!-- Premium Background Layers -->
        <div class="absolute inset-0 z-0">
            <!-- Noise Texture -->
            <div class="absolute inset-0 opacity-[0.03] z-10 pointer-events-none mix-blend-overlay" 
                 style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E');">
            </div>

            <!-- Deep Gradient Base -->
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-900 via-slate-900 to-black opacity-90"></div>

            <!-- Animated Orbs -->
            <div class="absolute top-[-10%] left-[-10%] w-[50vh] h-[50vh] bg-emerald-600/30 rounded-full blur-[120px] animate-blob"></div>
            <div class="absolute top-[-20%] right-[-10%] w-[60vh] h-[60vh] bg-teal-600/30 rounded-full blur-[120px] animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-[-20%] left-[20%] w-[60vh] h-[60vh] bg-cyan-600/20 rounded-full blur-[120px] animate-blob animation-delay-4000"></div>
        </div>
        
        <!-- Content -->
        <div class="relative z-20 w-full max-w-2xl mx-auto p-6 my-8">
            <!-- Card -->
            <div class="bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl p-8 shadow-2xl">
                
                <!-- Step Indicator -->
                <div class="flex items-center justify-center gap-2 mb-8">
                    <div v-for="i in 3" :key="i" 
                         class="w-3 h-3 rounded-full transition-all duration-300"
                         :class="currentStep >= i ? 'bg-emerald-500 scale-110' : 'bg-slate-700'">
                    </div>
                </div>

                <!-- Logo -->
                <div class="text-center mb-8">
                    <div class="inline-block bg-white/10 rounded-2xl p-4 mb-4">
                        <img 
                            src="/kasirku/public/icons/kasirku-logo.png" 
                            alt="KasirKu Logo" 
                            class="w-16 h-16 object-contain"
                        />
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">
                        {{ stepTitle }}
                    </h1>
                    <p class="text-slate-400 text-sm">{{ stepDescription }}</p>
                </div>

                <!-- Step 1: Choose Mode -->
                <div v-if="currentStep === 1" class="space-y-4 animate-fade-in">
                    <!-- Create New Store Option -->
                    <button 
                        @click="selectMode('new')"
                        class="w-full p-6 rounded-2xl border-2 transition-all duration-300 text-left group"
                        :class="selectedMode === 'new' 
                            ? 'border-emerald-500 bg-emerald-900/30' 
                            : 'border-slate-700 bg-slate-800/30 hover:border-slate-600'"
                    >
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-xl bg-emerald-600/20 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-1">Buat Toko Baru</h3>
                                <p class="text-sm text-slate-400">Mulai dari awal dengan mengatur nama toko, alamat, dan logo</p>
                            </div>
                            <div class="ml-auto">
                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center"
                                     :class="selectedMode === 'new' ? 'border-emerald-500 bg-emerald-500' : 'border-slate-600'">
                                    <svg v-if="selectedMode === 'new'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </button>

                    <!-- Restore Database Option -->
                    <button 
                        @click="selectMode('restore')"
                        class="w-full p-6 rounded-2xl border-2 transition-all duration-300 text-left group"
                        :class="selectedMode === 'restore' 
                            ? 'border-cyan-500 bg-cyan-900/30' 
                            : 'border-slate-700 bg-slate-800/30 hover:border-slate-600'"
                    >
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-xl bg-cyan-600/20 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-1">Restore Database</h3>
                                <p class="text-sm text-slate-400">Pulihkan data dari file backup (.sql) yang sudah ada</p>
                            </div>
                            <div class="ml-auto">
                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center"
                                     :class="selectedMode === 'restore' ? 'border-cyan-500 bg-cyan-500' : 'border-slate-600'">
                                    <svg v-if="selectedMode === 'restore'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </button>

                    <!-- Continue Button -->
                    <button 
                        @click="goToStep2"
                        class="w-full mt-6 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold py-3 px-6 rounded-xl transition-all shadow-lg shadow-emerald-600/25 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!selectedMode"
                    >
                        Lanjutkan →
                    </button>
                </div>

                <!-- Step 2a: Create New Store Form -->
                <div v-if="currentStep === 2 && selectedMode === 'new'" class="space-y-5 animate-fade-in">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Nama Toko *</label>
                        <input 
                            v-model="storeForm.store_name"
                            type="text" 
                            placeholder="Contoh: Toko Makmur Jaya"
                            class="w-full bg-slate-800/50 border border-slate-700 text-white rounded-xl px-4 py-3 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Alamat Toko</label>
                        <textarea 
                            v-model="storeForm.store_address"
                            rows="2"
                            placeholder="Jl. Contoh No. 123, Kota"
                            class="w-full bg-slate-800/50 border border-slate-700 text-white rounded-xl px-4 py-3 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all resize-none"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Telepon</label>
                        <input 
                            v-model="storeForm.store_phone"
                            type="text" 
                            placeholder="08xx-xxxx-xxxx"
                            class="w-full bg-slate-800/50 border border-slate-700 text-white rounded-xl px-4 py-3 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Logo Toko (Opsional)</label>
                        <div class="flex items-center gap-4">
                            <div v-if="logoPreview" class="w-16 h-16 rounded-xl border-2 border-slate-600 overflow-hidden bg-white flex-shrink-0">
                                <img :src="logoPreview" alt="Logo Preview" class="w-full h-full object-contain" />
                            </div>
                            <div v-else class="w-16 h-16 rounded-xl border-2 border-dashed border-slate-600 flex items-center justify-center bg-slate-800/50 flex-shrink-0">
                                <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <input 
                                    ref="fileInput"
                                    type="file" 
                                    @change="handleLogoUpload" 
                                    accept="image/*" 
                                    class="hidden" 
                                />
                                <button 
                                    type="button"
                                    @click="$refs.fileInput.click()"
                                    class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg text-sm transition-colors"
                                >
                                    {{ logoPreview ? 'Ganti' : 'Upload' }}
                                </button>
                                <p class="text-xs text-slate-500 mt-1">Maks 2MB</p>
                            </div>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="error" class="p-3 bg-red-900/30 border border-red-800 rounded-xl text-red-400 text-sm text-center">
                        {{ error }}
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button 
                            @click="currentStep = 1"
                            class="flex-1 bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 px-6 rounded-xl transition-colors"
                        >
                            ← Kembali
                        </button>
                        <button 
                            @click="submitNewStore"
                            class="flex-1 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold py-3 px-6 rounded-xl transition-all shadow-lg shadow-emerald-600/25"
                            :disabled="loading || !storeForm.store_name"
                        >
                            <span v-if="loading" class="inline-block w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2"></span>
                            {{ loading ? 'Menyimpan...' : 'Buat Toko' }}
                        </button>
                    </div>
                </div>

                <!-- Step 2b: Restore Database -->
                <div v-if="currentStep === 2 && selectedMode === 'restore'" class="space-y-5 animate-fade-in">
                    <div class="p-4 bg-amber-900/30 border border-amber-700 rounded-xl">
                        <div class="flex items-start gap-3">
                            <span class="text-xl">⚠️</span>
                            <div>
                                <p class="text-amber-300 font-medium text-sm">Perhatian</p>
                                <p class="text-amber-400/70 text-sm mt-1">
                                    Restore akan mengganti semua data yang ada. Pastikan file backup valid.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- File Upload Area -->
                    <label 
                        class="block w-full p-8 border-2 border-dashed rounded-2xl cursor-pointer transition-colors"
                        :class="restoreFile ? 'border-cyan-500 bg-cyan-900/20' : 'border-slate-600 hover:border-slate-500 bg-slate-800/30'"
                    >
                        <input 
                            type="file" 
                            @change="handleRestoreFileSelect"
                            accept=".sql,.txt"
                            class="hidden"
                        />
                        <div v-if="restoreFile" class="text-center text-cyan-400">
                            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="font-medium">{{ restoreFile.name }}</p>
                            <p class="text-sm opacity-75 mt-1">{{ formatFileSize(restoreFile.size) }}</p>
                        </div>
                        <div v-else class="text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p class="font-medium">Klik untuk memilih file backup</p>
                            <p class="text-sm mt-1">Format: .sql (Maks. 100MB)</p>
                        </div>
                    </label>

                    <!-- Error Message -->
                    <div v-if="error" class="p-3 bg-red-900/30 border border-red-800 rounded-xl text-red-400 text-sm text-center">
                        {{ error }}
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button 
                            @click="currentStep = 1"
                            class="flex-1 bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 px-6 rounded-xl transition-colors"
                        >
                            ← Kembali
                        </button>
                        <button 
                            @click="submitRestore"
                            class="flex-1 bg-gradient-to-r from-cyan-600 to-teal-600 hover:from-cyan-500 hover:to-teal-500 text-white font-semibold py-3 px-6 rounded-xl transition-all shadow-lg shadow-cyan-600/25"
                            :disabled="loading || !restoreFile"
                        >
                            <span v-if="loading" class="inline-block w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2"></span>
                            {{ loading ? 'Memulihkan...' : 'Pulihkan Database' }}
                        </button>
                    </div>
                </div>

                <!-- Step 3: Success -->
                <div v-if="currentStep === 3" class="text-center animate-fade-in">
                    <div class="w-24 h-24 mx-auto mb-6 bg-emerald-600/20 rounded-full flex items-center justify-center animate-bounce-gentle">
                        <svg class="w-12 h-12 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-2">Setup Berhasil! 🎉</h2>
                    <p class="text-slate-400 mb-6">{{ successMessage }}</p>
                    
                    <div class="flex flex-col items-center gap-4">
                        <div class="relative w-16 h-1 bg-slate-800 rounded-full overflow-hidden">
                            <div class="absolute inset-0 bg-emerald-500 animate-loading-bar"></div>
                        </div>
                        <span class="text-sm font-medium text-slate-400 tracking-wider uppercase text-[10px]">Mengalihkan ke Login</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const currentStep = ref(1);
const selectedMode = ref(null);
const loading = ref(false);
const error = ref('');
const successMessage = ref('');

// Store form
const storeForm = ref({
    store_name: '',
    store_address: '',
    store_phone: ''
});
const logoFile = ref(null);
const logoPreview = ref(null);

// Restore file
const restoreFile = ref(null);

const stepTitle = computed(() => {
    if (currentStep.value === 1) return 'Setup KasirKu';
    if (currentStep.value === 2 && selectedMode.value === 'new') return 'Buat Toko Baru';
    if (currentStep.value === 2 && selectedMode.value === 'restore') return 'Restore Database';
    if (currentStep.value === 3) return 'Selesai!';
    return 'Setup';
});

const stepDescription = computed(() => {
    if (currentStep.value === 1) return 'Pilih cara untuk memulai aplikasi kasir Anda';
    if (currentStep.value === 2 && selectedMode.value === 'new') return 'Isi informasi toko Anda';
    if (currentStep.value === 2 && selectedMode.value === 'restore') return 'Upload file backup untuk dipulihkan';
    if (currentStep.value === 3) return '';
    return '';
});

const selectMode = (mode) => {
    selectedMode.value = mode;
};

const goToStep2 = () => {
    if (selectedMode.value) {
        currentStep.value = 2;
        error.value = '';
    }
};

const handleLogoUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            error.value = 'Ukuran file maksimal 2MB';
            return;
        }
        logoFile.value = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const handleRestoreFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        restoreFile.value = file;
        error.value = '';
    }
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const submitNewStore = async () => {
    if (!storeForm.value.store_name.trim()) {
        error.value = 'Nama toko wajib diisi';
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        const formData = new FormData();
        formData.append('store_name', storeForm.value.store_name);
        formData.append('store_address', storeForm.value.store_address);
        formData.append('store_phone', storeForm.value.store_phone);
        
        if (logoFile.value) {
            formData.append('store_logo', logoFile.value);
        }

        const response = await axios.post('/setup/create-store', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success) {
            successMessage.value = 'Toko Anda telah dibuat. Selamat berjualan!';
            localStorage.setItem('kasirku_setup_completed', 'true');
            currentStep.value = 3;
            
            setTimeout(() => {
                router.replace({ name: 'login' });
            }, 2500);
        } else {
            error.value = response.data.message || 'Gagal membuat toko';
        }
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal membuat toko';
    } finally {
        loading.value = false;
    }
};

const submitRestore = async () => {
    if (!restoreFile.value) {
        error.value = 'Pilih file backup terlebih dahulu';
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        const formData = new FormData();
        formData.append('file', restoreFile.value);

        const response = await axios.post('/setup/restore', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success) {
            successMessage.value = 'Data berhasil dipulihkan. Selamat berjualan!';
            localStorage.setItem('kasirku_setup_completed', 'true');
            currentStep.value = 3;
            
            setTimeout(() => {
                router.replace({ name: 'login' });
            }, 2500);
        } else {
            error.value = response.data.message || 'Gagal memulihkan database';
        }
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal memulihkan database';
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    // Check if already setup completed
    try {
        const response = await axios.get('/setup/status');
        if (response.data.setup_completed) {
            router.replace({ name: 'login' });
        }
    } catch (e) {
        // Continue with setup if check fails
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

.animate-fade-in {
    animation: fade-in 0.5s ease forwards;
}

@keyframes fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes bounce-gentle {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.animate-bounce-gentle {
    animation: bounce-gentle 2s ease-in-out infinite;
}

@keyframes loading-bar {
    0% { transform: translateX(-100%); }
    50% { transform: translateX(0); }
    100% { transform: translateX(100%); }
}

.animate-loading-bar {
    animation: loading-bar 1.5s infinite linear;
}
</style>
