<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="text-2xl font-bold text-dark-900 dark:text-white tracking-tight hidden">Produk</h2>
            
            <div class="flex flex-col sm:flex-row gap-3 w-full">
                <!-- Filters Group -->
                <div class="flex flex-1 gap-3">
                    <!-- Search -->
                     <div class="relative flex-1">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-dark-400" />
                        <input 
                            v-model="search" 
                            type="text" 
                            class="input pl-10 rounded-full bg-white dark:bg-dark-800 border-none shadow-sm focus:ring-2 focus:ring-primary-500" 
                            placeholder="Cari produk..." 
                        />
                    </div>
                    
                    <!-- Category Filter -->
                    <select v-model="selectedCategory" class="input w-40 rounded-full bg-white dark:bg-dark-800 border-none shadow-sm cursor-pointer">
                        <option value="">Semua Kategori</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>
                
                <!-- Action Button -->
                <router-link to="/products/create" class="btn-primary rounded-full px-6 flex items-center gap-2 whitespace-nowrap shadow-md shadow-primary-500/20">
                    <PlusIcon class="w-5 h-5" />
                    <span>Tambah Produk</span>
                </router-link>
            </div>
        </div>
        
        <!-- Product List -->
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="pl-6 w-16">No</th>
                            <th class="w-24">Thumbnail</th>
                            <th>Nama Produk</th>
                            <th>Barcode</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th class="text-center">Stok</th>
                            <th class="pr-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filteredProducts.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center text-dark-400">
                                <div class="flex flex-col items-center justify-center">
                                    <ArchiveBoxIcon class="w-12 h-12 mb-3 text-dark-300 dark:text-dark-600" />
                                    <p>Tidak ada produk yang ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        <tr 
                            v-for="(product, index) in filteredProducts" 
                            :key="product.id"
                            class="group"
                        >
                            <td class="pl-6 text-dark-500 font-medium">{{ index + 1 }}</td>
                            <td>
                                <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-dark-700 flex items-center justify-center overflow-hidden border border-gray-200 dark:border-dark-600">
                                    <img v-if="product.image" :src="product.image" class="w-full h-full object-cover" />
                                    <CubeIcon v-else class="w-6 h-6 text-dark-300 dark:text-dark-500" />
                                </div>
                            </td>
                            <td>
                                <div class="font-semibold text-dark-900 dark:text-white">{{ product.name }}</div>
                            </td>
                            <td>
                                <span class="text-xs font-mono text-dark-500">{{ product.barcode || '-' }}</span>
                            </td>
                            <td>
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-secondary-50 text-secondary-700 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-100 dark:border-secondary-700/50">
                                    {{ product.category?.name || 'Umum' }}
                                </span>
                            </td>
                            <td class="font-bold text-dark-900 dark:text-white">
                                {{ formatCurrency(product.price) }}
                            </td>
                            <td class="text-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" :checked="product.stock > 0" class="sr-only peer" disabled>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full dark:bg-dark-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500"></div>
                                    <span class="ml-2 text-xs font-medium" :class="product.stock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500'">{{ product.stock }}</span>
                                </label>
                            </td>
                            <td class="pr-6 text-center">
                                <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <router-link :to="{ name: 'products.edit', params: { id: product.id } }" class="p-2 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 transition-colors" title="Edit">
                                        <PencilIcon class="w-4 h-4" />
                                    </router-link>
                                    <button @click="deleteProduct(product)" class="p-2 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400 dark:hover:bg-rose-900/50 transition-colors" title="Hapus">
                                        <TrashIcon class="w-4 h-4" />
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
import { ref, computed, onMounted } from 'vue';
import { MagnifyingGlassIcon, PlusIcon, CubeIcon, PencilIcon, TrashIcon, PrinterIcon, ArchiveBoxIcon } from '@heroicons/vue/24/outline';


const search = ref('');
const products = ref([]);
const categories = ref([]);
const selectedCategory = ref('');

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);

const filteredProducts = computed(() => {
    let result = products.value;

    if (selectedCategory.value) {
        result = result.filter(p => p.category_id === selectedCategory.value);
    }

    if (search.value) {
        const q = search.value.toLowerCase();
        result = result.filter(p => p.name.toLowerCase().includes(q) || p.barcode.includes(q));
    }

    return result;
});

const deleteProduct = async (product) => {
    if (!confirm(`Hapus produk "${product.name}"?`)) return;
    try {
        await axios.delete(`/products/${product.id}`);
        products.value = products.value.filter(p => p.id !== product.id);
    } catch (e) {
        alert('Gagal menghapus produk');
    }
};

onMounted(async () => {
    try {
        const [productsRes, categoriesRes] = await Promise.all([
            axios.get('/products'),
            axios.get('/categories')
        ]);
        products.value = productsRes.data.data || productsRes.data;
        categories.value = categoriesRes.data.data || categoriesRes.data;
    } catch (e) {
        // Fallback or error handling
        products.value = [];
        categories.value = [];
    }
});
</script>
