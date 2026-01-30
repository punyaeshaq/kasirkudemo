<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="spinner"></div>
            <span class="ml-3 text-dark-500">Memuat data...</span>
        </div>

        <div v-else-if="error" class="p-4 bg-red-100 text-red-700 rounded-lg text-center">
            {{ error }}
        </div>

        <template v-else>
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="stat in stats" :key="stat.label" class="card p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-dark-500 dark:text-dark-400">{{ stat.label }}</p>
                            <p class="text-2xl font-bold text-dark-900 dark:text-white mt-1">
                                {{ stat.value }}
                            </p>
                            <p :class="['text-xs mt-1', stat.change >= 0 ? 'text-emerald-500' : 'text-red-500']">
                                {{ stat.change >= 0 ? '+' : '' }}{{ stat.change }}% dari kemarin
                            </p>
                        </div>
                        <div :class="['w-12 h-12 rounded-xl flex items-center justify-center', stat.bgColor]">
                            <component :is="stat.icon" :class="['w-6 h-6', stat.iconColor]" />
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Sales Chart -->
                <div class="card p-5">
                    <h3 class="text-lg font-semibold text-dark-900 dark:text-white mb-4">Grafik Penjualan (7 Hari)</h3>
                    <div class="relative h-80 w-full">
                        <canvas ref="salesChart"></canvas>
                    </div>
                </div>
                
                <!-- Top Products -->
                <div class="card p-5">
                    <h3 class="text-lg font-semibold text-dark-900 dark:text-white mb-4">Produk Terlaris Hari Ini</h3>
                    <div v-if="topProducts.length === 0" class="text-center py-8 text-dark-400">
                        Belum ada transaksi hari ini
                    </div>
                    <div v-else class="space-y-3">
                        <div 
                            v-for="(product, index) in topProducts" 
                            :key="product.id"
                            class="flex items-center gap-3"
                        >
                            <span class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 text-xs flex items-center justify-center font-medium">
                                {{ index + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-dark-900 dark:text-white truncate">
                                    {{ product.name }}
                                </p>
                                <p class="text-xs text-dark-500 dark:text-dark-400">
                                    {{ product.sold }} terjual
                                </p>
                            </div>
                            <span class="text-sm font-semibold text-dark-900 dark:text-white">
                                {{ formatCurrency(product.revenue) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-dark-900 dark:text-white">Transaksi Terbaru</h3>
                    <router-link to="/transactions" class="text-sm text-primary-500 hover:text-primary-600">
                        Lihat Semua
                    </router-link>
                </div>
                <div v-if="recentTransactions.length === 0" class="text-center py-8 text-dark-400">
                    Belum ada transaksi
                </div>
                <div v-else class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Waktu</th>
                                <th>Item</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tx in recentTransactions" :key="tx.id">
                                <td class="font-medium">{{ tx.invoice }}</td>
                                <td>{{ tx.time }}</td>
                                <td>{{ tx.items }} item</td>
                                <td class="font-semibold">{{ formatCurrency(tx.total) }}</td>
                                <td>
                                    <span :class="['badge', tx.status === 'paid' ? 'badge-success' : 'badge-warning']">
                                        {{ tx.status === 'paid' ? 'Lunas' : 'Pending' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Chart, registerables } from 'chart.js';
import {
    CurrencyDollarIcon,
    ShoppingCartIcon,
    CubeIcon,
    ArchiveBoxIcon
} from '@heroicons/vue/24/outline';

Chart.register(...registerables);

const salesChart = ref(null);
const loading = ref(true);
const error = ref(null);
let chartInstance = null;

const stats = ref([]);
const topProducts = ref([]);
const recentTransactions = ref([]);
const chartData = ref({ labels: [], values: [] });

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value || 0);
};

const fetchDashboardData = async () => {
    loading.value = true;
    
    try {
        const response = await axios.get('/dashboard');
        const data = response.data;
        
        // Update stats from API
        stats.value = [
            { 
                label: 'Total Penjualan Hari Ini', 
                value: formatCurrency(data.stats?.total_sales || 0), 
                change: data.stats?.sales_change || 0,
                icon: CurrencyDollarIcon,
                bgColor: 'bg-emerald-100 dark:bg-emerald-900/30',
                iconColor: 'text-emerald-600 dark:text-emerald-400'
            },
            { 
                label: 'Transaksi Hari Ini', 
                value: String(data.stats?.total_transactions || 0), 
                change: data.stats?.tx_change || 0,
                icon: ShoppingCartIcon,
                bgColor: 'bg-primary-100 dark:bg-primary-900/30',
                iconColor: 'text-primary-600 dark:text-primary-400'
            },
            { 
                label: 'Total Produk', 
                value: String(data.stats?.total_products || 0), 
                change: 0,
                icon: CubeIcon,
                bgColor: 'bg-secondary-100 dark:bg-secondary-900/30',
                iconColor: 'text-secondary-600 dark:text-secondary-400'
            },
            { 
                label: 'Stok Menipis', 
                value: String(data.stats?.low_stock || 0), 
                change: 0,
                icon: ArchiveBoxIcon,
                bgColor: 'bg-amber-100 dark:bg-amber-900/30',
                iconColor: 'text-amber-600 dark:text-amber-400'
            }
        ];
        
        // Update top products
        topProducts.value = (data.top_products || []).map(p => ({
            id: p.id,
            name: p.name,
            sold: p.sold || p.quantity,
            revenue: p.revenue
        }));
        
        // Update recent transactions
        recentTransactions.value = (data.recent_transactions || []).map(tx => ({
            id: tx.id,
            invoice: tx.invoice,
            time: tx.time,
            items: tx.items,
            total: tx.total,
            status: tx.status
        }));
        
        // Fetch chart data from reports
        await fetchChartData();
        
    } catch (e) {
        console.error('Error fetching dashboard:', e);
        error.value = 'Gagal memuat data dashboard. Silakan refresh halaman.';
        // Set empty data
        stats.value = [
            { label: 'Total Penjualan Hari Ini', value: 'Rp 0', change: 0, icon: CurrencyDollarIcon, bgColor: 'bg-emerald-100 dark:bg-emerald-900/30', iconColor: 'text-emerald-600 dark:text-emerald-400' },
            { label: 'Transaksi Hari Ini', value: '0', change: 0, icon: ShoppingCartIcon, bgColor: 'bg-primary-100 dark:bg-primary-900/30', iconColor: 'text-primary-600 dark:text-primary-400' },
            { label: 'Total Produk', value: '0', change: 0, icon: CubeIcon, bgColor: 'bg-secondary-100 dark:bg-secondary-900/30', iconColor: 'text-secondary-600 dark:text-secondary-400' },
            { label: 'Stok Menipis', value: '0', change: 0, icon: ArchiveBoxIcon, bgColor: 'bg-amber-100 dark:bg-amber-900/30', iconColor: 'text-amber-600 dark:text-amber-400' }
        ];
    } finally {
        loading.value = false;
    }
};

const fetchChartData = async () => {
    try {
        const response = await axios.get('/reports/sales', { params: { period: 'week' } });
        chartData.value = response.data.chart_data || { labels: [], values: [] };
    } catch (e) {
        chartData.value = { labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'], values: [0, 0, 0, 0, 0, 0, 0] };
    }
    
    renderChart();
};

const renderChart = () => {
    if (chartInstance) {
        chartInstance.destroy();
    }
    
    if (salesChart.value) {
        chartInstance = new Chart(salesChart.value, {
            type: 'line',
            data: {
                labels: chartData.value.labels,
                datasets: [{
                    label: 'Penjualan',
                    data: chartData.value.values,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                                } else if (value >= 1000) {
                                    return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                                }
                                return 'Rp ' + value;
                            }
                        }
                    }
                }
            }
        });
    }
};

onMounted(() => {
    fetchDashboardData();
});
</script>
