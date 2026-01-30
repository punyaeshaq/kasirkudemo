<template>
    <div class="max-w-2xl animate-fade-in">
        <div class="card p-6">
            <h2 class="text-xl font-bold text-dark-900 dark:text-white mb-2">Rekening & E-Wallet</h2>
            <p class="text-sm text-dark-500 dark:text-dark-400 mb-6">Kelola rekening bank dan e-wallet untuk metode pembayaran transfer.</p>
            
            <!-- Existing Accounts List -->
            <div v-if="bankAccounts.length" class="space-y-3 mb-6">
                <div 
                    v-for="(account, index) in bankAccounts" 
                    :key="index"
                    class="flex items-center justify-between p-4 bg-dark-50 dark:bg-dark-700 rounded-xl"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-primary-500 to-secondary-500 flex items-center justify-center text-white">
                            <CreditCardIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="font-semibold text-dark-900 dark:text-white">
                                {{ account.bank_name }}
                            </p>
                            <p class="text-sm text-dark-600 dark:text-dark-400">{{ account.account_number }}</p>
                            <p class="text-xs text-dark-500">a.n. {{ account.account_holder }}</p>
                        </div>
                    </div>
                    <button 
                        @click="removeBankAccount(index)"
                        class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 p-2 rounded-lg transition-colors"
                        title="Hapus"
                    >
                        <TrashIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>
            <div v-else class="text-center py-8 text-dark-400 bg-dark-50 dark:bg-dark-700 rounded-xl mb-6">
                <CreditCardIcon class="w-12 h-12 mx-auto mb-3 opacity-50" />
                <p>Belum ada rekening atau e-wallet</p>
            </div>

            <!-- Add New Account Form -->
            <div class="bg-gradient-to-r from-primary-50 to-secondary-50 dark:from-primary-900/20 dark:to-secondary-900/20 p-6 rounded-xl">
                <h3 class="text-lg font-semibold text-dark-900 dark:text-white mb-4">
                    <PlusCircleIcon class="w-5 h-5 inline-block mr-2" />
                    Tambah Rekening Baru
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Nama Bank / E-Wallet</label>
                        <input 
                            v-model="newAccount.bank_name" 
                            type="text" 
                            class="input" 
                            placeholder="BCA, Mandiri, BRI, GoPay, OVO, DANA, dll"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Nomor Rekening / Nomor HP</label>
                        <input 
                            v-model="newAccount.account_number" 
                            type="text" 
                            class="input" 
                            placeholder="1234567890"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Nama Pemilik Rekening</label>
                        <input 
                            v-model="newAccount.account_holder" 
                            type="text" 
                            class="input" 
                            placeholder="Nama sesuai di rekening/akun"
                        />
                    </div>
                    <button 
                        @click="addBankAccount"
                        class="btn-primary w-full"
                        :disabled="!newAccount.bank_name || !newAccount.account_number || !newAccount.account_holder || saving"
                    >
                        {{ saving ? 'Menyimpan...' : '+ Tambah Rekening' }}
                    </button>
                </div>
            </div>

            <!-- QRIS Info -->
            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        💡 Untuk menambahkan gambar QRIS, buka <strong>Pengaturan</strong> → <strong>QRIS Payment</strong>. Gambar QRIS akan otomatis muncul di kasir saat pembayaran.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { CreditCardIcon, TrashIcon, PlusCircleIcon } from '@heroicons/vue/24/outline';

const saving = ref(false);
const bankAccounts = ref([]);
const newAccount = reactive({
    bank_name: '',
    account_number: '',
    account_holder: ''
});

const addBankAccount = async () => {
    if (!newAccount.bank_name || !newAccount.account_number || !newAccount.account_holder) {
        return;
    }
    
    bankAccounts.value.push({
        bank_name: newAccount.bank_name,
        account_number: newAccount.account_number,
        account_holder: newAccount.account_holder
    });
    
    // Clear form
    newAccount.bank_name = '';
    newAccount.account_number = '';
    newAccount.account_holder = '';
    
    // Save to server
    await saveBankAccounts();
};

const removeBankAccount = async (index) => {
    if (confirm('Hapus rekening ini?')) {
        bankAccounts.value.splice(index, 1);
        await saveBankAccounts();
    }
};

const saveBankAccounts = async () => {
    saving.value = true;
    try {
        const formData = new FormData();
        formData.append('bank_accounts', JSON.stringify(bankAccounts.value));
        formData.append('_method', 'PUT');
        
        await axios.post('/settings', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    } catch (e) {
        console.error('Failed to save bank accounts', e);
        alert('Gagal menyimpan rekening');
    } finally {
        saving.value = false;
    }
};

onMounted(async () => {
    try {
        const res = await axios.get('/settings');
        if (res.data.bank_accounts) {
            try {
                bankAccounts.value = JSON.parse(res.data.bank_accounts);
            } catch (e) {
                bankAccounts.value = [];
            }
        }
    } catch (e) {
        console.error('Failed to load bank accounts', e);
    }
});
</script>
