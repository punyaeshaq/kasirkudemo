<template>
    <div class="space-y-4 animate-fade-in">
        <div class="flex items-center gap-4">
            <input v-model="search" type="text" class="input max-w-xs" placeholder="Cari invoice atau pelanggan..." @input="debouncedSearch" />
        </div>
        
        <div class="card">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Sisa Hutang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="debt in debts" :key="debt.id">
                            <td class="font-medium">{{ debt.invoice_number }}</td>
                            <td>{{ formatDate(debt.created_at) }}</td>
                            <td>{{ debt.customer?.name || '-' }}</td>
                            <td>{{ formatCurrency(debt.total) }}</td>
                            <td class="font-bold text-red-500">{{ formatCurrency(debt.remaining_debt) }}</td>
                            <td>
                                <span :class="['badge', debt.remaining_debt > 0 ? 'badge-warning' : 'badge-success']">
                                    {{ debt.remaining_debt > 0 ? 'Belum Lunas' : 'Lunas' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button v-if="debt.remaining_debt > 0" @click="openPaymentModal(debt)" class="btn-primary text-xs py-1.5">
                                        Bayar
                                    </button>
                                    <button @click="viewHistory(debt)" class="btn-ghost text-xs py-1.5">
                                        Riwayat
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!debts.length">
                            <td colspan="7" class="text-center text-dark-500 py-8">Tidak ada piutang</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Modal -->
        <Teleport to="body">
            <div v-if="showPaymentModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="card max-w-md w-full p-6 animate-scale-in">
                    <h3 class="text-xl font-bold text-dark-900 dark:text-white mb-4">
                        Pembayaran Piutang
                    </h3>
                    <div class="mb-4 p-3 bg-dark-50 dark:bg-dark-700 rounded-lg">
                        <p class="text-sm text-dark-600 dark:text-dark-400">Invoice: <span class="font-medium text-dark-900 dark:text-white">{{ selectedDebt?.invoice_number }}</span></p>
                        <p class="text-sm text-dark-600 dark:text-dark-400">Pelanggan: <span class="font-medium text-dark-900 dark:text-white">{{ selectedDebt?.customer?.name }}</span></p>
                        <p class="text-sm text-dark-600 dark:text-dark-400">Sisa Hutang: <span class="font-bold text-red-500">{{ formatCurrency(selectedDebt?.remaining_debt) }}</span></p>
                    </div>
                    <form @submit.prevent="submitPayment" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Jumlah Bayar *</label>
                            <input v-model="paymentForm.amount" type="number" class="input" :max="selectedDebt?.remaining_debt" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Metode Pembayaran</label>
                            <select v-model="paymentForm.payment_method" class="input">
                                <option value="cash">Tunai</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Catatan</label>
                            <textarea v-model="paymentForm.note" class="input" rows="2"></textarea>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="button" @click="showPaymentModal = false" class="btn-ghost flex-1">Batal</button>
                            <button type="submit" class="btn-success flex-1" :disabled="paying">
                                {{ paying ? 'Memproses...' : 'Bayar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- History Modal -->
        <Teleport to="body">
            <div v-if="showHistoryModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="card max-w-lg w-full p-6 animate-scale-in">
                    <h3 class="text-xl font-bold text-dark-900 dark:text-white mb-4">
                        Riwayat Pembayaran
                    </h3>
                    <div class="mb-4 p-3 bg-dark-50 dark:bg-dark-700 rounded-lg">
                        <p class="text-sm text-dark-600 dark:text-dark-400">Invoice: <span class="font-medium text-dark-900 dark:text-white">{{ selectedDebt?.invoice_number }}</span></p>
                        <p class="text-sm text-dark-600 dark:text-dark-400">Total: <span class="font-medium text-dark-900 dark:text-white">{{ formatCurrency(selectedDebt?.total) }}</span></p>
                    </div>
                    <div class="max-h-64 overflow-y-auto space-y-2">
                        <div v-for="payment in paymentHistory" :key="payment.id" class="p-3 bg-dark-50 dark:bg-dark-700 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-dark-900 dark:text-white">{{ formatCurrency(payment.amount) }}</span>
                                <span class="text-xs text-dark-500">{{ formatDateTime(payment.paid_at) }}</span>
                            </div>
                            <div class="text-sm text-dark-600 dark:text-dark-400">
                                <span class="badge badge-ghost text-xs">{{ getPaymentLabel(payment.payment_method) }}</span>
                                <span v-if="payment.note" class="ml-2">{{ payment.note }}</span>
                            </div>
                        </div>
                        <div v-if="!paymentHistory.length" class="text-center text-dark-500 py-4">
                            Belum ada pembayaran
                        </div>
                    </div>
                    <div class="flex gap-2 pt-4 mt-4 border-t border-dark-200 dark:border-dark-700">
                        <button @click="showHistoryModal = false" class="btn-ghost flex-1">Tutup</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';

const debts = ref([]);
const search = ref('');
const showPaymentModal = ref(false);
const showHistoryModal = ref(false);
const selectedDebt = ref(null);
const paymentHistory = ref([]);
const paying = ref(false);

const paymentForm = reactive({
    amount: '',
    payment_method: 'cash',
    note: ''
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v || 0);
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';
const formatDateTime = (d) => d ? new Date(d).toLocaleString('id-ID') : '-';

const getPaymentLabel = (method) => {
    const labels = { 'cash': 'Tunai', 'transfer': 'Transfer', 'qris': 'QRIS' };
    return labels[method] || method;
};

let searchTimeout = null;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(fetchDebts, 300);
};

const fetchDebts = async () => {
    try {
        const res = await axios.get('/debts', { params: { search: search.value } });
        debts.value = res.data.data || res.data;
    } catch (e) {
        console.error('Failed to fetch debts', e);
    }
};

const openPaymentModal = (debt) => {
    selectedDebt.value = debt;
    paymentForm.amount = debt.remaining_debt;
    paymentForm.payment_method = 'cash';
    paymentForm.note = '';
    showPaymentModal.value = true;
};

const submitPayment = async () => {
    if (paymentForm.amount <= 0 || paymentForm.amount > selectedDebt.value.remaining_debt) {
        alert('Jumlah bayar tidak valid');
        return;
    }
    
    paying.value = true;
    try {
        await axios.post('/debts/pay', {
            transaction_id: selectedDebt.value.id,
            amount: paymentForm.amount,
            payment_method: paymentForm.payment_method,
            note: paymentForm.note
        });
        showPaymentModal.value = false;
        fetchDebts();
        alert('Pembayaran berhasil!');
    } catch (e) {
        alert(e.response?.data?.message || 'Gagal memproses pembayaran');
    } finally {
        paying.value = false;
    }
};

const viewHistory = async (debt) => {
    selectedDebt.value = debt;
    try {
        const res = await axios.get(`/debts/${debt.id}/history`);
        paymentHistory.value = res.data;
        showHistoryModal.value = true;
    } catch (e) {
        alert('Gagal memuat riwayat');
    }
};

onMounted(fetchDebts);
</script>
