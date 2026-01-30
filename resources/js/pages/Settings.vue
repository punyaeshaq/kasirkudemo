<template>
    <div class="w-full animate-fade-in">
        <form @submit.prevent="save">
            <!-- Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                
                <!-- Card 1: Informasi Toko -->
                <div class="card p-6">
                    <h2 class="text-xl font-bold text-dark-900 dark:text-white mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Informasi Toko
                    </h2>
                    
                    <div class="space-y-4">
                        <!-- Logo Upload -->
                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-2">Logo Toko</label>
                            <div class="flex items-center gap-4">
                                <div v-if="logoPreview" class="w-20 h-20 rounded-lg border-2 border-dark-200 dark:border-dark-600 overflow-hidden bg-white flex-shrink-0">
                                    <img :src="logoPreview" alt="Logo Preview" class="w-full h-full object-contain" />
                                </div>
                                <div v-else class="w-20 h-20 rounded-lg border-2 border-dashed border-dark-300 dark:border-dark-600 flex items-center justify-center bg-dark-50 dark:bg-dark-800 flex-shrink-0">
                                    <svg class="w-8 h-8 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        class="btn-secondary text-sm"
                                    >
                                        {{ logoPreview ? 'Ganti' : 'Upload' }}
                                    </button>
                                    <p class="text-xs text-dark-500 dark:text-dark-400 mt-1">Maks 2MB</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Nama Toko</label>
                            <input v-model="settings.store_name" type="text" class="input" />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Alamat</label>
                            <textarea v-model="settings.store_address" class="input" rows="3"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Telepon</label>
                            <input v-model="settings.store_phone" type="text" class="input" />
                        </div>
                    </div>
                </div>

                <!-- Card 2: Pengaturan Pajak -->
                <div class="card p-6">
                    <h2 class="text-xl font-bold text-dark-900 dark:text-white mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Pengaturan Pajak
                    </h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">PPN (%)</label>
                            <input v-model="settings.tax_rate" type="number" class="input" min="0" max="100" />
                            <p class="text-xs text-dark-500 dark:text-dark-400 mt-1">Tarif pajak pertambahan nilai</p>
                        </div>
                        
                        <div class="p-4 bg-dark-50 dark:bg-dark-700/50 rounded-lg">
                            <p class="text-sm text-dark-600 dark:text-dark-400">
                                <strong class="text-dark-900 dark:text-white">Contoh:</strong> Jika PPN 11%, produk Rp 100.000 akan dikenakan pajak Rp 11.000
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Pengaturan Struk -->
                <div class="card p-6">
                    <h2 class="text-xl font-bold text-dark-900 dark:text-white mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Pengaturan Struk
                    </h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Pesan Footer (Judul)</label>
                            <input v-model="settings.receipt_footer_text" type="text" class="input" placeholder="Contoh: TERIMA KASIH" />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Catatan Kaki</label>
                            <textarea v-model="settings.receipt_note" class="input" rows="2" placeholder="Contoh: Barang yang sudah dibeli..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Website / Media Sosial</label>
                            <input v-model="settings.receipt_website" type="text" class="input" placeholder="Contoh: www.kasirku.id" />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Ukuran Struk</label>
                            <select v-model="settings.receipt_printer_width" class="input">
                                <option value="58mm">58mm (Kecil)</option>
                                <option value="80mm">80mm (Besar)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Card 4: QRIS Payment -->
                <div class="card p-6">
                    <h2 class="text-xl font-bold text-dark-900 dark:text-white mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        QRIS Payment
                    </h2>
                    
                    <div class="space-y-4">
                        <p class="text-sm text-dark-500 dark:text-dark-400">
                            Upload gambar QRIS Anda untuk ditampilkan di kasir POS saat pembayaran QRIS.
                        </p>
                        
                        <div class="flex justify-center">
                            <div v-if="qrisPreview" class="relative group">
                                <div class="w-48 h-48 rounded-xl border-2 border-dark-200 dark:border-dark-600 overflow-hidden bg-white p-2">
                                    <img :src="qrisPreview" alt="QRIS Preview" class="w-full h-full object-contain" />
                                </div>
                                <button 
                                    type="button"
                                    @click="removeQris"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white p-1.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg"
                                    title="Hapus QRIS"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <label v-else class="block w-48 h-48 cursor-pointer">
                                <div class="w-full h-full rounded-xl border-2 border-dashed border-dark-300 dark:border-dark-600 flex flex-col items-center justify-center bg-dark-50 dark:bg-dark-800 hover:border-primary-400 transition-colors">
                                    <svg class="w-12 h-12 text-dark-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                    <p class="text-sm text-dark-500">Upload QRIS</p>
                                    <p class="text-xs text-dark-400">PNG, JPG max 2MB</p>
                                </div>
                                <input 
                                    type="file" 
                                    @change="handleQrisUpload" 
                                    accept="image/*" 
                                    class="hidden" 
                                />
                            </label>
                        </div>
                        
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                💡 Gambar ini akan muncul di modal pembayaran QRIS saat kasir memilih metode pembayaran QRIS.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Save Button -->
            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn-primary px-8" :disabled="loading">
                    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ loading ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';

const loading = ref(false);
const settings = reactive({
    store_name: 'Toko KasirKu',
    store_address: 'Jl Trans Sulawesi, Buntulia Tengah, Kec. Buntulia, Kabupaten Pohuwato',
    store_phone: '021-1234567',
    tax_rate: 11,
    receipt_footer_text: 'TERIMA KASIH',
    receipt_note: 'Barang yang sudah dibeli tidak dapat ditukar/dikembalikan',
    receipt_website: 'www.kasirku.id',
    receipt_printer_width: '58mm'
});

const save = async () => {
    loading.value = true;
    try {
        const formData = new FormData();
        Object.keys(settings).forEach(key => {
            formData.append(key, settings[key]);
        });
        
        if (logoFile.value) {
            formData.append('store_logo', logoFile.value);
        }
        
        if (qrisFile.value) {
            formData.append('qris_image', qrisFile.value);
        }

        const res = await axios.post('/settings', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        alert('Pengaturan disimpan!');
        
        // Update logo preview with server URL if logo was uploaded
        if (res.data.data.store_logo_url) {
            logoPreview.value = res.data.data.store_logo_url;
            logoFile.value = null; // Clear file reference
        }
        
        // Update QRIS preview with server URL if QRIS was uploaded
        if (res.data.data.qris_image_url) {
            qrisPreview.value = res.data.data.qris_image_url;
            qrisFile.value = null;
        }
        
        // Update settings with response data
        Object.assign(settings, res.data.data);
    } catch (e) {
        console.error(e);
        alert('Gagal menyimpan');
    } finally {
        loading.value = false;
    }
};

const logoPreview = ref(null);
const logoFile = ref(null);
const qrisPreview = ref(null);
const qrisFile = ref(null);

const handleLogoUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB');
            return;
        }
        logoFile.value = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const handleQrisUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB');
            return;
        }
        qrisFile.value = file;
        qrisPreview.value = URL.createObjectURL(file);
    }
};

const removeQris = async () => {
    if (confirm('Hapus gambar QRIS?')) {
        qrisPreview.value = null;
        qrisFile.value = null;
        // Save removal to server
        try {
            await axios.post('/settings', {
                qris_image: ''
            });
        } catch (e) {
            console.error('Failed to remove QRIS', e);
        }
    }
};

onMounted(async () => {
    try {
        const res = await axios.get('/settings');
        Object.assign(settings, res.data);
        if (res.data.store_logo_url) {
            logoPreview.value = res.data.store_logo_url;
        }
        if (res.data.qris_image_url) {
            qrisPreview.value = res.data.qris_image_url;
        }
    } catch (e) {}
});
</script>
