<template>
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center h-screen bg-white">
        <div class="text-center">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-black mx-auto mb-4"></div>
            <p class="text-gray-600">Memuat struk...</p>
        </div>
    </div>
    
    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center h-screen bg-white">
        <div class="text-center text-red-600">
            <p class="text-xl mb-2">⚠️</p>
            <p>{{ error }}</p>
        </div>
    </div>
    
    <!-- Receipt Content -->
    <div v-else class="receipt font-mono text-xs p-2 bg-white text-black leading-tight mx-auto transition-all" :style="{ maxWidth: settings.receipt_printer_width || '58mm' }">
        <div class="text-center mb-4">
            <div v-if="settings.store_logo_url" class="flex justify-center mb-2">
                <img :src="settings.store_logo_url" alt="Logo" class="w-16 h-16 object-contain grayscale" />
            </div>
            <div v-else class="w-8 h-8 border-2 border-black rounded-full flex items-center justify-center mx-auto mb-1 font-bold text-base">
                K
            </div>
            <h1 class="uppercase font-bold text-sm tracking-widest mb-1">{{ settings.store_name || 'KASIRKU' }}</h1>
            <p class="text-[10px]">{{ settings.store_address }}</p>
            <p class="text-[10px]">{{ settings.store_phone }}</p>
        </div>

        <div class="border-y border-dashed border-black py-2 my-2 text-[10px]">
            <div class="flex justify-between">
                <span>No: {{ transaction.invoice_number }}</span>
                <span>{{ formatTime(transaction.created_at) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Kasir: {{ transaction.user?.name || 'Admin' }}</span>
                <span>{{ formatDate(transaction.created_at) }}</span>
            </div>
        </div>

        <div class="space-y-2 mb-4">
            <div v-for="item in transaction.items" :key="item.id">
                <div class="font-bold text-[10px] mb-0.5">{{ item.product?.name }}</div>
                <div class="flex justify-between pl-2 text-[9px]">
                    <span>{{ item.quantity }} x {{ formatCurrency(item.price) }}</span>
                    <span class="font-medium">{{ formatCurrency(item.subtotal) }}</span>
                </div>
            </div>
        </div>

        <div class="border-t border-dotted border-black pt-2 space-y-1">
            <div class="flex justify-between text-[9px]">
                <span>Subtotal</span>
                <span>{{ formatCurrency(transaction.subtotal) }}</span>
            </div>
            <div v-if="transaction.discount > 0" class="flex justify-between text-[9px]">
                <span>Diskon</span>
                <span>-{{ formatCurrency(transaction.discount) }}</span>
            </div>
            <div v-if="transaction.tax > 0" class="flex justify-between text-[9px]">
                <span>PPN</span>
                <span>{{ formatCurrency(transaction.tax) }}</span>
            </div>
            <div class="flex justify-between font-bold text-xs pt-1 border-t border-black mt-1">
                <span>TOTAL</span>
                <span>{{ formatCurrency(transaction.total) }}</span>
            </div>
        </div>

        <div class="mt-2 text-[9px] space-y-1">
            <div class="flex justify-between">
                <span class="uppercase">Bayar ({{ transaction.payment_method === 'cash' ? 'Tunai' : transaction.payment_method }})</span>
                <span>{{ formatCurrency(transaction.cash_received || transaction.total) }}</span>
            </div>
            <div v-if="transaction.change > 0 || (transaction.cash_received - transaction.total) > 0" class="flex justify-between font-bold">
                <span>KEMBALI</span>
                <span>{{ formatCurrency((transaction.cash_received - transaction.total) || 0) }}</span>
            </div>
        </div>

        <div class="text-center border-t border-dashed border-black mt-4 pt-2">
            <p class="font-bold text-xs mb-1">{{ settings.receipt_footer_text || 'TERIMA KASIH' }}</p>
            <p class="italic text-[9px] text-gray-600 mb-1">{{ settings.receipt_note || 'Barang yang sudah dibeli tidak dapat ditukar/dikembalikan' }}</p>
            <p class="text-[9px] text-gray-500">{{ settings.receipt_website }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const transaction = ref({});
const settings = ref({});
const loading = ref(true);
const error = ref(null);

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v || 0);
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';
const formatTime = (d) => d ? new Date(d).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}) : '-';

onMounted(async () => {
    try {
        // Use window.axios to leverage the configured baseURL
        const [txRes, setRes] = await Promise.all([
            window.axios.get(`/print-receipt/${route.params.id}`),
            window.axios.get('/settings')
        ]);
        
        const txData = txRes.data;
        const setData = setRes.data;
        
        transaction.value = txData.data || txData;
        settings.value = setData;
        loading.value = false;

        // Auto print after data is loaded and DOM updated
        setTimeout(() => {
            window.print();
        }, 500);
    } catch (e) {
        console.error('Error loading transaction:', e);
        error.value = 'Gagal memuat data transaksi: ' + e.message;
        loading.value = false;
    }
});
</script>

<style scoped>
@media print {
    @page {
        margin: 0;
        size: auto; 
    }
    body {
        margin: 0;
        padding: 0;
        background: white;
    }
    .receipt {
        width: 100%;
        max-width: none;
        padding: 0;
    }
}
</style>
