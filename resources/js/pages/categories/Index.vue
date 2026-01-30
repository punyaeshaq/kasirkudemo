<template>
    <div class="space-y-4 animate-fade-in">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-dark-900 dark:text-white">Kategori</h2>
            <button @click="openModal()" class="btn-primary">
                <PlusIcon class="w-4 h-4 mr-2" />
                Tambah Kategori
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="cat in categories" :key="cat.id" class="card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-dark-900 dark:text-white">{{ cat.name }}</h3>
                        <p class="text-sm text-dark-500">{{ cat.products_count || 0 }} produk</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="openModal(cat)" class="p-2 text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg">
                            <PencilIcon class="w-4 h-4" />
                        </button>
                        <button @click="deleteCategory(cat)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                            <TrashIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="card max-w-md w-full p-6 animate-scale-in">
                    <h3 class="text-lg font-bold text-dark-900 dark:text-white mb-4">
                        {{ editingId ? 'Edit Kategori' : 'Tambah Kategori' }}
                    </h3>
                    <form @submit.prevent="save">
                        <input v-model="form.name" type="text" class="input mb-4" placeholder="Nama kategori" required />
                        <div class="flex gap-2">
                            <button type="button" @click="showModal = false" class="btn-ghost flex-1">Batal</button>
                            <button type="submit" class="btn-primary flex-1">Simpan</button>
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

const categories = ref([]);
const showModal = ref(false);
const editingId = ref(null);
const form = reactive({ name: '' });

const openModal = (cat = null) => {
    editingId.value = cat?.id || null;
    form.name = cat?.name || '';
    showModal.value = true;
};

const save = async () => {
    try {
        if (editingId.value) {
            await axios.put(`/categories/${editingId.value}`, form);
        } else {
            await axios.post('/categories', form);
        }
        showModal.value = false;
        fetchCategories();
    } catch (e) {
        alert('Gagal menyimpan');
    }
};

const deleteCategory = async (cat) => {
    if (!confirm(`Hapus kategori "${cat.name}"?`)) return;
    try {
        await axios.delete(`/categories/${cat.id}`);
        fetchCategories();
    } catch (e) {
        alert('Gagal menghapus');
    }
};

const fetchCategories = async () => {
    try {
        const res = await axios.get('/categories');
        categories.value = res.data.data || res.data;
    } catch (e) {
        categories.value = [{ id: 1, name: 'Makanan', products_count: 5 }, { id: 2, name: 'Minuman', products_count: 8 }];
    }
};

onMounted(fetchCategories);
</script>
