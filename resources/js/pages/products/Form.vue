<template>
    <div class="max-w-2xl animate-fade-in">
        <div class="card p-6">
            <h2 class="text-xl font-bold text-dark-900 dark:text-white mb-6">
                {{ isEdit ? 'Edit Produk' : 'Tambah Produk' }}
            </h2>
            
            <form @submit.prevent="save" class="space-y-4">
                <!-- Image Upload -->
                <div class="flex flex-col items-center justify-center mb-6">
                    <div class="relative w-32 h-32 rounded-xl bg-dark-100 dark:bg-dark-700 overflow-hidden mb-2 border-2 border-dashed border-dark-300 dark:border-dark-600 hover:border-primary-500 transition-colors">
                        <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover" />
                        <div v-else class="flex flex-col items-center justify-center w-full h-full text-dark-400">
                            <span class="text-xs">Upload Foto</span>
                        </div>
                        <input 
                            type="file" 
                            accept="image/*" 
                            class="absolute inset-0 opacity-0 cursor-pointer"
                            @change="handleFileUpload"
                        />
                    </div>
                    <p class="text-xs text-dark-500">Klik kotak untuk ubah foto</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Nama Produk</label>
                    <input v-model="form.name" type="text" class="input" required />
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Barcode</label>
                        <div class="flex gap-2">
                            <input 
                                ref="barcodeInput"
                                v-model="form.barcode" 
                                type="text" 
                                class="input flex-1" 
                                placeholder="Masukkan, scan kamera, atau scan Bluetooth" 
                                @focus="enableBluetoothScanner"
                                @blur="disableBluetoothScanner"
                            />
                            <BarcodeScanner @scanned="onBarcodeScanned" />
                        </div>
                        <!-- Bluetooth Scanner Status -->
                        <div v-if="bluetoothScannerActive" class="mt-2 flex items-center gap-2 text-xs text-primary-600 dark:text-primary-400">
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                            </span>
                            Scanner Bluetooth aktif - Siap menerima scan
                        </div>
                        <!-- Barcode Preview -->
                        <div v-if="form.barcode" class="mt-3 p-3 bg-white dark:bg-dark-700 rounded-lg border border-dark-200 dark:border-dark-600">
                            <p class="text-xs text-dark-500 mb-2 text-center">Preview Barcode</p>
                            <div class="flex justify-center">
                                <svg id="barcode-preview"></svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Kategori</label>
                        <select v-model="form.category_id" class="input">
                            <option value="">Pilih Kategori</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Harga</label>
                        <input v-model="form.price" type="number" class="input" required min="0" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Stok</label>
                        <input v-model="form.stock" type="number" class="input" required min="0" />
                    </div>
                </div>
                
                <div class="flex gap-3 pt-4">
                    <router-link to="/products" class="btn-ghost flex-1">Batal</router-link>
                    <button type="submit" class="btn-primary flex-1" :disabled="loading">
                        {{ loading ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import BarcodeScanner from '../../components/BarcodeScanner.vue';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const loading = ref(false);
const categories = ref([]);
const imageFile = ref(null);
const imagePreview = ref(null);
const barcodeInput = ref(null);
const bluetoothScannerActive = ref(false);

// Bluetooth Scanner State
let scanBuffer = '';
let scanTimeout = null;
const SCAN_THRESHOLD_MS = 50; // Time between keystrokes to detect scanner

const form = reactive({
    name: '',
    barcode: '',
    category_id: '',
    price: 0,
    stock: 0,
    loading: false
});

const handleFileUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        imageFile.value = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

// Handle barcode scanned from camera or Bluetooth
const onBarcodeScanned = (barcode) => {
    form.barcode = barcode;
    generateBarcodePreview();
    // Show success feedback
    showScanSuccess();
};

// Show visual feedback when barcode is scanned
const showScanSuccess = () => {
    if (barcodeInput.value) {
        barcodeInput.value.classList.add('ring-2', 'ring-green-500');
        setTimeout(() => {
            barcodeInput.value?.classList.remove('ring-2', 'ring-green-500');
        }, 1000);
    }
};

// Bluetooth Scanner - Handle keyboard input
const handleKeyDown = (e) => {
    // Ignore if typing in other inputs
    if (document.activeElement !== barcodeInput.value) return;
    
    // Clear previous timeout
    if (scanTimeout) clearTimeout(scanTimeout);
    
    // If Enter is pressed, process the buffer as a complete scan
    if (e.key === 'Enter') {
        e.preventDefault();
        if (scanBuffer.length >= 4) { // Minimum barcode length
            form.barcode = scanBuffer;
            generateBarcodePreview();
            showScanSuccess();
        }
        scanBuffer = '';
        return;
    }
    
    // Only accept alphanumeric characters for barcode
    if (e.key.length === 1 && /[a-zA-Z0-9]/.test(e.key)) {
        scanBuffer += e.key;
        
        // Auto-clear buffer after delay (for manual typing)
        scanTimeout = setTimeout(() => {
            scanBuffer = '';
        }, 500);
    }
};

// Enable Bluetooth scanner listener
const enableBluetoothScanner = () => {
    bluetoothScannerActive.value = true;
    scanBuffer = '';
    document.addEventListener('keydown', handleKeyDown);
};

// Disable Bluetooth scanner listener
const disableBluetoothScanner = () => {
    bluetoothScannerActive.value = false;
    scanBuffer = '';
    if (scanTimeout) clearTimeout(scanTimeout);
    document.removeEventListener('keydown', handleKeyDown);
};

// Load JsBarcode library dynamically
const loadJsBarcode = () => {
    return new Promise((resolve) => {
        if (window.JsBarcode) {
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js';
        script.onload = resolve;
        document.head.appendChild(script);
    });
};

// Generate barcode preview image
const generateBarcodePreview = async () => {
    if (!form.barcode) return;
    
    await loadJsBarcode();
    await nextTick();
    
    const element = document.getElementById('barcode-preview');
    if (element && window.JsBarcode) {
        try {
            window.JsBarcode('#barcode-preview', form.barcode, {
                format: "CODE128",
                width: 2,
                height: 60,
                displayValue: true,
                fontSize: 14,
                margin: 10,
                background: "transparent"
            });
        } catch (e) {
            console.error('Barcode generation error:', e);
        }
    }
};

// Watch for barcode changes to update preview
watch(() => form.barcode, (newVal) => {
    if (newVal) {
        generateBarcodePreview();
    }
});

const save = async () => {
    loading.value = true;
    try {
        const formData = new FormData();
        formData.append('name', form.name);
        formData.append('barcode', form.barcode || '');
        formData.append('category_id', form.category_id || '');
        formData.append('price', form.price);
        formData.append('stock', form.stock);
        
        if (imageFile.value) {
            formData.append('image', imageFile.value);
        }

        if (isEdit.value) {
            formData.append('_method', 'PUT'); // Fake PUT for FormData
            await axios.post(`/products/${route.params.id}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        } else {
            await axios.post('/products', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        }
        router.push('/products');
    } catch (e) {
        console.error(e);
        alert(e.response?.data?.message || 'Gagal menyimpan produk');
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    try {
        const catRes = await axios.get('/categories');
        categories.value = catRes.data.data || catRes.data;
        
        if (isEdit.value) {
            const res = await axios.get(`/products/${route.params.id}`);
            const data = res.data.data || res.data;
            Object.assign(form, data);
            if (data.image) {
                imagePreview.value = data.image; // Assume full URL or relative path
            }
            // Generate barcode preview if editing
            if (data.barcode) {
                generateBarcodePreview();
            }
        }
    } catch (e) {
        categories.value = [{ id: 1, name: 'Makanan' }];
    }
});

// Cleanup on unmount
onBeforeUnmount(() => {
    disableBluetoothScanner();
});
</script>
