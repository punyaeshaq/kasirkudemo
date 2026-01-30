<template>
    <div class="w-full animate-fade-in">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-dark-900 dark:text-white">Backup Database</h1>
                <p class="text-dark-500 dark:text-dark-400 mt-1">Kelola backup dan pulihkan database</p>
            </div>
            <div class="flex gap-2">
                <button 
                    @click="showUploadModal = true" 
                    class="btn-secondary flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Upload & Pulihkan
                </button>
                <button 
                    @click="createBackup" 
                    class="btn-primary flex items-center gap-2"
                    :disabled="creating"
                >
                    <svg v-if="creating" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ creating ? 'Membuat...' : 'Buat Backup' }}
                </button>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card p-4 mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-blue-700 dark:text-blue-300">
                    <p class="font-semibold mb-1">Petunjuk Backup Database:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Backup menyimpan seluruh data transaksi, produk, kategori, dan pengaturan</li>
                        <li>Disarankan membuat backup secara rutin (harian/mingguan)</li>
                        <li>Download file backup ke komputer untuk penyimpanan aman</li>
                        <li>Gunakan "Pulihkan" untuk mengembalikan data dari backup</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Backups List -->
        <div class="card">
            <div class="p-4 border-b border-dark-200 dark:border-dark-700">
                <h2 class="text-lg font-semibold text-dark-900 dark:text-white">Daftar Backup</h2>
            </div>

            <div v-if="loading" class="p-8 text-center">
                <div class="animate-spin w-8 h-8 border-4 border-primary-500 border-t-transparent rounded-full mx-auto"></div>
                <p class="text-dark-500 mt-3">Memuat daftar backup...</p>
            </div>

            <div v-else-if="backups.length === 0" class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-dark-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
                <p class="text-dark-500 dark:text-dark-400">Belum ada backup</p>
                <p class="text-sm text-dark-400 dark:text-dark-500 mt-1">Klik "Buat Backup" untuk membuat backup pertama</p>
            </div>

            <div v-else class="divide-y divide-dark-200 dark:divide-dark-700">
                <div 
                    v-for="backup in backups" 
                    :key="backup.filename"
                    class="p-4 flex items-center justify-between hover:bg-dark-50 dark:hover:bg-dark-700/50 transition-colors"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-dark-900 dark:text-white">{{ backup.filename }}</p>
                            <p class="text-sm text-dark-500 dark:text-dark-400">
                                {{ formatDate(backup.created_at) }} • {{ backup.size }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button 
                            @click="downloadBackup(backup.filename)"
                            class="btn-ghost text-sm flex items-center gap-1"
                            :disabled="downloading === backup.filename"
                        >
                            <svg v-if="downloading === backup.filename" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            {{ downloading === backup.filename ? 'Downloading...' : 'Download' }}
                        </button>
                        <button 
                            @click="confirmRestore(backup)"
                            class="btn-secondary text-sm flex items-center gap-1"
                            :disabled="restoring"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Pulihkan
                        </button>
                        <button 
                            @click="confirmDelete(backup)"
                            class="btn-ghost text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Restore Confirmation Modal -->
        <Teleport to="body">
            <div v-if="showRestoreModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="card max-w-md w-full p-6 animate-scale-in">
                    <div class="text-center">
                        <div class="w-16 h-16 rounded-full bg-yellow-100 dark:bg-yellow-900/30 mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-dark-900 dark:text-white mb-2">Konfirmasi Pulihkan</h3>
                        <p class="text-dark-600 dark:text-dark-400 mb-4">
                            Data saat ini akan <strong class="text-red-500">diganti</strong> dengan data dari backup:<br>
                            <code class="text-sm bg-dark-100 dark:bg-dark-700 px-2 py-1 rounded">{{ selectedBackup?.filename }}</code>
                        </p>
                        <p class="text-sm text-red-500 mb-4 font-medium">
                            ⚠️ Tindakan ini tidak dapat dibatalkan!
                        </p>
                        <div class="flex gap-2">
                            <button @click="showRestoreModal = false" class="btn-ghost flex-1">Batal</button>
                            <button 
                                @click="restoreBackup" 
                                class="btn-primary bg-yellow-500 hover:bg-yellow-600 flex-1"
                                :disabled="restoring"
                            >
                                {{ restoring ? 'Memulihkan...' : 'Ya, Pulihkan' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="card max-w-md w-full p-6 animate-scale-in">
                    <div class="text-center">
                        <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-dark-900 dark:text-white mb-2">Hapus Backup?</h3>
                        <p class="text-dark-600 dark:text-dark-400 mb-4">
                            Backup "<strong>{{ selectedBackup?.filename }}</strong>" akan dihapus permanen.
                        </p>
                        <div class="flex gap-2">
                            <button @click="showDeleteModal = false" class="btn-ghost flex-1">Batal</button>
                            <button 
                                @click="deleteBackup" 
                                class="btn-primary bg-red-500 hover:bg-red-600 flex-1"
                                :disabled="deleting"
                            >
                                {{ deleting ? 'Menghapus...' : 'Hapus' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Upload Restore Modal -->
        <Teleport to="body">
            <div v-if="showUploadModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="card max-w-lg w-full p-6 animate-scale-in">
                    <div class="text-center">
                        <div class="w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/30 mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-dark-900 dark:text-white mb-2">Pulihkan dari File</h3>
                        <p class="text-dark-600 dark:text-dark-400 mb-4">
                            Pilih file backup (.sql) dari komputer Anda
                        </p>
                        
                        <div class="mb-4">
                            <label 
                                class="block w-full p-6 border-2 border-dashed rounded-xl cursor-pointer"
                                :class="uploadFile ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-dark-300 dark:border-dark-600 hover:border-primary-400'"
                            >
                                <input 
                                    type="file" 
                                    @change="handleFileSelect"
                                    accept=".sql,.txt"
                                    class="hidden"
                                />
                                <div v-if="uploadFile" class="text-primary-600 dark:text-primary-400">
                                    <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="font-medium">{{ uploadFile.name }}</p>
                                    <p class="text-sm opacity-75">{{ formatFileSize(uploadFile.size) }}</p>
                                </div>
                                <div v-else class="text-dark-400">
                                    <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p>Klik untuk memilih file</p>
                                    <p class="text-sm">atau drag & drop file .sql</p>
                                </div>
                            </label>
                        </div>

                        <p class="text-sm text-yellow-600 dark:text-yellow-400 mb-4">
                            ⚠️ Data saat ini akan diganti dengan data dari file backup!
                        </p>
                        
                        <div class="flex gap-2">
                            <button @click="closeUploadModal" class="btn-ghost flex-1">Batal</button>
                            <button 
                                @click="restoreFromUpload" 
                                class="btn-primary flex-1"
                                :disabled="!uploadFile || uploading"
                            >
                                {{ uploading ? 'Memulihkan...' : 'Pulihkan' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const backups = ref([]);
const loading = ref(true);
const creating = ref(false);
const restoring = ref(false);
const deleting = ref(false);
const downloading = ref(null);
const uploading = ref(false);
const showRestoreModal = ref(false);
const showDeleteModal = ref(false);
const showUploadModal = ref(false);
const selectedBackup = ref(null);
const uploadFile = ref(null);

const fetchBackups = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/backup');
        backups.value = res.data.data || [];
    } catch (e) {
        console.error('Failed to fetch backups', e);
    } finally {
        loading.value = false;
    }
};

const createBackup = async () => {
    creating.value = true;
    try {
        const res = await axios.post('/backup/create');
        if (res.data.success) {
            alert(`✅ ${res.data.message}\nFile: ${res.data.filename}`);
            fetchBackups();
        } else {
            alert('❌ ' + res.data.message);
        }
    } catch (e) {
        alert('❌ Gagal membuat backup: ' + (e.response?.data?.message || e.message));
    } finally {
        creating.value = false;
    }
};

const downloadBackup = async (filename) => {
    downloading.value = filename;
    try {
        const response = await axios.get(`/backup/download/${filename}`, {
            responseType: 'blob'
        });
        
        // Create blob URL and trigger download
        const blob = new Blob([response.data], { type: 'application/sql' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    } catch (e) {
        alert('❌ Gagal download: ' + (e.response?.data?.message || e.message));
    } finally {
        downloading.value = null;
    }
};

const confirmRestore = (backup) => {
    selectedBackup.value = backup;
    showRestoreModal.value = true;
};

const restoreBackup = async () => {
    if (!selectedBackup.value) return;
    
    restoring.value = true;
    try {
        const res = await axios.post('/backup/restore', {
            filename: selectedBackup.value.filename
        });
        if (res.data.success) {
            alert('✅ ' + res.data.message);
            showRestoreModal.value = false;
        } else {
            alert('❌ ' + res.data.message);
        }
    } catch (e) {
        alert('❌ Gagal memulihkan: ' + (e.response?.data?.message || e.message));
    } finally {
        restoring.value = false;
    }
};

const confirmDelete = (backup) => {
    selectedBackup.value = backup;
    showDeleteModal.value = true;
};

const deleteBackup = async () => {
    if (!selectedBackup.value) return;
    
    deleting.value = true;
    try {
        const res = await axios.delete(`/backup/${selectedBackup.value.filename}`);
        if (res.data.success) {
            alert('✅ ' + res.data.message);
            showDeleteModal.value = false;
            fetchBackups();
        } else {
            alert('❌ ' + res.data.message);
        }
    } catch (e) {
        alert('❌ Gagal menghapus: ' + (e.response?.data?.message || e.message));
    } finally {
        deleting.value = false;
    }
};

const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        uploadFile.value = file;
    }
};

const closeUploadModal = () => {
    showUploadModal.value = false;
    uploadFile.value = null;
};

const restoreFromUpload = async () => {
    if (!uploadFile.value) return;
    
    uploading.value = true;
    try {
        const formData = new FormData();
        formData.append('file', uploadFile.value);
        
        const res = await axios.post('/backup/restore-upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        if (res.data.success) {
            alert('✅ ' + res.data.message);
            closeUploadModal();
            fetchBackups(); // Refresh backup list
        } else {
            alert('❌ ' + res.data.message);
        }
    } catch (e) {
        alert('❌ Gagal memulihkan: ' + (e.response?.data?.message || e.message));
    } finally {
        uploading.value = false;
    }
};

onMounted(() => {
    fetchBackups();
});
</script>
