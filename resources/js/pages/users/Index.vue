<template>
    <div class="space-y-4 animate-fade-in">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-dark-900 dark:text-white">Pengguna</h2>
            <button @click="openModal()" class="btn-primary">
                <PlusIcon class="w-4 h-4 mr-2" />
                Tambah Pengguna
            </button>
        </div>
        
        <div class="card">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Hak Akses</th>
                            <th>QR Login</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id">
                            <td class="font-medium">{{ user.name }}</td>
                            <td>{{ user.email }}</td>
                            <td><span class="badge badge-primary capitalize">{{ user.role }}</span></td>
                            <td>
                                <span v-if="user.role === 'admin'" class="text-xs text-dark-500">Semua Akses</span>
                                <span v-else class="text-xs text-dark-500">{{ formatPermissions(user.permissions) }}</span>
                            </td>
                            <td>
                                <button 
                                    v-if="user.login_token" 
                                    @click="showQrCode(user)"
                                    class="text-xs px-2 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded hover:bg-green-200 dark:hover:bg-green-900/50"
                                >
                                    📱 Lihat QR
                                </button>
                                <button 
                                    v-else
                                    @click="generateQrToken(user)"
                                    class="text-xs px-2 py-1 bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 rounded hover:bg-primary-200 dark:hover:bg-primary-900/50"
                                    :disabled="generatingQr === user.id"
                                >
                                    {{ generatingQr === user.id ? '...' : '+ Buat QR' }}
                                </button>
                            </td>
                            <td><span :class="['badge', user.is_active ? 'badge-success' : 'badge-danger']">{{ user.is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <div class="flex gap-2">
                                    <button @click="openModal(user)" class="p-2 text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg"><PencilIcon class="w-4 h-4" /></button>
                                    <button @click="deleteUser(user)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg"><TrashIcon class="w-4 h-4" /></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- User Form Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="card max-w-lg w-full p-6 animate-scale-in max-h-[90vh] overflow-y-auto">
                    <h3 class="text-lg font-bold text-dark-900 dark:text-white mb-4">{{ editingId ? 'Edit' : 'Tambah' }} Pengguna</h3>
                    <form @submit.prevent="save" class="space-y-4">
                        <input v-model="form.name" type="text" class="input" placeholder="Nama" required />
                        <input v-model="form.email" type="email" class="input" placeholder="Email" required />
                        <input v-model="form.password" type="password" class="input" :placeholder="editingId ? 'Password (kosongkan jika tidak diubah)' : 'Password'" :required="!editingId" />
                        <select v-model="form.role" class="input">
                            <option value="admin">Admin</option>
                            <option value="kasir">Kasir</option>
                        </select>
                        
                        <!-- Permissions Section (only for non-admin) -->
                        <div v-if="form.role !== 'admin'" class="border border-dark-200 dark:border-dark-700 rounded-lg p-4">
                            <h4 class="font-medium text-dark-900 dark:text-white mb-3">Hak Akses Fitur</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <label 
                                    v-for="(label, key) in availablePermissions" 
                                    :key="key"
                                    class="flex items-center gap-2 p-2 rounded hover:bg-dark-50 dark:hover:bg-dark-700 cursor-pointer"
                                >
                                    <input 
                                        type="checkbox" 
                                        :value="key" 
                                        v-model="form.permissions"
                                        class="w-4 h-4 rounded border-dark-300 text-primary-600 focus:ring-primary-500"
                                    />
                                    <span class="text-sm text-dark-700 dark:text-dark-300">{{ label }}</span>
                                </label>
                            </div>
                            <div class="flex gap-2 mt-3">
                                <button type="button" @click="selectAllPermissions" class="text-xs text-primary-600 hover:underline">Pilih Semua</button>
                                <button type="button" @click="deselectAllPermissions" class="text-xs text-dark-500 hover:underline">Hapus Semua</button>
                            </div>
                        </div>
                        <p v-else class="text-sm text-dark-500 italic">Admin memiliki akses ke semua fitur</p>
                        
                        <label class="flex items-center gap-2">
                            <input v-model="form.is_active" type="checkbox" class="w-4 h-4" />
                            <span class="text-sm text-dark-700 dark:text-dark-300">Aktif</span>
                        </label>
                        <div class="flex gap-2 pt-2">
                            <button type="button" @click="showModal = false" class="btn-ghost flex-1">Batal</button>
                            <button type="submit" class="btn-primary flex-1">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- QR Code Modal -->
        <Teleport to="body">
            <div v-if="showQrModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="card max-w-sm w-full p-6 animate-scale-in">
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-dark-900 dark:text-white mb-2">QR Code Login</h3>
                        <p class="text-dark-500 dark:text-dark-400 mb-4">{{ selectedUserForQr?.name }}</p>
                        
                        <div class="bg-white p-4 rounded-xl inline-block mb-4">
                            <div ref="qrCodeContainer"></div>
                        </div>
                        
                        <p class="text-xs text-dark-400 mb-4">Scan QR ini untuk login tanpa password</p>
                        
                        <div class="flex gap-2">
                            <button @click="revokeQrToken" class="btn-ghost flex-1 text-red-500" :disabled="revokingQr">
                                {{ revokingQr ? 'Mencabut...' : '🗑️ Cabut QR' }}
                            </button>
                            <button @click="closeQrModal" class="btn-primary flex-1">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';
import QRCode from 'qrcode';

const users = ref([]);
const showModal = ref(false);
const editingId = ref(null);
const availablePermissions = ref({});
const form = reactive({ 
    name: '', 
    email: '', 
    password: '', 
    role: 'kasir', 
    permissions: [],
    is_active: true 
});

// QR Code state
const showQrModal = ref(false);
const selectedUserForQr = ref(null);
const generatingQr = ref(null);
const revokingQr = ref(false);
const qrCodeContainer = ref(null);

const formatPermissions = (permissions) => {
    if (!permissions || permissions.length === 0) return 'Tidak ada akses';
    if (permissions.length <= 2) {
        return permissions.map(p => availablePermissions.value[p] || p).join(', ');
    }
    return `${permissions.length} fitur`;
};

const selectAllPermissions = () => {
    form.permissions = Object.keys(availablePermissions.value);
};

const deselectAllPermissions = () => {
    form.permissions = [];
};

const openModal = (user = null) => {
    editingId.value = user?.id || null;
    form.name = user?.name || '';
    form.email = user?.email || '';
    form.password = '';
    form.role = user?.role || 'kasir';
    form.permissions = user?.permissions || [];
    form.is_active = user?.is_active ?? true;
    showModal.value = true;
};

const save = async () => {
    try {
        const data = { ...form };
        // If admin, clear permissions (admin has all)
        if (data.role === 'admin') {
            data.permissions = [];
        }
        
        if (editingId.value) {
            await axios.put(`/users/${editingId.value}`, data);
        } else {
            await axios.post('/users', data);
        }
        showModal.value = false;
        fetchUsers();
    } catch (e) {
        alert(e.response?.data?.message || 'Gagal menyimpan');
    }
};

const deleteUser = async (user) => {
    if (!confirm(`Hapus pengguna "${user.name}"?`)) return;
    try {
        await axios.delete(`/users/${user.id}`);
        fetchUsers();
    } catch (e) {
        alert('Gagal menghapus');
    }
};

// QR Code Functions
const generateQrToken = async (user) => {
    generatingQr.value = user.id;
    try {
        const res = await axios.post(`/users/${user.id}/generate-qr-token`);
        // Update user in list with new token
        const idx = users.value.findIndex(u => u.id === user.id);
        if (idx > -1) {
            users.value[idx].login_token = res.data.login_token;
        }
        // Show QR modal
        showQrCode(users.value[idx]);
    } catch (e) {
        alert(e.response?.data?.message || 'Gagal membuat QR token');
    } finally {
        generatingQr.value = null;
    }
};

const showQrCode = async (user) => {
    selectedUserForQr.value = user;
    showQrModal.value = true;
    
    await nextTick();
    
    // Generate QR code
    if (qrCodeContainer.value && user.login_token) {
        qrCodeContainer.value.innerHTML = '';
        const canvas = document.createElement('canvas');
        await QRCode.toCanvas(canvas, user.login_token, {
            width: 200,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#ffffff'
            }
        });
        qrCodeContainer.value.appendChild(canvas);
    }
};

const closeQrModal = () => {
    showQrModal.value = false;
    selectedUserForQr.value = null;
};

const revokeQrToken = async () => {
    if (!confirm('Cabut QR login untuk user ini? User tidak akan bisa login dengan QR lagi.')) return;
    
    revokingQr.value = true;
    try {
        await axios.post(`/users/${selectedUserForQr.value.id}/revoke-qr-token`);
        // Update user in list
        const idx = users.value.findIndex(u => u.id === selectedUserForQr.value.id);
        if (idx > -1) {
            users.value[idx].login_token = null;
        }
        closeQrModal();
    } catch (e) {
        alert(e.response?.data?.message || 'Gagal mencabut QR token');
    } finally {
        revokingQr.value = false;
    }
};

const fetchUsers = async () => {
    try {
        const res = await axios.get('/users');
        users.value = res.data.data || res.data;
    } catch (e) {
        users.value = [
            { id: 1, name: 'Admin', email: 'admin@kasirku.com', role: 'admin', permissions: [], is_active: true },
            { id: 2, name: 'Kasir 1', email: 'kasir@kasirku.com', role: 'kasir', permissions: ['pos', 'products'], is_active: true }
        ];
    }
};

const fetchPermissions = async () => {
    try {
        const res = await axios.get('/users/available/permissions');
        availablePermissions.value = res.data.data || {};
    } catch (e) {
        availablePermissions.value = {
            'dashboard': 'Dashboard',
            'pos': 'Kasir (POS)',
            'products': 'Produk',
            'categories': 'Kategori',
            'transactions': 'Transaksi',
            'customers': 'Pelanggan',
            'debts': 'Piutang',
            'reports': 'Laporan',
            'settings': 'Pengaturan',
        };
    }
};

onMounted(() => {
    fetchUsers();
    fetchPermissions();
});
</script>
