<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3'; // Added Link
// ADD the new icons: Car, AlertCircle, CalendarCheck
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { TrendingUp, TrendingDown, Wallet, Wrench, BarChart3, Car, AlertCircle, CalendarCheck } from 'lucide-vue-next';
import { ref, computed } from 'vue';

import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

// Update Props to include the operational metrics
const props = defineProps<{
    metrics: any;
    chart: any;
    mechanics: any[];
    active_work_orders: number;
    today_checkins: number;
    low_stock_items: number;
}>();

const formatIDR = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const changeFilter = (event: Event) => {
    const selected = (event.target as HTMLSelectElement).value;
    router.get(route('admin.dashboard'), { filter: selected }, { preserveState: true, preserveScroll: true });
};

const chartData = computed(() => ({
    labels: props.chart.labels,
    datasets: [
        {
            label: 'Pemasukan (Rp)',
            backgroundColor: '#3b82f6',
            borderRadius: 4,
            data: props.chart.income
        },
        {
            label: 'Pengeluaran (Rp)',
            backgroundColor: '#ef4444',
            borderRadius: 4,
            data: props.chart.expense
        }
    ]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value: any) => 'Rp ' + (value / 1000) + 'k'
            }
        }
    },
    plugins: {
        legend: { position: 'top' as const },
        tooltip: {
            callbacks: {
                label: (context: any) => formatIDR(context.raw)
            }
        }
    }
};
</script>

<template>
    <Head title="Dashboard Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-xl text-slate-800 leading-tight uppercase tracking-widest">
                    Dashboard Admin
                </h2>
                <div class="bg-blue-100 text-blue-800 px-4 py-1.5 rounded-full text-sm font-bold uppercase tracking-widest">
                    Bulan: {{ metrics.month_name }}
                </div>
            </div>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card class="border-2 border-slate-200 shadow-sm">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-bold uppercase text-slate-500">Pemasukan Kotor</CardTitle>
                        <TrendingUp class="h-5 w-5 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-black text-slate-800">{{ formatIDR(metrics.income) }}</div>
                        <p class="text-xs text-slate-400 mt-1 font-medium">Total tagihan invoice pelanggan</p>
                    </CardContent>
                </Card>

                <Card class="border-2 border-slate-200 shadow-sm">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-bold uppercase text-slate-500">Pengeluaran Operasional</CardTitle>
                        <TrendingDown class="h-5 w-5 text-red-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-black text-slate-800">{{ formatIDR(metrics.expense) }}</div>
                        <p class="text-xs text-slate-400 mt-1 font-medium">Buku kas & belanja bengkel</p>
                    </CardContent>
                </Card>

                <Card class="border-2 border-blue-500 shadow-lg bg-blue-50">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-bold uppercase text-blue-800">Keuntungan Bersih</CardTitle>
                        <Wallet class="h-5 w-5 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-black text-blue-700">{{ formatIDR(metrics.net_profit) }}</div>
                        <p class="text-xs text-blue-500 mt-1 font-medium">Uang riil yang bisa dibawa pulang</p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card class="border-2 border-slate-200 shadow-sm">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs font-bold uppercase text-slate-500">Mobil Dikerjakan</CardTitle>
                        <Car class="h-4 w-4 text-blue-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-xl font-black text-slate-800">{{ active_work_orders }} Unit</div>
                    </CardContent>
                </Card>

                <Card class="border-2 border-slate-200 shadow-sm">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs font-bold uppercase text-slate-500">Check-in Hari Ini</CardTitle>
                        <CalendarCheck class="h-4 w-4 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-xl font-black text-slate-800">{{ today_checkins }} Unit</div>
                    </CardContent>
                </Card>

                <Card class="border-2 border-orange-500 shadow-sm bg-orange-50/50">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs font-bold uppercase text-orange-800">Peringatan Stok</CardTitle>
                        <AlertCircle class="h-4 w-4 text-orange-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-xl font-black text-orange-700">{{ low_stock_items }} Barang</div>
                        <Link :href="route('parts.index')" class="text-[10px] font-bold text-orange-800 underline mt-1 inline-block">Cek Gudang &rarr;</Link>
                    </CardContent>
                </Card>
            </div>

            <Card class="border-2 border-slate-200 shadow-sm">
                <CardHeader class="bg-slate-50 border-b pb-4 flex flex-row justify-between items-center">
                    <CardTitle class="text-sm font-bold uppercase tracking-widest text-slate-700 flex items-center gap-2">
                        <BarChart3 class="w-4 h-4 text-slate-500" /> Arus Kas Bengkel
                    </CardTitle>
                    <select 
                        @change="changeFilter" 
                        :value="chart.current_filter"
                        class="text-xs font-bold uppercase rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="week">7 Hari Terakhir</option>
                        <option value="month">Bulan Ini</option>
                        <option value="year">Tahun Ini</option>
                    </select>
                </CardHeader>
                <CardContent class="p-6">
                    <div class="h-[350px] w-full">
                        <Bar :data="chartData" :options="chartOptions" />
                    </div>
                </CardContent>
            </Card>

            <Card class="border-2 border-slate-200 shadow-sm">
                <CardHeader class="bg-slate-50 border-b pb-4 flex flex-row justify-between items-center">
                    <CardTitle class="text-sm font-bold uppercase tracking-widest text-slate-700 flex items-center gap-2">
                        <Wrench class="w-4 h-4 text-slate-500" /> Laporan Mekanik
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b text-[10px] uppercase text-slate-500 tracking-wider">
                            <tr>
                                <th class="p-4 text-left font-bold w-12">Peringkat</th>
                                <th class="p-4 text-left font-bold">Nama Mekanik</th>
                                <th class="p-4 text-center font-bold">Total Mobil Dikerjakan</th>
                                <th class="p-4 text-right font-bold">Estimasi Komisi (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(mechanic, index) in mechanics" :key="index" class="hover:bg-slate-50 transition-colors">
                                <td class="p-4 text-center">
                                    <span v-if="index === 0" class="text-xl">🥇</span>
                                    <span v-else-if="index === 1" class="text-xl">🥈</span>
                                    <span v-else-if="index === 2" class="text-xl">🥉</span>
                                    <span v-else class="text-slate-400 font-bold">#{{ index + 1 }}</span>
                                </td>
                                <td class="p-4 font-bold text-slate-800 text-lg">{{ mechanic.name }}</td>
                                <td class="p-4 text-center">
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-black text-lg">
                                        {{ mechanic.total_cars }} Mobil
                                    </span>
                                </td>
                                <!-- <td class="p-4 text-right font-mono font-black text-green-600 text-lg">
                                    {{ formatIDR(mechanic.total_commission) }}
                                </td> -->
                            </tr>
                            <tr v-if="mechanics.length === 0">
                                <td colspan="4" class="p-8 text-center text-slate-500 italic">Belum ada data pengerjaan mekanik bulan ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>

        </div>
    </AuthenticatedLayout>
</template>