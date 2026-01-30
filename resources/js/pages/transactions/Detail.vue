<template>
    <div class="max-w-3xl animate-fade-in">
        <div class="card p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-dark-900 dark:text-white">{{ transaction.invoice_number }}</h2>
                    <p class="text-sm text-dark-500">{{ formatDate(transaction.created_at) }}</p>
                </div>
                <span :class="['badge text-sm', transaction.status === 'paid' ? 'badge-success' : 'badge-warning']">
                    {{ transaction.status === 'paid' ? 'Lunas' : 'Pending' }}
                </span>
            </div>
            
            <div class="border-t border-dark-200 dark:border-dark-700 pt-4 mb-4">
                <h3 class="font-semibold text-dark-900 dark:text-white mb-3">Item</h3>
                <div class="space-y-2">
                    <div v-for="item in transaction.items" :key="item.id" class="flex justify-between">
                        <span class="text-dark-700 dark:text-dark-300">{{ item.product?.name }} x{{ item.quantity }}</span>
                        <span class="font-medium text-dark-900 dark:text-white">{{ formatCurrency(item.subtotal) }}</span>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-dark-200 dark:border-dark-700 pt-4 space-y-2">
                <div class="flex justify-between text-dark-600 dark:text-dark-400">
                    <span>Subtotal</span>
                    <span>{{ formatCurrency(transaction.subtotal) }}</span>
                </div>
                <div v-if="transaction.discount" class="flex justify-between text-red-500">
                    <span>Diskon</span>
                    <span>-{{ formatCurrency(transaction.discount) }}</span>
                </div>
                <div class="flex justify-between text-dark-600 dark:text-dark-400">
                    <span>PPN</span>
                    <span>{{ formatCurrency(transaction.tax) }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold text-dark-900 dark:text-white pt-2 border-t border-dark-200 dark:border-dark-600">
                    <span>Total</span>
                    <span>{{ formatCurrency(transaction.total) }}</span>
                </div>
            </div>
            
            <div class="mt-6 flex gap-2">
                <router-link to="/transactions" class="btn-ghost flex-1">Kembali</router-link>
                <button @click="printReceipt" class="btn-primary flex-1">🖨️ Cetak Struk</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const transaction = ref({});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v || 0);
const formatDate = (d) => d ? new Date(d).toLocaleString('id-ID') : '-';

const printReceipt = () => {
    const url = router.resolve({ name: 'transactions.print', params: { id: transaction.value.id } }).href;
    window.open(url, '_blank', 'width=400,height=600');
};

onMounted(async () => {
    try {
        const res = await axios.get(`/transactions/${route.params.id}`);
        transaction.value = res.data.data || res.data;
    } catch (e) {
        transaction.value = {
            id: 1, invoice_number: 'INV-20240125-001', created_at: new Date(), status: 'paid',
            subtotal: 100000, discount: 5000, tax: 10450, total: 105450,
            items: [{ id: 1, product: { name: 'Indomie Goreng' }, quantity: 5, subtotal: 17500 }]
        };
    }
});
</script>
