<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-20">
            <div class="spinner w-8 h-8"></div>
            <span class="ml-3 text-dark-500 font-medium">Memuat data...</span>
        </div>

        <div v-else-if="error" class="p-4 bg-rose-50 dark:bg-rose-900/10 text-rose-700 dark:text-rose-400 rounded-xl border border-rose-100 dark:border-rose-900/20 text-center">
            {{ error }}
        </div>

        <template v-else>
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Stat Card -->
                <div v-for="stat in stats" :key="stat.label" class="card p-6 flex flex-col justify-between hover:border-primary-200 dark:hover:border-primary-800 transition-colors duration-200">
                    <div class="flex items-start justify-between mb-4">
                        <div :class="['p-3 rounded-xl', stat.bgColor]">
                            <component :is="stat.icon" :class="['w-6 h-6', stat.iconColor]" />
                        </div>
                        <span :class="['text-xs font-bold px-2.5 py-1 rounded-full', stat.change >= 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400']">
                            {{ stat.change >= 0 ? '+' : '' }}{{ stat.change }}%
                        </span>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-dark-900 dark:text-white tracking-tight">
                            {{ stat.value }}
                        </p>
                        <p class="text-sm font-medium text-dark-500 dark:text-dark-400 mt-1">{{ stat.label }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Sales Chart -->
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-dark-900 dark:text-white">Grafik Penjualan</h3>
                        <div class="relative">
                            <select class="text-xs bg-gray-50 dark:bg-dark-700 border-none rounded-lg py-1.5 pl-3 pr-8 text-dark-600 dark:text-dark-300 font-medium focus:ring-1 focus:ring-primary-500 cursor-pointer">
                                <option value="week">7 Hari Terakhir</option>
                                <option value="month">Bulan Ini</option>
                            </select>
                        </div>
                    </div>
                    <div class="relative h-80 w-full">
                        <canvas ref="salesChart"></canvas>
                    </div>
                </div>
                
                <!-- Top Products -->
                <div class="card p-6">
                    <h3 class="text-lg font-bold text-dark-900 dark:text-white mb-6">Produk Terlaris</h3>
                    <div v-if="topProducts.length === 0" class="text-center py-12 text-dark-400 flex flex-col items-center">
                        <ArchiveBoxIcon class="w-12 h-12 mb-3 text-dark-300 dark:text-dark-600" />
                        <p>Belum ada transaksi hari ini</p>
                    </div>
                    <div v-else class="space-y-4">
                        <div 
                            v-for="(product, index) in topProducts" 
                            :key="product.id"
                            class="flex items-center gap-4 group p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-dark-700/30 transition-colors"
                        >
                            <span class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-dark-700 text-dark-600 dark:text-dark-300 text-sm font-bold flex items-center justify-center group-hover:bg-primary-50 group-hover:text-primary-600 dark:group-hover:bg-primary-900/30 dark:group-hover:text-primary-400 transition-colors">
                                {{ index + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-dark-900 dark:text-white truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                    {{ product.name }}
                                </p>
                                <p class="text-xs text-dark-500 dark:text-dark-400 font-medium">
                                    {{ product.sold }} item terjual
                                </p>
                            </div>
                            <span class="text-sm font-bold text-dark-900 dark:text-white">
                                {{ formatCurrency(product.revenue) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="card overflow-hidden">
                <div class="p-6 border-b border-dark-100 dark:border-dark-700/60 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-dark-900 dark:text-white">Transaksi Terbaru</h3>
                    <router-link to="/transactions" class="text-sm font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                        Lihat Semua
                    </router-link>
                </div>
                
                <div v-if="recentTransactions.length === 0" class="text-center py-12 text-dark-400">
                    Belum ada transaksi
                </div>
                
                <div v-else class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="pl-6">Invoice</th>
                                <th>Waktu</th>
                                <th>Item</th>
                                <th class="text-right">Total</th>
                                <th class="pr-6 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tx in recentTransactions" :key="tx.id">
                                <td class="pl-6 font-medium text-dark-900 dark:text-white">{{ tx.invoice }}</td>
                                <td class="text-dark-500 dark:text-dark-400">{{ tx.time }}</td>
                                <td class="text-dark-500 dark:text-dark-400">{{ tx.items }} item</td>
                                <td class="font-bold text-dark-900 dark:text-white text-right">{{ formatCurrency(tx.total) }}</td>
                                <td class="pr-6 text-center">
                                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', tx.status === 'paid' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400']">
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
    ArchiveBoxIcon,
    ArrowUpIcon,
    ArrowDownIcon
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
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
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
                label: 'Penjualan', 
                value: formatCurrency(data.stats?.total_sales || 0), 
                change: data.stats?.sales_change || 0,
                icon: CurrencyDollarIcon,
                bgColor: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400',
                iconColor: 'text-emerald-600 dark:text-emerald-400'
            },
            { 
                label: 'Transaksi', 
                value: String(data.stats?.total_transactions || 0), 
                change: data.stats?.tx_change || 0,
                icon: ShoppingCartIcon,
                bgColor: 'bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400',
                iconColor: 'text-primary-600 dark:text-primary-400'
            },
            { 
                label: 'Produk', 
                value: String(data.stats?.total_products || 0), 
                change: 0,
                icon: CubeIcon,
                bgColor: 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
                iconColor: 'text-blue-600 dark:text-blue-400'
            },
            { 
                label: 'Stok Kritis', 
                value: String(data.stats?.low_stock || 0), 
                change: 0,
                icon: ArchiveBoxIcon,
                bgColor: 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400',
                iconColor: 'text-amber-600 dark:text-amber-400'
            }
        ];
        
        topProducts.value = (data.top_products || []).map(p => ({
            id: p.id,
            name: p.name,
            sold: p.sold || p.quantity,
            revenue: p.revenue
        }));
        
        recentTransactions.value = (data.recent_transactions || []).map(tx => ({
            id: tx.id,
            invoice: tx.invoice,
            time: tx.time,
            items: tx.items,
            total: tx.total,
            status: tx.status
        }));
        
        await fetchChartData();
        
    } catch (e) {
        console.error('Error fetching dashboard:', e);
        error.value = 'Gagal memuat data dashboard. Silakan refresh halaman.';
        // Set empty data as fallback
        stats.value = [
            { label: 'Penjualan', value: 'Rp 0', change: 0, icon: CurrencyDollarIcon, bgColor: 'bg-emerald-50', iconColor: 'text-emerald-600' },
            { label: 'Transaksi', value: '0', change: 0, icon: ShoppingCartIcon, bgColor: 'bg-primary-50', iconColor: 'text-primary-600' },
            { label: 'Produk', value: '0', change: 0, icon: CubeIcon, bgColor: 'bg-blue-50', iconColor: 'text-blue-600' },
            { label: 'Stok Kritis', value: '0', change: 0, icon: ArchiveBoxIcon, bgColor: 'bg-amber-50', iconColor: 'text-amber-600' }
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
        // Create gradient
        const ctx = salesChart.value.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.2)'); // Primary-600 with opacity
        gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');
        
        chartInstance = new Chart(salesChart.value, {
            type: 'line',
            data: {
                labels: chartData.value.labels,
                datasets: [{
                    label: 'Penjualan',
                    data: chartData.value.values,
                    borderColor: '#4f46e5', // Primary-600
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#4f46e5',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111827', // Gray-900
                        titleColor: '#f9fafb',
                        bodyColor: '#f9fafb',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += formatCurrency(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#6b7280', // Gray-500
                            font: { size: 11 }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e5e7eb', // Gray-200
                            borderDash: [5, 5],
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: { size: 11 },
                            callback: (value) => {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'jt';
                                else if (value >= 1000) return (value / 1000).toFixed(0) + 'K';
                                return value;
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });
    }
};

onMounted(() => {
    fetchDashboardData();
});
</script>
