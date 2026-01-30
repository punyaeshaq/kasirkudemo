<template>
    <div class="space-y-4 animate-fade-in">
        <div class="flex items-center gap-4">
            <input v-model="filters.search" type="text" class="input max-w-xs" placeholder="Cari invoice..." />
            <input v-model="filters.date" type="date" class="input max-w-xs" />
            <select v-model="filters.status" class="input max-w-xs">
                <option value="">Semua Status</option>
                <option value="paid">Lunas</option>
                <option value="pending">Pending</option>
            </select>
        </div>
        
        <div class="card">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th>Kasir</th>
                            <th>Item</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="tx in transactions" :key="tx.id">
                            <td class="font-medium">{{ tx.invoice_number }}</td>
                            <td>{{ formatDate(tx.created_at) }}</td>
                            <td>{{ tx.user?.name || '-' }}</td>
                            <td>{{ tx.items_count }} item</td>
                            <td class="font-semibold">{{ formatCurrency(tx.total) }}</td>
                            <td>
                                <span :class="['badge', getPaymentBadgeColor(getPaymentMethod(tx))]">
                                    {{ getPaymentLabel(getPaymentMethod(tx)) }}
                                </span>
                            </td>
                            <td>
                                <span :class="['badge', tx.status === 'paid' ? 'badge-success' : 'badge-warning']">
                                    {{ tx.status === 'paid' ? 'Lunas' : 'Pending' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex gap-1">
                                    <router-link :to="`/transactions/${tx.id}`" class="btn-ghost text-xs py-1.5">
                                        Detail
                                    </router-link>
                                    <button @click="deleteTransaction(tx)" class="btn-ghost text-xs py-1.5 text-red-500 hover:text-red-600">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';

const transactions = ref([]);
const filters = reactive({ search: '', date: '', status: '' });

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
const formatDate = (d) => new Date(d).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' });

const getPaymentMethod = (tx) => {
    // If API returns payments array, take the first one
    if (tx.payments && tx.payments.length > 0) {
        return tx.payments[0].method;
    }
    // Fallback if flat property exists (e.g. dummy data)
    return tx.payment_method || '-';
};

const getPaymentLabel = (method) => {
    const labels = {
        'cash': 'Tunai',
        'qris': 'QRIS',
        'transfer': 'Transfer'
    };
    return labels[method] || method?.toUpperCase() || '-';
};

const getPaymentBadgeColor = (method) => {
    const colors = {
        'cash': 'badge-success',   // Green
        'qris': 'badge-primary',   // Blue/Indigo
        'transfer': 'badge-warning' // Yellow/Orange
    };
    return colors[method] || 'badge-ghost';
};

const fetchTransactions = async () => {
    try {
        const res = await axios.get('/transactions', { params: filters });
        transactions.value = res.data.data || res.data;
    } catch (e) {
        transactions.value = [
            { id: 1, invoice_number: 'INV-DEMO-01', created_at: new Date(), user: { name: 'Kasir 1' }, items_count: 5, total: 125000, payments: [{ method: 'cash' }], status: 'paid' },
            { id: 2, invoice_number: 'INV-DEMO-02', created_at: new Date(), user: { name: 'Kasir 1' }, items_count: 3, total: 87500, payments: [{ method: 'qris' }], status: 'paid' },
        ];
    }
};

const deleteTransaction = async (tx) => {
    if (!confirm(`Hapus transaksi ${tx.invoice_number}?\n\nStok produk akan dikembalikan.`)) return;
    try {
        await axios.delete(`/transactions/${tx.id}`);
        alert('Transaksi berhasil dihapus dan stok dikembalikan');
        fetchTransactions();
    } catch (e) {
        alert(e.response?.data?.message || 'Gagal menghapus transaksi');
    }
};

watch(filters, fetchTransactions, { deep: true });
onMounted(fetchTransactions);
</script>
