<template>
    <div class="max-w-3xl animate-fade-in">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-dark-900 dark:text-white">
                {{ isEdit ? 'Edit Diskon' : 'Tambah Diskon Baru' }}
            </h1>
            <router-link to="/discounts" class="btn-ghost">
                ← Kembali
            </router-link>
        </div>

        <div class="card p-6">
            <form @submit.prevent="save" class="space-y-6">
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Nama Diskon</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="input" 
                            placeholder="Contoh: Diskon Kemerdekaan, Promo Weekend"
                            required
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Tipe Diskon</label>
                        <select v-model="form.type" class="input">
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed">Nominal Tetap (Rp)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">
                            {{ form.type === 'percentage' ? 'Nilai Persentase (%)' : 'Nilai Potongan (Rp)' }}
                        </label>
                        <input 
                            v-model="form.value" 
                            type="number" 
                            class="input" 
                            min="0"
                            :max="form.type === 'percentage' ? 100 : undefined"
                            required
                        />
                    </div>
                </div>

                <!-- Conditions -->
                <div class="border-t border-dark-200 dark:border-dark-700 pt-6">
                    <h3 class="text-lg font-medium text-dark-900 dark:text-white mb-4">Syarat & Ketentuan</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Minimal Belanja (Opsional)</label>
                            <input 
                                v-model="form.min_purchase" 
                                type="number" 
                                class="input" 
                                placeholder="0"
                            />
                            <p class="text-xs text-dark-500 mt-1">Kosongkan jika tidak ada minimal belanja</p>
                        </div>
                        
                        <div class="flex items-center pt-6">
                            <label class="flex items-center cursor-pointer">
                                <input v-model="form.is_active" type="checkbox" class="form-checkbox h-5 w-5 text-primary-600 rounded" />
                                <span class="ml-2 text-dark-700 dark:text-dark-300">Status Aktif</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Tanggal Mulai (Opsional)</label>
                            <input 
                                v-model="form.start_date" 
                                type="date" 
                                class="input" 
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-dark-700 dark:text-dark-300 mb-1">Tanggal Berakhir (Opsional)</label>
                            <input 
                                v-model="form.end_date" 
                                type="date" 
                                class="input" 
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-dark-200 dark:border-dark-700">
                    <router-link to="/discounts" class="btn-ghost">Batal</router-link>
                    <button type="submit" class="btn-primary" :disabled="processing">
                        {{ processing ? 'Menyimpan...' : 'Simpan Diskon' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const isEdit = computed(() => !!route.params.id);
const processing = ref(false);

const form = reactive({
    name: '',
    type: 'percentage',
    value: '',
    min_purchase: '',
    start_date: '',
    end_date: '',
    is_active: true
});

onMounted(async () => {
    if (isEdit.value) {
        try {
            const { data } = await axios.get(`/discounts/${route.params.id}`);
            const discount = data.data;
            Object.assign(form, {
                ...discount,
                value: Number(discount.value),
                min_purchase: Number(discount.min_purchase)
            });
        } catch (e) {
            router.push('/discounts');
        }
    }
});

const save = async () => {
    processing.value = true;
    try {
        const payload = { ...form };
        
        // Handle optional fields
        if (!payload.min_purchase) payload.min_purchase = null;
        if (!payload.start_date) payload.start_date = null;
        if (!payload.end_date) payload.end_date = null;

        if (isEdit.value) {
            await axios.put(`/discounts/${route.params.id}`, payload);
        } else {
            await axios.post('/discounts', payload);
        }
        
        router.push('/discounts');
    } catch (e) {
        console.error(e);
        alert(e.response?.data?.message || 'Gagal menyimpan data');
    } finally {
        processing.value = false;
    }
};
</script>
