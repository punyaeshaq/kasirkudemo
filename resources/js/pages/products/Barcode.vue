<template>
    <div class="barcode-print-page p-4 bg-white">
        <div class="no-print mb-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button @click="goBack" class="btn-ghost">← Kembali</button>
                <div>
                    <label class="text-sm font-medium mr-2">Jumlah Cetak:</label>
                    <input v-model.number="copies" type="number" min="1" max="100" class="input w-20 text-center" />
                </div>
            </div>
            <button @click="printBarcodes" class="btn-primary">🖨️ Cetak Barcode</button>
        </div>

        <div class="text-center mb-4 no-print">
            <h2 class="text-xl font-bold">Preview Barcode</h2>
            <p class="text-dark-500">{{ product.name }}</p>
        </div>

        <div class="barcode-container flex flex-wrap justify-center gap-2">
            <div 
                v-for="n in copies" 
                :key="n" 
                class="barcode-item text-center p-2 border border-dark-200 rounded"
                style="width: 200px;"
            >
                <svg :id="`barcode-${n}`"></svg>
                <p class="text-xs font-medium mt-1 truncate">{{ product.name }}</p>
                <p class="text-xs text-dark-500">{{ formatCurrency(product.price) }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const product = ref({ name: '', barcode: '', price: 0 });
const copies = ref(1);

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v || 0);

const goBack = () => {
    router.push('/products');
};

const generateBarcodes = async () => {
    await nextTick();
    if (window.JsBarcode && product.value.barcode) {
        for (let i = 1; i <= copies.value; i++) {
            try {
                window.JsBarcode(`#barcode-${i}`, product.value.barcode, {
                    format: "CODE128",
                    width: 2,
                    height: 50,
                    displayValue: true,
                    fontSize: 12,
                    margin: 5
                });
            } catch (e) {
                console.error('Barcode generation error:', e);
            }
        }
    }
};

const printBarcodes = () => {
    const originalTitle = document.title;
    document.title = `Barcode - ${product.value.name}`;
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

watch(copies, generateBarcodes);

onMounted(async () => {
    // Load JsBarcode library
    await loadJsBarcode();
    
    // Fetch product data
    try {
        const res = await axios.get(`/products/${route.params.id}`);
        product.value = res.data.data || res.data;
        generateBarcodes();
    } catch (e) {
        alert('Gagal memuat data produk');
        router.push('/products');
    }
});
</script>

<style scoped>
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
        gap: 5mm;
        justify-content: flex-start;
    }
    
    .barcode-item {
        border: none !important;
        page-break-inside: avoid;
        width: 50mm !important;
        padding: 2mm !important;
    }
    
    @page {
        margin: 5mm;
        size: auto;
    }
}
</style>
