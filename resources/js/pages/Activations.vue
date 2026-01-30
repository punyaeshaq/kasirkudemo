<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-dark-900 dark:text-white">Manajemen Aktivasi</h1>
                <p class="text-dark-500 dark:text-dark-400 mt-1">Kelola lisensi dan aktivasi aplikasi</p>
            </div>
            <button @click="loadActivations" class="btn-ghost">
                🔄 Refresh
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                        <span class="text-xl">✅</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-dark-900 dark:text-white">{{ stats.active }}</p>
                        <p class="text-sm text-dark-500">Lisensi Aktif</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-500/20 rounded-lg flex items-center justify-center">
                        <span class="text-xl">⏰</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-dark-900 dark:text-white">{{ stats.expired }}</p>
                        <p class="text-sm text-dark-500">Lisensi Expired</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                        <span class="text-xl">📊</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-dark-900 dark:text-white">{{ stats.total }}</p>
                        <p class="text-sm text-dark-500">Total Lisensi</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card overflow-hidden">
            <div class="p-4 border-b border-dark-200 dark:border-dark-700">
                <h3 class="font-semibold text-dark-900 dark:text-white">Daftar Aktivasi</h3>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="p-8 text-center">
                <div class="spinner mx-auto mb-2"></div>
                <p class="text-dark-500">Memuat data...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="activations.length === 0" class="p-8 text-center">
                <p class="text-dark-500">Belum ada data aktivasi</p>
            </div>

            <!-- Table Data -->
            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-dark-50 dark:bg-dark-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dark-500 uppercase">Machine ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dark-500 uppercase">Tanggal Aktivasi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dark-500 uppercase">Expired</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dark-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dark-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-200 dark:divide-dark-700">
                        <tr v-for="item in activations" :key="item.machine_id" class="hover:bg-dark-50 dark:hover:bg-dark-800/50">
                            <td class="px-4 py-3">
                                <code class="text-sm bg-dark-100 dark:bg-dark-700 px-2 py-1 rounded">{{ item.machine_id }}</code>
                            </td>
                            <td class="px-4 py-3 text-sm text-dark-600 dark:text-dark-400">
                                {{ formatDate(item.activated_at) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-dark-600 dark:text-dark-400">
                                {{ item.expired_at }}
                                <span v-if="!item.is_expired" class="text-xs text-green-600 ml-1">
                                    ({{ item.days_remaining }} hari lagi)
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span v-if="item.is_expired" class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                    Expired
                                </span>
                                <span v-else class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                    Aktif
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button 
                                    @click="confirmRevoke(item)" 
                                    class="text-red-600 hover:text-red-800 text-sm font-medium"
                                    :disabled="revoking === item.machine_id"
                                >
                                    {{ revoking === item.machine_id ? 'Menghapus...' : '🗑️ Hapus' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Revoke Confirmation Modal -->
        <Teleport to="body">
            <div v-if="showRevokeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
                <div class="bg-white dark:bg-dark-800 rounded-xl p-6 max-w-md w-full shadow-xl">
                    <h3 class="text-lg font-bold text-dark-900 dark:text-white mb-2">Konfirmasi Hapus Aktivasi</h3>
                    <p class="text-dark-600 dark:text-dark-400 mb-4">
                        Yakin ingin menghapus aktivasi untuk Machine ID:
                        <code class="bg-dark-100 dark:bg-dark-700 px-2 py-1 rounded text-sm block mt-2">{{ selectedItem?.machine_id }}</code>
                    </p>
                    <p class="text-sm text-red-600 mb-4">
                        ⚠️ Perangkat dengan Machine ID ini perlu diaktivasi ulang untuk menggunakan aplikasi.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button @click="showRevokeModal = false" class="btn-ghost">Batal</button>
                        <button @click="revokeActivation" class="btn-primary bg-red-600 hover:bg-red-700">
                            Hapus Aktivasi
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const activations = ref([]);
const loading = ref(false);
const revoking = ref(null);
const showRevokeModal = ref(false);
const selectedItem = ref(null);

const stats = computed(() => {
    const active = activations.value.filter(a => !a.is_expired).length;
    const expired = activations.value.filter(a => a.is_expired).length;
    return {
        active,
        expired,
        total: activations.value.length
    };
});

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const loadActivations = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/activations');
        activations.value = res.data.data;
    } catch (e) {
        console.error('Failed to load activations:', e);
    } finally {
        loading.value = false;
    }
};

const confirmRevoke = (item) => {
    selectedItem.value = item;
    showRevokeModal.value = true;
};

const revokeActivation = async () => {
    if (!selectedItem.value) return;
    
    revoking.value = selectedItem.value.machine_id;
    showRevokeModal.value = false;
    
    try {
        await axios.post('/activations/revoke', {
            machine_id: selectedItem.value.machine_id
        });
        
        // Remove from list
        activations.value = activations.value.filter(
            a => a.machine_id !== selectedItem.value.machine_id
        );
    } catch (e) {
        alert('Gagal menghapus aktivasi: ' + (e.response?.data?.message || e.message));
    } finally {
        revoking.value = null;
        selectedItem.value = null;
    }
};

onMounted(() => {
    loadActivations();
});
</script>
