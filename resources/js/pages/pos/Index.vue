<template>
    <div class="h-[calc(100vh-7rem)] flex gap-4 animate-fade-in">
        <!-- Products Grid -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Search & Categories -->
            <div class="mb-4 space-y-3">
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-dark-400 pointer-events-none" />
                        <input 
                            v-model="search"
                            type="text" 
                            class="input w-full pl-10" 
                            placeholder="Cari produk (F1) atau scan barcode"
                            @keyup.enter="searchProduct"
                            ref="searchInput"
                        />
                    </div>
                    <button 
                        @click="openBarcodeScanner"
                        class="btn-primary flex items-center gap-2 px-4"
                        title="Scan Barcode dengan Kamera"
                    >
                        <CameraIcon class="w-5 h-5" />
                        <span class="hidden sm:inline">Scan (F4)</span>
                    </button>
                </div>
                
                <div class="flex gap-2 overflow-x-auto pb-2">
                    <button 
                        @click="selectedCategory = null"
                        :class="['btn', !selectedCategory ? 'btn-primary' : 'btn-ghost']"
                    >
                        Semua
                    </button>
                    <button 
                        v-for="cat in categories" 
                        :key="cat.id"
                        @click="selectedCategory = cat.id"
                        :class="['btn whitespace-nowrap', selectedCategory === cat.id ? 'btn-primary' : 'btn-ghost']"
                    >
                        {{ cat.name }}
                    </button>
                </div>
            </div>
            
            <!-- Products -->
            <div class="flex-1 overflow-y-auto">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    <div 
                        v-for="product in filteredProducts" 
                        :key="product.id"
                        @click="addToCart(product)"
                        class="card p-3 cursor-pointer hover:scale-[1.02] hover:shadow-xl transition-all duration-200"
                        :class="{ 'opacity-50 cursor-not-allowed': product.stock === 0 }"
                    >
                        <div class="aspect-square bg-dark-100 dark:bg-dark-700 rounded-lg mb-2 flex items-center justify-center overflow-hidden">
                            <img 
                                v-if="product.image" 
                                :src="product.image" 
                                :alt="product.name"
                                class="w-full h-full object-cover"
                            />
                            <CubeIcon v-else class="w-12 h-12 text-dark-300" />
                        </div>
                        <h4 class="text-sm font-medium text-dark-900 dark:text-white truncate">
                            {{ product.name }}
                        </h4>
                        <p class="text-xs text-dark-500 dark:text-dark-400 mt-0.5">
                            Stok: {{ product.stock }}
                        </p>
                        <p class="text-sm font-bold text-primary-600 dark:text-primary-400 mt-1">
                            {{ formatCurrency(product.price) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cart Sidebar -->
        <div class="w-80 lg:w-96 flex flex-col card">
            <!-- Cart Header -->
            <div class="p-4 border-b border-dark-200 dark:border-dark-700">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-dark-900 dark:text-white">
                        Keranjang ({{ cartStore.itemCount }})
                    </h3>
                    <button 
                        v-if="cartStore.items.length"
                        @click="clearCart"
                        class="text-sm text-red-500 hover:text-red-600"
                    >
                        Hapus Semua (F3)
                    </button>
                </div>
            </div>
            
            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <div v-if="!cartStore.items.length" class="text-center py-12 text-dark-400">
                    <ShoppingCartIcon class="w-16 h-16 mx-auto mb-3 opacity-50" />
                    <p>Keranjang kosong</p>
                </div>
                
                <div 
                    v-for="item in cartStore.items" 
                    :key="item.id"
                    class="bg-dark-50 dark:bg-dark-700 rounded-xl p-3"
                >
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-dark-900 dark:text-white truncate">
                                {{ item.name }}
                            </h4>
                            <p class="text-xs text-dark-500 dark:text-dark-400">
                                {{ formatCurrency(item.price) }}
                            </p>
                        </div>
                        <button 
                            @click="removeFromCart(item)"
                            class="text-dark-400 hover:text-red-500 p-1"
                        >
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button 
                                @click="decrementCartItem(item)"
                                class="w-7 h-7 rounded-lg bg-white dark:bg-dark-600 flex items-center justify-center hover:bg-dark-100 dark:hover:bg-dark-500"
                            >
                                <MinusIcon class="w-4 h-4" />
                            </button>
                            <input 
                                type="number"
                                :value="item.quantity"
                                @input="updateItemQuantity(item.id, $event.target.value, getProductStock(item.id) + item.quantity)"
                                @blur="validateQuantity(item.id, $event.target.value, getProductStock(item.id) + item.quantity)"
                                @keyup.enter="$event.target.blur()"
                                class="w-12 h-7 text-center text-sm font-medium rounded-lg border border-dark-200 dark:border-dark-600 bg-white dark:bg-dark-600 text-dark-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                min="1"
                            />
                            <button 
                                @click="incrementCartItem(item)"
                                class="w-7 h-7 rounded-lg bg-white dark:bg-dark-600 flex items-center justify-center hover:bg-dark-100 dark:hover:bg-dark-500"
                                :disabled="getProductStock(item.id) <= 0"
                            >
                                <PlusIcon class="w-4 h-4" />
                            </button>
                        </div>
                        <span class="text-sm font-semibold text-dark-900 dark:text-white">
                            {{ formatCurrency(item.price * item.quantity) }}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Discount -->
            <div class="p-4 border-t border-dark-200 dark:border-dark-700">
                <div class="flex flex-col gap-2 mb-3">
                    <select 
                        v-model="selectedDiscountId" 
                        class="input w-full mb-2"
                        @change="handleDiscountChange"
                    >
                        <option value="manual">Diskon Manual</option>
                        <option v-for="discount in availableDiscounts" :key="discount.id" :value="discount.id">
                            {{ discount.name }} ({{ discount.type === 'percentage' ? parseFloat(discount.value) + '%' : formatCurrency(discount.value) }})
                        </option>
                    </select>

                    <div class="flex gap-2">
                        <select 
                            v-model="discountType"
                            class="input py-2 w-24"
                            :disabled="selectedDiscountId !== 'manual'"
                        >
                            <option value="percent">%</option>
                            <option value="nominal">Rp</option>
                        </select>
                        <input 
                            v-model="discountValue"
                            type="number" 
                            class="input py-2 flex-1" 
                            placeholder="Nilai Diskon"
                            @input="updateDiscount"
                            :readonly="selectedDiscountId !== 'manual'"
                            :class="{'bg-gray-100 dark:bg-dark-800 cursor-not-allowed': selectedDiscountId !== 'manual'}"
                        />
                    </div>
                </div>
            </div>
            
            <!-- Summary -->
            <div class="p-4 border-t border-dark-200 dark:border-dark-700 bg-dark-50 dark:bg-dark-700/50">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-dark-600 dark:text-dark-400">
                        <span>Subtotal</span>
                        <span>{{ formatCurrency(cartStore.subtotal) }}</span>
                    </div>
                    <div v-if="cartStore.discountAmount > 0" class="flex justify-between text-red-500">
                        <span>Diskon</span>
                        <span>-{{ formatCurrency(cartStore.discountAmount) }}</span>
                    </div>
                    <div class="flex justify-between text-dark-600 dark:text-dark-400">
                        <span>PPN ({{ cartStore.taxRate }}%)</span>
                        <span>{{ formatCurrency(cartStore.taxAmount) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-dark-900 dark:text-white pt-2 border-t border-dark-200 dark:border-dark-600">
                        <span>Total</span>
                        <span>{{ formatCurrency(cartStore.total) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Payment Methods -->
            <div class="p-4 border-t border-dark-200 dark:border-dark-700">
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <button 
                        @click="cartStore.setPaymentMethod('cash')"
                        :class="['btn text-xs', cartStore.paymentMethod === 'cash' ? 'btn-primary' : 'btn-ghost']"
                    >
                        💵 Cash
                    </button>
                    <button 
                        @click="cartStore.setPaymentMethod('qris')"
                        :class="['btn text-xs', cartStore.paymentMethod === 'qris' ? 'btn-primary' : 'btn-ghost']"
                    >
                        📱 QRIS
                    </button>
                    <button 
                        @click="cartStore.setPaymentMethod('transfer')"
                        :class="['btn text-xs', cartStore.paymentMethod === 'transfer' ? 'btn-primary' : 'btn-ghost']"
                    >
                        🏦 Transfer
                    </button>
                </div>
                
                <button 
                    @click="checkout"
                    class="btn-primary w-full text-base py-3"
                    :disabled="!cartStore.items.length || processing"
                >
                    <span v-if="processing" class="spinner mr-2"></span>
                    {{ processing ? 'Memproses...' : 'Bayar Sekarang (F2)' }}
                </button>
            </div>
        </div>
        
        <!-- Payment Modal -->
        <Teleport to="body">
            <div v-if="showPaymentModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="card max-w-md w-full p-6 animate-scale-in">
                    <div v-if="cartStore.paymentMethod === 'qris'" class="text-center">
                        <h3 class="text-xl font-bold text-dark-900 dark:text-white mb-4">Scan QRIS</h3>
                        <div class="bg-white p-4 rounded-xl inline-block mb-4">
                            <img :src="qrisImage" alt="QRIS" class="w-48 h-48" />
                        </div>
                        <p class="text-dark-600 dark:text-dark-400 mb-4">
                            Total: <span class="font-bold text-dark-900 dark:text-white">{{ formatCurrency(cartStore.total) }}</span>
                        </p>
                        <div class="flex gap-2">
                            <button @click="showPaymentModal = false" class="btn-ghost flex-1">Batal</button>
                            <button @click="confirmPayment" class="btn-primary flex-1">Konfirmasi</button>
                        </div>
                    </div>
                    
                    <div v-else-if="cartStore.paymentMethod === 'cash'" class="text-center">
                        <h3 class="text-xl font-bold text-dark-900 dark:text-white mb-4">Pembayaran Cash</h3>
                        <p class="text-dark-600 dark:text-dark-400 mb-2">Total Tagihan</p>
                        <p class="text-3xl font-bold text-dark-900 dark:text-white mb-4">
                            {{ formatCurrency(cartStore.total) }}
                        </p>
                        
                        <!-- Quick Cash Amount Buttons (Uang Pas) -->
                        <div class="mb-4">
                            <p class="text-sm text-dark-500 dark:text-dark-400 mb-2">Pilih Nominal Uang:</p>
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <!-- Uang Pas Button -->
                                <button 
                                    @click="cashReceived = cartStore.total"
                                    :class="[
                                        'btn text-sm py-2 transition-all',
                                        cashReceived === cartStore.total ? 'btn-primary ring-2 ring-primary-400' : 'btn-ghost bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-700'
                                    ]"
                                >
                                    💵 Uang Pas
                                </button>
                                <!-- Quick Amount Buttons -->
                                <button 
                                    v-for="amount in quickCashAmounts" 
                                    :key="amount"
                                    @click="cashReceived = amount"
                                    :class="[
                                        'btn text-sm py-2 transition-all',
                                        cashReceived === amount ? 'btn-primary ring-2 ring-primary-400' : 'btn-ghost hover:bg-dark-100 dark:hover:bg-dark-600'
                                    ]"
                                    :disabled="amount < cartStore.total"
                                >
                                    {{ formatQuickAmount(amount) }}
                                </button>
                            </div>
                        </div>
                        
                        <input 
                            ref="paymentInput"
                            v-model="cashReceived"
                            type="number" 
                            class="input text-center text-xl mb-4" 
                            placeholder="Atau ketik jumlah"
                            @keyup.enter="confirmPayment"
                        />
                        <p v-if="change > 0" class="text-lg text-emerald-500 font-bold mb-4">
                            Kembalian: {{ formatCurrency(change) }}
                        </p>
                        
                        <!-- Partial Payment / Credit Option -->
                        <div v-if="cashReceived > 0 && cashReceived < cartStore.total" class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                            <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-2">
                                <span class="font-bold">Sisa Piutang:</span> {{ formatCurrency(cartStore.total - cashReceived) }}
                            </p>
                            <div class="text-left">
                                <label class="block text-xs font-medium text-yellow-700 dark:text-yellow-300 mb-1">Pilih Pelanggan (Wajib untuk Piutang)</label>
                                <select v-model="selectedCustomerId" class="input text-sm">
                                    <option value="">-- Pilih Pelanggan --</option>
                                    <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                        {{ customer.name }} {{ customer.phone ? `(${customer.phone})` : '' }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex gap-2">
                            <button @click="showPaymentModal = false" class="btn-ghost flex-1">Batal</button>
                            <button 
                                @click="confirmPayment" 
                                class="btn-primary flex-1" 
                                :disabled="(cashReceived < cartStore.total && !selectedCustomerId) || cashReceived <= 0"
                            >
                                {{ cashReceived < cartStore.total ? 'Simpan sebagai Piutang' : 'Selesai' }}
                            </button>
                        </div>
                    </div>
                    
                    <div v-else class="text-center">
                        <h3 class="text-xl font-bold text-dark-900 dark:text-white mb-4">Transfer Bank / E-Wallet</h3>
                        
                        <div v-if="bankAccounts.length" class="text-left mb-4">
                            <p class="text-dark-600 dark:text-dark-400 mb-3 text-center">Transfer ke salah satu rekening:</p>
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                <div 
                                    v-for="(account, idx) in bankAccounts" 
                                    :key="idx"
                                    class="p-3 bg-dark-50 dark:bg-dark-700 rounded-lg"
                                >
                                    <p class="font-bold text-dark-900 dark:text-white">{{ account.bank_name }} - {{ account.account_number }}</p>
                                    <p class="text-sm text-dark-500">a.n. {{ account.account_holder }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                            <p class="text-yellow-700 dark:text-yellow-300 text-sm">
                                Belum ada rekening yang dikonfigurasi. Silakan tambahkan rekening di menu <strong>Pengaturan</strong>.
                            </p>
                        </div>
                        
                        <p class="text-3xl font-bold text-dark-900 dark:text-white mb-4">
                            {{ formatCurrency(cartStore.total) }}
                        </p>
                        <div class="flex gap-2">
                            <button @click="showPaymentModal = false" class="btn-ghost flex-1">Batal</button>
                            <button @click="confirmPayment" class="btn-primary flex-1" :disabled="!bankAccounts.length">Konfirmasi</button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
        
        <!-- Success Modal -->
        <Teleport to="body">
            <div v-if="showSuccessModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="card max-w-sm w-full p-6 text-center animate-scale-in">
                    <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/30 mx-auto mb-4 flex items-center justify-center">
                        <CheckIcon class="w-8 h-8 text-emerald-500" />
                    </div>
                    <h3 class="text-xl font-bold text-dark-900 dark:text-white mb-2">Pembayaran Berhasil!</h3>
                    <p class="text-dark-600 dark:text-dark-400 mb-4">
                        Invoice: {{ lastInvoice }}
                    </p>
                    <div class="flex gap-2">
                        <button @click="printReceipt" class="btn-ghost flex-1">
                            🖨️ Cetak Struk
                        </button>
                        <button @click="newTransaction" class="btn-primary flex-1">
                            Transaksi Baru
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Printable Receipt -->

        <!-- Barcode Scanner Modal -->
        <Teleport to="body">
            <div v-if="showScannerModal" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
                <div class="card max-w-lg w-full p-6 animate-scale-in">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-dark-900 dark:text-white">📷 Scan Barcode</h3>
                        <button @click="closeBarcodeScanner" class="text-dark-400 hover:text-dark-600 p-1">
                            <XMarkIcon class="w-6 h-6" />
                        </button>
                    </div>
                    
                    <div id="barcode-scanner-container" class="w-full aspect-video bg-black rounded-lg overflow-hidden mb-4"></div>
                    
                    <p class="text-sm text-dark-500 dark:text-dark-400 text-center mb-4">
                        Arahkan kamera ke barcode produk
                    </p>
                    
                    <!-- Success Message -->
                    <div v-if="scanSuccessMessage" class="text-emerald-500 font-bold text-center mb-4 animate-bounce">
                        {{ scanSuccessMessage }}
                    </div>
                    
                    <!-- Out of Stock Warning -->
                    <div v-if="scannerError && scannerError.includes('BARANG HABIS')" class="mb-4 p-4 bg-yellow-100 dark:bg-yellow-900/30 border-2 border-yellow-500 rounded-xl animate-pulse">
                        <div class="flex items-center justify-center gap-2 text-yellow-700 dark:text-yellow-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="text-lg font-bold">STOK HABIS!</span>
                        </div>
                        <p class="text-center text-yellow-800 dark:text-yellow-200 mt-2 font-medium">
                            {{ scannerError.replace('⚠️ BARANG HABIS: ', '').replace(' stok kosong!', '') }}
                        </p>
                    </div>
                    
                    <!-- Other Errors -->
                    <div v-else-if="scannerError" class="text-red-500 text-sm text-center mb-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        {{ scannerError }}
                    </div>
                    
                    <button @click="closeBarcodeScanner" class="btn-ghost w-full">
                        Selesai / Tutup Scanner
                    </button>
                </div>
            </div>
        </Teleport>

    </div>
</template>

<style>
/* Print styling is now handled in the popup window */
</style>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useRouter } from 'vue-router'; // Added imports
import { useCartStore } from '@/stores/cart';
import { useAuthStore } from '@/stores/auth';
import {
    MagnifyingGlassIcon,
    CubeIcon,
    ShoppingCartIcon,
    XMarkIcon,
    MinusIcon,
    PlusIcon,
    CheckIcon,
    CameraIcon
} from '@heroicons/vue/24/outline';

const router = useRouter(); // Initialize router
const cartStore = useCartStore();
const authStore = useAuthStore();

// State
const products = ref([]);
const categories = ref([]);
const customers = ref([]);
const search = ref('');
const selectedCategory = ref(null);
const selectedCustomerId = ref('');
const searchInput = ref(null);
const paymentInput = ref(null);
const processing = ref(false);
const showPaymentModal = ref(false);
const showSuccessModal = ref(false);
const cashReceived = ref(0);
const discountType = ref('percent');
const discountValue = ref('');
const lastInvoice = ref('');
const lastTransactionId = ref(null);

// Barcode Scanner State
const showScannerModal = ref(false);
const scannerError = ref('');
let html5QrScanner = null;

// Bank Accounts State
const bankAccounts = ref([]);

// QRIS Image from Settings
const qrisImage = ref('/images/qris-placeholder.png');

// Quick Cash Amounts for easy payment
const quickCashAmounts = computed(() => {
    const total = cartStore.total;
    const amounts = [];
    
    // Common Indonesian currency denominations
    const denominations = [10000, 20000, 50000, 100000, 150000, 200000];
    
    // Add denominations that are >= total
    for (const denom of denominations) {
        if (denom >= total && amounts.length < 5) {
            amounts.push(denom);
        }
    }
    
    // If total is very high, add rounded amounts
    if (total > 200000) {
        const roundedUp = Math.ceil(total / 50000) * 50000;
        if (!amounts.includes(roundedUp)) amounts.push(roundedUp);
        if (!amounts.includes(roundedUp + 50000)) amounts.push(roundedUp + 50000);
    }
    
    return amounts.slice(0, 5); // Maximum 5 quick amounts
});

// Format quick amount to shorter display
const formatQuickAmount = (amount) => {
    if (amount >= 1000000) {
        return `${(amount / 1000000).toFixed(amount % 1000000 === 0 ? 0 : 1)}jt`;
    } else if (amount >= 1000) {
        return `${(amount / 1000).toFixed(0)}rb`;
    }
    return amount.toString();
};

// Global Discounts State
const activeDiscounts = ref([]);
const selectedDiscountId = ref('manual');

// Validated Discounts (Check Min Purchase)
const availableDiscounts = computed(() => {
    return activeDiscounts.value.filter(d => {
        if (!d.min_purchase || parseFloat(d.min_purchase) === 0) return true;
        return cartStore.subtotal >= parseFloat(d.min_purchase);
    });
});

// Watch for subtotal changes to validate current discount
import { watch } from 'vue'; // Ensure watch is imported (it is usually auto or already imported)

watch(() => cartStore.subtotal, (newSubtotal) => {
    if (selectedDiscountId.value !== 'manual') {
        const currentDiscount = activeDiscounts.value.find(d => d.id === selectedDiscountId.value);
        if (currentDiscount && currentDiscount.min_purchase > 0 && newSubtotal < parseFloat(currentDiscount.min_purchase)) {
            // Cart total dropped below requirement, reset discount
            selectedDiscountId.value = 'manual';
            handleDiscountChange();
            // Optional: alert user
            // alert(`Diskon ${currentDiscount.name} dihapus karena minimal belanja tidak terpenuhi`);
        }
    }
});

// Computed
const filteredProducts = computed(() => {
    let result = products.value;
    
    // Filter by category
    if (selectedCategory.value) {
        result = result.filter(p => p.category_id === selectedCategory.value);
    }
    
    // Filter by search
    if (search.value) {
        const query = search.value.toLowerCase();
        result = result.filter(p => 
            p.name.toLowerCase().includes(query) || 
            p.barcode?.includes(query)
        );
    }
    
    return result;
});

const change = computed(() => {
    return Math.max(0, cashReceived.value - cartStore.total);
});

// Methods
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const addToCart = (product) => {
    if (product.stock > 0) {
        cartStore.addItem(product);
        // Kurangi stok di tampilan kasir
        product.stock--;
    } else {
        alert('Stok habis!');
    }
};

// Get current product stock from products list
const getProductStock = (productId) => {
    const product = products.value.find(p => p.id === productId);
    return product ? product.stock : 0;
};

// Increment cart item and decrease product stock
const incrementCartItem = (item) => {
    const product = products.value.find(p => p.id === item.id);
    if (product && product.stock > 0) {
        cartStore.incrementQuantity(item.id);
        product.stock--;
    }
};

// Decrement cart item and restore product stock
const decrementCartItem = (item) => {
    const product = products.value.find(p => p.id === item.id);
    if (item.quantity > 1) {
        cartStore.decrementQuantity(item.id);
        if (product) product.stock++;
    } else {
        // Jika quantity = 1, hapus item dari keranjang
        removeFromCart(item);
    }
};

// Remove item from cart and restore all its stock
const removeFromCart = (item) => {
    const product = products.value.find(p => p.id === item.id);
    if (product) {
        product.stock += item.quantity;
    }
    cartStore.removeItem(item.id);
};

// Clear entire cart and restore all stock
const clearCart = () => {
    cartStore.items.forEach(item => {
        const product = products.value.find(p => p.id === item.id);
        if (product) {
            product.stock += item.quantity;
        }
    });
    cartStore.clear();
};

// Update item quantity with direct input
const updateItemQuantity = (productId, value, maxStock) => {
    const currentItem = cartStore.items.find(i => i.id === productId);
    const product = products.value.find(p => p.id === productId);
    if (!currentItem || !product) return;
    
    const oldQty = currentItem.quantity;
    const newQty = Math.max(1, Math.min(parseInt(value) || 1, maxStock));
    const diff = newQty - oldQty;
    
    // Update product stock based on difference
    product.stock -= diff;
    cartStore.updateQuantity(productId, newQty);
};

// Validate quantity on blur (ensure valid value)
const validateQuantity = (productId, value, maxStock) => {
    const currentItem = cartStore.items.find(i => i.id === productId);
    const product = products.value.find(p => p.id === productId);
    if (!currentItem || !product) return;
    
    const oldQty = currentItem.quantity;
    let newQty = parseInt(value) || 1;
    if (newQty < 1) newQty = 1;
    if (newQty > maxStock) newQty = maxStock;
    
    const diff = newQty - oldQty;
    product.stock -= diff;
    cartStore.updateQuantity(productId, newQty);
};

const searchProduct = () => {
    if (search.value && filteredProducts.value.length === 1) {
        addToCart(filteredProducts.value[0]);
        search.value = '';
    }
};

// Barcode Scanner Methods
const loadHtml5QrCode = () => {
    return new Promise((resolve) => {
        if (window.Html5Qrcode) {
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
        script.onload = resolve;
        document.head.appendChild(script);
    });
};

const openBarcodeScanner = async () => {
    showScannerModal.value = true;
    scannerError.value = '';
    
    await loadHtml5QrCode();
    
    // Wait for DOM to be ready
    setTimeout(async () => {
        try {
            html5QrScanner = new window.Html5Qrcode('barcode-scanner-container');
            
            await html5QrScanner.start(
                { facingMode: 'environment' },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 150 },
                    aspectRatio: 16/9
                },
                onScanSuccess,
                (errorMessage) => {
                    // Ignore scan errors (no barcode found yet)
                }
            );
        } catch (err) {
            console.error('Scanner error:', err);
            scannerError.value = 'Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.';
        }
    }, 100);
};

// Scanner debounce state
const lastScannedCode = ref('');
const lastScannedTime = ref(0);
const scanSuccessMessage = ref('');
const beepAudio = new Audio('/audio/beep.mp3'); // Pastikan file audio ada atau gunakan base64/synth

const onScanSuccess = (decodedText) => {
    const now = Date.now();
    // Prevent duplicate scans within 1.5 seconds for the same code
    if (decodedText === lastScannedCode.value && now - lastScannedTime.value < 1500) {
        return;
    }

    lastScannedCode.value = decodedText;
    lastScannedTime.value = now;

    // Find product by barcode
    const product = products.value.find(p => p.barcode === decodedText);
    
    if (product) {
        // Check if stock is available
        if (product.stock <= 0) {
            scannerError.value = `⚠️ BARANG HABIS: "${product.name}" stok kosong!`;
            scanSuccessMessage.value = '';
            return;
        }
        
        addToCart(product);
        // Play beep sound (optional, using browser API if file not exists)
        // playBeep(); 
        
        scanSuccessMessage.value = `✅ ${product.name} ditambahkan`;
        scannerError.value = '';
        
        // Clear success message after 2 seconds
        setTimeout(() => {
            scanSuccessMessage.value = '';
        }, 2000);
    } else {
        // Try search with barcode
        const results = products.value.filter(p => matchProduct(p, decodedText));
        
        if (results.length === 1) {
            const foundProduct = results[0];
            
            // Check if stock is available
            if (foundProduct.stock <= 0) {
                scannerError.value = `⚠️ BARANG HABIS: "${foundProduct.name}" stok kosong!`;
                scanSuccessMessage.value = '';
                return;
            }
            
            addToCart(foundProduct);
            scanSuccessMessage.value = `✅ ${foundProduct.name} ditambahkan`;
            scannerError.value = '';
            setTimeout(() => { scanSuccessMessage.value = ''; }, 2000);
        } else {
            scannerError.value = `❌ Produk "${decodedText}" tidak ditemukan`;
            scanSuccessMessage.value = '';
        }
    }
};

const matchProduct = (p, query) => {
    return p.name.toLowerCase().includes(query.toLowerCase()) || p.barcode?.includes(query);
};

const closeBarcodeScanner = async () => {
    if (html5QrScanner) {
        try {
            await html5QrScanner.stop();
            html5QrScanner.clear();
        } catch (e) {
            console.log('Scanner already stopped');
        }
        html5QrScanner = null;
    }
    showScannerModal.value = false;
    scannerError.value = '';
};

const handleDiscountChange = () => {
    if (selectedDiscountId.value === 'manual') {
        discountType.value = 'percent';
        discountValue.value = '';
        updateDiscount();
    } else {
        const discount = activeDiscounts.value.find(d => d.id === selectedDiscountId.value);
        if (discount) {
            discountType.value = discount.type === 'percentage' ? 'percent' : 'nominal';
            discountValue.value = discount.value;
            cartStore.setDiscount(discountType.value, discount.value);
        }
    }
};

const updateDiscount = () => {
    if (selectedDiscountId.value === 'manual') {
        cartStore.setDiscount(discountType.value, discountValue.value || 0);
    }
};

const checkout = () => {
    if (!cartStore.items.length) return;
    
    // Empty the input so user can type manually
    cashReceived.value = '';
    
    showPaymentModal.value = true;
    
    nextTick(() => {
        paymentInput.value?.focus();
    });
};

const printReceipt = () => {
    if (!lastTransactionId.value) return;
    const url = router.resolve({ name: 'transactions.print', params: { id: lastTransactionId.value } }).href;
    window.open(url, '_blank', 'width=400,height=600');
};

const confirmPayment = async () => {
    processing.value = true;
    
    try {
        const response = await axios.post('/transactions', {
            items: cartStore.items.map(item => ({
                product_id: item.id,
                quantity: item.quantity,
                price: item.price
            })),
            subtotal: cartStore.subtotal,
            discount: cartStore.discountAmount,
            tax: cartStore.taxAmount,
            total: cartStore.total,
            payment_method: cartStore.paymentMethod,
            cash_received: cashReceived.value,
            customer_id: selectedCustomerId.value || null
        });
        
        lastInvoice.value = response.data.invoice_number;
        lastTransactionId.value = response.data.data.id; // Corrected path to ID
        showPaymentModal.value = false;
        showSuccessModal.value = true;
        
        // Refresh products to get updated stock
        try {
            const prodRes = await axios.get('/products');
            products.value = prodRes.data.data || prodRes.data;
        } catch (e) {}
        
    } catch (e) {
        console.error('Transaction error:', e);
        showPaymentModal.value = false;
        
        let errorMessage = 'Terjadi kesalahan saat menyimpan transaksi.';
        if (e.response?.status === 401) {
            errorMessage = 'Sesi login Anda habis. Silakan login kembali.';
        } else if (e.response?.data?.message) {
            errorMessage = e.response.data.message;
        } else if (e.response?.data?.errors) {
            errorMessage = Object.values(e.response.data.errors).flat().join('\n');
        }
        
        alert(errorMessage);
    } finally {
        processing.value = false;
    }
};

const newTransaction = () => {
    cartStore.clear();
    cashReceived.value = 0;
    discountValue.value = '';
    selectedCustomerId.value = '';
    showSuccessModal.value = false;
    searchInput.value?.focus();
};

onMounted(async () => {
    try {
        const [prodRes, catRes, settingsRes, custRes, discountsRes] = await Promise.all([
            axios.get('/products'),
            axios.get('/categories'),
            axios.get('/settings', { params: { _t: new Date().getTime() } }),
            axios.get('/customers'),
            axios.get('/discounts/active')
        ]);
        products.value = prodRes.data.data || prodRes.data;
        categories.value = catRes.data.data || catRes.data;
        customers.value = custRes.data.data || custRes.data;
        activeDiscounts.value = discountsRes.data.data;

        // Sync product stock with cart items (Fix: Stock reset bug on navigation)
        if (cartStore.items.length) {
            cartStore.items.forEach(cartItem => {
                const product = products.value.find(p => p.id === cartItem.id);
                if (product) {
                    product.stock = Math.max(0, product.stock - cartItem.quantity);
                }
            });
        }
        
        // Update tax rate from settings
        if (settingsRes.data && settingsRes.data.tax_rate !== undefined) {
            cartStore.setTaxRate(settingsRes.data.tax_rate);
        }
        
        // Load bank accounts
        if (settingsRes.data && settingsRes.data.bank_accounts) {
            try {
                bankAccounts.value = JSON.parse(settingsRes.data.bank_accounts);
            } catch (e) {
                bankAccounts.value = [];
            }
        }
        
        // Load QRIS image from settings
        if (settingsRes.data && settingsRes.data.qris_image_url) {
            qrisImage.value = settingsRes.data.qris_image_url;
        }
    } catch (e) {
        console.log('Using demo data/Fallback due to error', e);
    }
    
    searchInput.value?.focus();
    
    // Add keyboard shortcuts listener
    window.addEventListener('keydown', handleGlobalKeydown);
});

// Keyboard Shortcuts Handler
const handleGlobalKeydown = (e) => {
    // F1: Focus Search
    if (e.key === 'F1') {
        e.preventDefault();
        searchInput.value?.focus();
        return;
    }
    
    // F2: Checkout / Confirm Payment
    if (e.key === 'F2') {
        e.preventDefault();
        if (showPaymentModal.value) {
            // If in payment modal, F2 acts as Confirm
            // Check validation logic similar to button binding
            const canConfirm = cartStore.paymentMethod === 'cash' 
                ? (cashReceived.value >= cartStore.total || (cashReceived.value > 0 && selectedCustomerId.value))
                : (cartStore.paymentMethod === 'qris' || bankAccounts.value.length > 0);
                
            if (canConfirm && !processing.value) {
                confirmPayment();
            }
        } else if (cartStore.items.length > 0) {
            checkout();
        }
        return;
    }
    
    // F3: Clear Cart
    if (e.key === 'F3') {
        e.preventDefault();
        if (cartStore.items.length > 0 && !showPaymentModal.value && !showScannerModal.value) {
            if (confirm('Yakin ingin mengosongkan keranjang?')) {
                clearCart();
            }
        }
        return;
    }
    
    // F4: Scanner
    if (e.key === 'F4') {
        e.preventDefault();
        if (!showScannerModal.value && !showPaymentModal.value) {
            openBarcodeScanner();
        }
        return;
    }
    
    // Escape: Close Modals
    if (e.key === 'Escape') {
        if (showScannerModal.value) {
            closeBarcodeScanner();
        } else if (showPaymentModal.value) {
            showPaymentModal.value = false;
        } else if (showSuccessModal.value) {
            newTransaction();
        }
    }
};

// Cleanup scanner on unmount
onBeforeUnmount(() => {
    closeBarcodeScanner();
    window.removeEventListener('keydown', handleGlobalKeydown);
});
</script>
