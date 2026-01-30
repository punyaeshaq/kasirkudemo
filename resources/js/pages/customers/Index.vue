<template>
    <div class="space-y-4 animate-fade-in">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <input v-model="search" type="text" class="input max-w-xs" placeholder="Cari pelanggan..." @input="debouncedSearch" />
            </div>
            <button @click="openModal()" class="btn-primary">
                <PlusIcon class="w-4 h-4 mr-2" />
                Tambah Pelanggan
            </button>
        </div>
        
        <div class="card">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th>Total Transaksi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="customer in customers" :key="customer.id">
                            <td class="font-medium">{{ customer.name }}</td>
                            <td>{{ customer.phone || '-' }}</td>
                            <td class="max-w-xs truncate">{{ customer.address || '-' }}</td>
                            <td>{{ customer.transactions_count || 0 }}</td>
                            <td>
                                <div class="flex gap-2">
                                    <button @click="openModal(customer)" class="btn-ghost text-xs py-1.5">
                                        <PencilIcon class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteCustomer(customer)" class="btn-ghost text-xs py-1.5 text-red-500 hover:text-red-600">
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!customers.length">
                            <td colspan="5" class="text-center text-dark-500 py-8">Tidak ada data pelanggan</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="card max-w-md w-full p-6 animate-scale-in">
                    <h3 class="text-xl font-bold text-dark-900 dark:text-white mb-4">
                        {{ editingCustomer ? 'Edit Pelanggan' : 'Tambah Pelanggan' }}
                    </h3>
                    <form @submit.prevent="saveCustomer" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Nama *</label>
                            <input v-model="form.name" type="text" class="input" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Telepon</label>
                            <input v-model="form.phone" type="text" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Alamat</label>
                            <textarea v-model="form.address" class="input" rows="2"></textarea>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="button" @click="showModal = false" class="btn-ghost flex-1">Batal</button>
                            <button type="submit" class="btn-primary flex-1" :disabled="saving">
                                {{ saving ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

const customers = ref([]);
const search = ref('');
const showModal = ref(false);
const saving = ref(false);
const editingCustomer = ref(null);
const form = reactive({
    name: '',
    phone: '',
    address: ''
});

let searchTimeout = null;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(fetchCustomers, 300);
};

const fetchCustomers = async () => {
    try {
        const res = await axios.get('/customers', { params: { search: search.value } });
        customers.value = res.data.data || res.data;
    } catch (e) {
        console.error('Failed to fetch customers', e);
    }
};

const openModal = (customer = null) => {
    editingCustomer.value = customer;
    if (customer) {
        form.name = customer.name;
        form.phone = customer.phone || '';
        form.address = customer.address || '';
    } else {
        form.name = '';
        form.phone = '';
        form.address = '';
    }
    showModal.value = true;
};

const saveCustomer = async () => {
    saving.value = true;
    try {
        if (editingCustomer.value) {
            await axios.put(`/customers/${editingCustomer.value.id}`, form);
        } else {
            await axios.post('/customers', form);
        }
        showModal.value = false;
        fetchCustomers();
    } catch (e) {
        alert('Gagal menyimpan pelanggan');
    } finally {
        saving.value = false;
    }
};

const deleteCustomer = async (customer) => {
    if (!confirm(`Hapus pelanggan "${customer.name}"?`)) return;
    try {
        await axios.delete(`/customers/${customer.id}`);
        fetchCustomers();
    } catch (e) {
        alert('Gagal menghapus pelanggan');
    }
};

onMounted(fetchCustomers);
</script>
