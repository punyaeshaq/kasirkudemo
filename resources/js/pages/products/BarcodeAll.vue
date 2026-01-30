<template>
    <div class="barcode-print-page p-4 bg-white">
        <div class="no-print mb-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button @click="goBack" class="btn-ghost">← Kembali</button>
                <span class="text-dark-600">Total: {{ products.length }} produk</span>
            </div>
            <button @click="printBarcodes" class="btn-primary">🖨️ Cetak Semua Barcode</button>
        </div>

        <div class="text-center mb-4 no-print">
            <h2 class="text-xl font-bold">Preview Barcode Semua Produk</h2>
        </div>

        <div v-if="loading" class="text-center py-8 text-dark-500">
            Memuat data produk...
        </div>

        <div v-else class="barcode-container flex flex-wrap justify-center gap-2">
            <div 
                v-for="(product, index) in products" 
                :key="product.id" 
                class="barcode-item text-center p-2 border border-dark-200 rounded"
            >
                <svg :id="`barcode-${index}`"></svg>
                <p class="text-xs font-medium mt-1 barcode-name">{{ product.name }}</p>
                <p class="text-xs text-dark-500">{{ formatCurrency(product.price) }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const products = ref([]);
const loading = ref(true);

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v || 0);

const goBack = () => {
    router.push('/products');
};

const generateBarcodes = async () => {
    await nextTick();
    if (window.JsBarcode) {
        products.value.forEach((product, index) => {
            if (product.barcode) {
                try {
                    window.JsBarcode(`#barcode-${index}`, product.barcode, {
                        format: "CODE128",
                        width: 2,
                        height: 50,
                        displayValue: true,
                        fontSize: 12,
                        margin: 5
                    });
                } catch (e) {
                    console.error('Barcode generation error for product:', product.name, e);
                }
            }
        });
    }
};

const printBarcodes = () => {
    const originalTitle = document.title;
    document.title = `Barcode - Semua Produk (${products.value.length} item)`;
    window.print();
    document.title = originalTitle;
};

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

onMounted(async () => {
    // Load JsBarcode library
    await loadJsBarcode();
    
    // Fetch all products
    try {
        const res = await axios.get('/products');
        products.value = res.data.data || res.data;
        loading.value = false;
        generateBarcodes();
    } catch (e) {
        alert('Gagal memuat data produk');
        loading.value = false;
    }
});
</script>

<style scoped>
.barcode-item {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    width: auto;
    min-width: 120px;
    max-width: 280px;
}

.barcode-item svg {
    max-width: 100%;
    height: auto;
}

.barcode-name {
    max-width: 100%;
    word-wrap: break-word;
    overflow-wrap: break-word;
    white-space: normal;
}

@media print {
    .no-print {
        display: none !important;
    }
    
    .barcode-print-page {
        padding: 0;
        background: white;
    }
    
    .barcode-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8mm;
        justify-content: flex-start;
    }
    
    .barcode-item {
        border: 1px dashed #ccc !important;
        page-break-inside: avoid;
        width: auto !important;
        min-width: auto !important;
        max-width: none !important;
        padding: 4mm !important;
        margin: 2mm !important;
    }

    .barcode-item svg {
        max-width: none;
    }
    
    @page {
        margin: 5mm;
        size: auto;
    }
}
</style>
