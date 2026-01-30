<template>
    <div class="space-y-4 animate-fade-in">
        <div class="flex items-center justify-between">
            <div class="relative flex-1 max-w-md">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-dark-400" />
                <input v-model="search" type="text" class="input-search" placeholder="Cari produk..." />
            </div>
            <div class="flex gap-2">
                <router-link to="/products/barcode/all" class="btn-ghost">
                    <PrinterIcon class="w-4 h-4 mr-2" />
                    Cetak Semua Barcode
                </router-link>
                <router-link to="/products/create" class="btn-primary">
                    <PlusIcon class="w-4 h-4 mr-2" />
                    Tambah Produk
                </router-link>
            </div>
        </div>
        
        <div class="card">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Barcode</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in filteredProducts" :key="product.id">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-dark-100 dark:bg-dark-700 flex items-center justify-center">
                                        <img v-if="product.image" :src="product.image" class="w-full h-full object-cover rounded-lg" />
                                        <CubeIcon v-else class="w-5 h-5 text-dark-400" />
                                    </div>
                                    <span class="font-medium">{{ product.name }}</span>
                                </div>
                            </td>
                            <td><code class="text-xs bg-dark-100 dark:bg-dark-700 px-2 py-1 rounded">{{ product.barcode }}</code></td>
                            <td>{{ product.category?.name || '-' }}</td>
                            <td class="font-semibold">{{ formatCurrency(product.price) }}</td>
                            <td>
                                <span :class="['badge', product.stock > 10 ? 'badge-success' : product.stock > 0 ? 'badge-warning' : 'badge-danger']">
                                    {{ product.stock }}
                                </span>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <router-link :to="`/products/${product.id}/barcode`" class="p-2 text-dark-500 hover:bg-dark-100 dark:hover:bg-dark-700 rounded-lg" title="Cetak Barcode">
                                        <PrinterIcon class="w-4 h-4" />
                                    </router-link>
                                    <router-link :to="`/products/${product.id}/edit`" class="p-2 text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg">
                                        <PencilIcon class="w-4 h-4" />
                                    </router-link>
                                    <button @click="deleteProduct(product)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
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
import { MagnifyingGlassIcon, PlusIcon, CubeIcon, PencilIcon, TrashIcon, PrinterIcon } from '@heroicons/vue/24/outline';

const search = ref('');
const products = ref([]);

const filteredProducts = computed(() => {
    if (!search.value) return products.value;
    const q = search.value.toLowerCase();
    return products.value.filter(p => p.name.toLowerCase().includes(q) || p.barcode.includes(q));
});

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);

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
        const res = await axios.get('/products');
        products.value = res.data.data || res.data;
    } catch (e) {
        products.value = [
            { id: 1, name: 'Indomie Goreng', barcode: '8991002101202', price: 3500, stock: 100, category: { name: 'Makanan' } },
            { id: 2, name: 'Aqua 600ml', barcode: '8992001001012', price: 4000, stock: 200, category: { name: 'Minuman' } },
        ];
    }
});
</script>
