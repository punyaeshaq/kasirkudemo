<template>
    <div class="animate-fade-in">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-dark-900 dark:text-white">Manajemen Diskon</h1>
            <router-link to="/discounts/create" class="btn-primary">
                <PlusIcon class="w-5 h-5 mr-2" />
                Tambah Diskon
            </router-link>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Nama</th>
                            <th class="text-left">Tipe</th>
                            <th class="text-left">Nilai</th>
                            <th class="text-left">Periode</th>
                            <th class="text-left">Min. Belanja</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" class="animate-pulse">
                            <td colspan="7" class="text-center py-8 text-dark-500">Memuat data...</td>
                        </tr>
                        <tr v-else-if="discounts.length === 0">
                            <td colspan="7" class="text-center py-8 text-dark-500">Belum ada data diskon</td>
                        </tr>
                        <tr v-for="discount in discounts" :key="discount.id" class="hover:bg-dark-50 dark:hover:bg-dark-700/50">
                            <td class="font-medium text-dark-900 dark:text-white">{{ discount.name }}</td>
                            <td class="capitalize">{{ discount.type === 'percentage' ? 'Persentase' : 'Nominal' }}</td>
                            <td class="font-bold text-primary-600">
                                {{ discount.type === 'percentage' ? formatPercentage(discount.value) + '%' : formatCurrency(discount.value) }}
                            </td>
                            <td class="text-sm text-dark-600 dark:text-dark-400">
                                <div v-if="!discount.start_date && !discount.end_date">Selamanya</div>
                                <div v-else>
                                    {{ formatDate(discount.start_date) }} - {{ formatDate(discount.end_date) }}
                                </div>
                            </td>
                            <td class="text-sm">
                                {{ discount.min_purchase > 0 ? formatCurrency(discount.min_purchase) : '-' }}
                            </td>
                            <td class="text-center">
                                <span 
                                    class="px-2 py-1 text-xs rounded-full"
                                    :class="discount.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'"
                                >
                                    {{ discount.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-right space-x-2">
                                <router-link :to="`/discounts/${discount.id}/edit`" class="text-blue-600 hover:text-blue-800 p-2 inline-block">
                                    <PencilIcon class="w-5 h-5" />
                                </router-link>
                                <button @click="deleteDiscount(discount.id)" class="text-red-600 hover:text-red-800 p-2 inline-block">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';

const discounts = ref([]);
const loading = ref(true);

const fetchDiscounts = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/discounts');
        discounts.value = response.data.data;
    } catch (error) {
        console.error('Error fetching discounts:', error);
    } finally {
        loading.value = false;
    }
};

const deleteDiscount = async (id) => {
    if (!confirm('Apakah Anda yakin ingin menghapus diskon ini?')) return;
    
    try {
        await axios.delete(`/discounts/${id}`);
        fetchDiscounts();
    } catch (error) {
        alert('Gagal menghapus diskon');
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatPercentage = (value) => {
    const num = parseFloat(value);
    return Number.isInteger(num) ? num.toString() : num.toString().replace(/\.?0+$/, '');
};

const formatDate = (date) => {
    if (!date) return '∞';
    return format(new Date(date), 'd MMM yyyy', { locale: id });
};

onMounted(() => {
    fetchDiscounts();
});
</script>
