<template>
    <div class="space-y-6 animate-fade-in">
        <div class="flex items-center gap-4 flex-wrap">
            <select v-model="period" class="input max-w-xs">
                <option value="today">Hari Ini</option>
                <option value="week">Minggu Ini</option>
                <option value="month">Bulan Ini</option>
                <option value="custom">Custom</option>
            </select>
            <template v-if="period === 'custom'">
                <input v-model="startDate" type="date" class="input max-w-xs" />
                <input v-model="endDate" type="date" class="input max-w-xs" />
            </template>
            <div class="flex-1"></div>
            <button @click="exportPdf" class="btn-primary">📄 Export PDF</button>
            <button @click="exportExcel" class="btn-success">📊 Export Excel</button>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card p-4">
                <p class="text-sm text-dark-500">Total Penjualan</p>
                <p class="text-2xl font-bold text-dark-900 dark:text-white">{{ formatCurrency(report.total_sales) }}</p>
            </div>
            <div class="card p-4">
                <p class="text-sm text-dark-500">Jumlah Transaksi</p>
                <p class="text-2xl font-bold text-dark-900 dark:text-white">{{ report.total_transactions }}</p>
            </div>
            <div class="card p-4">
                <p class="text-sm text-dark-500">Rata-rata Transaksi</p>
                <p class="text-2xl font-bold text-dark-900 dark:text-white">{{ formatCurrency(report.avg_transaction) }}</p>
            </div>
            <div class="card p-4">
                <p class="text-sm text-dark-500">Item Terjual</p>
                <p class="text-2xl font-bold text-dark-900 dark:text-white">{{ report.total_items }}</p>
            </div>
        </div>
        
        <!-- Chart -->
        <div class="card p-5">
            <h3 class="text-lg font-semibold text-dark-900 dark:text-white mb-4">Grafik Penjualan</h3>
            <canvas ref="chartRef" height="120"></canvas>
        </div>
        
        <!-- Top Products -->
        <div class="card p-5">
            <h3 class="text-lg font-semibold text-dark-900 dark:text-white mb-4">Produk Terlaris</h3>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr><th>#</th><th>Produk</th><th>Qty</th><th>Revenue</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="(p, i) in report.top_products" :key="p.id">
                            <td>{{ i + 1 }}</td>
                            <td>{{ p.name }}</td>
                            <td>{{ p.quantity }}</td>
                            <td class="font-semibold">{{ formatCurrency(p.revenue) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

const chartRef = ref(null);
const period = ref('today');
const startDate = ref('');
const endDate = ref('');
let chartInstance = null;

const report = reactive({
    total_sales: 15250000,
    total_transactions: 48,
    avg_transaction: 317708,
    total_items: 156,
    chart_data: { labels: ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'], values: [1200000, 2500000, 3800000, 2100000, 1800000, 2500000, 1350000] },
    top_products: [
        { id: 1, name: 'Indomie Goreng', quantity: 245, revenue: 857500 },
        { id: 2, name: 'Aqua 600ml', quantity: 198, revenue: 792000 },
        { id: 3, name: 'Teh Pucuk 350ml', quantity: 156, revenue: 702000 },
    ]
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v || 0);

const renderChart = () => {
    if (chartInstance) chartInstance.destroy();
    if (!chartRef.value) return;
    chartInstance = new Chart(chartRef.value, {
        type: 'bar',
        data: {
            labels: report.chart_data.labels,
            datasets: [{ label: 'Penjualan', data: report.chart_data.values, backgroundColor: '#6366f1', borderRadius: 8 }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: (v) => 'Rp ' + (v / 1000000).toFixed(1) + 'M' } } } }
    });
};

const fetchReport = async () => {
    try {
        const res = await axios.get('/reports/sales', { params: { period: period.value, start_date: startDate.value, end_date: endDate.value } });
        Object.assign(report, res.data);
    } catch (e) {}
    renderChart();
};

const downloadFile = async (url, params, filename) => {
    try {
        const response = await axios.get(url, {
            params,
            responseType: 'blob'
        });
        const href = URL.createObjectURL(response.data);
        const link = document.createElement('a');
        link.href = href;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(href);
    } catch (e) {
        console.error('Download error:', e);
        alert('Gagal mengunduh file. Pastikan Anda sudah login.');
    }
};

const validateParams = () => {
    if (period.value === 'custom' && (!startDate.value || !endDate.value)) {
        alert('Silakan pilih rentang tanggal laporan.');
        return false;
    }
    return true;
};

const exportPdf = () => {
    if (!validateParams()) return;
    downloadFile('/reports/sales/pdf', { period: period.value, start_date: startDate.value, end_date: endDate.value }, 'laporan-penjualan.pdf');
};

const exportExcel = () => {
    if (!validateParams()) return;
    downloadFile('/reports/sales/excel', { period: period.value, start_date: startDate.value, end_date: endDate.value }, 'laporan-penjualan.csv');
};

watch([period, startDate, endDate], fetchReport);
onMounted(() => { fetchReport(); });
</script>
