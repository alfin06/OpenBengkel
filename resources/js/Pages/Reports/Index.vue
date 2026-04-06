<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { TrendingUp, TrendingDown, Wallet, Landmark, Calendar } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    stats: { 
        revenue: number; 
        expenses: number; 
        profit: number; 
        margin: number;
        annualRevenue: number; 
        annualExpenses: number; 
    };
    filters: { month: number; year: number };
    availableYears: number[];
    topItems: any[];
}>();

const selectedMonth = ref(props.filters.month);
const selectedYear = ref(props.filters.year);

// Watch both Month AND Year for changes
watch([selectedMonth, selectedYear], ([newMonth, newYear]) => {
    router.get(route('admin.reports.index'), 
        { month: newMonth, year: newYear }, 
        { preserveState: true, preserveScroll: true }
    );
});

const formatIDR = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0
    }).format(value || 0);
};

const months = [
    { v: 1, n: 'Januari' }, { v: 2, n: 'Februari' }, { v: 3, n: 'Maret' },
    { v: 4, n: 'April' }, { v: 5, n: 'Mei' }, { v: 6, n: 'Juni' },
    { v: 7, n: 'Juli' }, { v: 8, n: 'Agustus' }, { v: 9, n: 'September' },
    { v: 10, n: 'Oktober' }, { v: 11, n: 'November' }, { v: 12, n: 'Desember' }
];
</script>

<template>
    <Head title="Laporan Keuangan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <h2 class="font-black text-xl text-slate-800 uppercase tracking-widest">Laporan Keuangan</h2>
                
                <div class="flex items-center gap-2 bg-white p-1 rounded-xl border shadow-sm">
                    <div class="flex items-center gap-2 px-3 border-r">
                        <Calendar class="w-4 h-4 text-slate-400" />
                        <select v-model="selectedMonth" class="border-none text-sm font-bold focus:ring-0 cursor-pointer bg-transparent">
                            <option v-for="m in months" :key="m.v" :value="m.v">{{ m.n }}</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center gap-2 px-3">
                        <select v-model="selectedYear" class="border-none text-sm font-bold focus:ring-0 cursor-pointer bg-transparent">
                            <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card class="border-l-4 border-l-blue-500 shadow-sm">
                    <CardHeader class="pb-2"><CardTitle class="text-xs font-bold text-slate-500 uppercase">Total Pemasukan</CardTitle></CardHeader>
                    <CardContent>
                        <div class="text-2xl font-black text-slate-900">{{ formatIDR(stats?.revenue) }}</div>
                        <div class="flex items-center text-blue-600 text-xs font-bold mt-1"><TrendingUp class="w-3 h-3 mr-1" /> Gross Revenue</div>
                    </CardContent>
                </Card>

                <Card class="border-l-4 border-l-red-500 shadow-sm">
                    <CardHeader class="pb-2"><CardTitle class="text-xs font-bold text-slate-500 uppercase">Total Pengeluaran</CardTitle></CardHeader>
                    <CardContent>
                        <div class="text-2xl font-black text-slate-900">{{ formatIDR(stats?.expenses) }}</div>
                        <div class="flex items-center text-red-600 text-xs font-bold mt-1"><TrendingDown class="w-3 h-3 mr-1" /> Biaya Operasional</div>
                    </CardContent>
                </Card>

                <Card :class="(stats?.profit || 0) >= 0 ? 'border-l-4 border-l-green-500' : 'border-l-4 border-l-orange-500'" class="shadow-sm bg-slate-900 text-white">
                    <CardHeader class="pb-2"><CardTitle class="text-xs font-bold text-slate-400 uppercase">Laba Bersih</CardTitle></CardHeader>
                    <CardContent>
                        <div class="text-2xl font-black" :class="(stats?.profit || 0) >= 0 ? 'text-green-400' : 'text-orange-400'">
                            {{ formatIDR(stats?.profit) }}
                        </div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-1">Profit Margin: {{ stats?.margin || 0 }}%</div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-6">
                    <Card class="border-2 border-slate-100">
                        <CardHeader><CardTitle class="text-sm font-bold flex items-center gap-2"><Wallet class="w-4 h-4 text-blue-600" /> Ringkasan Performa</CardTitle></CardHeader>
                        <CardContent class="space-y-4">
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Bulan ini, bengkel menghasilkan untung bersih sebesar <strong class="text-slate-900">{{ formatIDR(stats?.profit) }}</strong>. 
                                Setiap Rp 1.000.000 yang masuk, bengkel menyimpan sekitar <strong class="text-slate-900">{{ formatIDR((stats?.margin || 0) * 10000) }}</strong> sebagai profit.
                            </p>
                        </CardContent>
                    </Card>

                    <Card class="border-2 border-slate-100">
                        <CardHeader>
                            <CardTitle class="text-sm font-bold flex items-center gap-2">
                                <TrendingUp class="w-4 h-4 text-green-600" /> Item & Jasa Terlaris
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="(item, index) in topItems || []" :key="index" class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                                    <div>
                                        <p class="text-xs font-bold text-slate-700 uppercase">{{ item.name }}</p>
                                        <p class="text-[10px] text-slate-400">{{ item.total_qty }} Kali Terjual</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-black text-slate-900">{{ formatIDR(item.revenue) }}</p>
                                    </div>
                                </div>
                                <p v-if="!topItems || topItems.length === 0" class="text-xs text-slate-400 italic text-center py-4">
                                    Belum ada data penjualan bulan ini.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card class="border-2 border-slate-100">
                    <CardHeader><CardTitle class="text-sm font-bold flex items-center gap-2"><Landmark class="w-4 h-4 text-blue-600" /> Struktur Biaya</CardTitle></CardHeader>
                    <CardContent class="flex items-center justify-center h-full min-h-[300px] border-2 border-dashed border-slate-100 rounded-xl">
                        <p class="text-xs text-slate-400 italic font-medium">Grafik akan muncul di sini saat data terkumpul.</p>
                    </CardContent>
                </Card>
            </div>

            <Card class="bg-slate-50 border-dashed border-2 border-slate-200 mt-8 overflow-hidden relative">
                <div class="absolute top-0 left-0 w-1 h-full bg-slate-400"></div>
                <CardContent class="p-6 flex flex-col md:flex-row justify-between items-center gap-6">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <Landmark class="w-4 h-4 text-slate-500" />
                            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest">
                                Akumulasi Tahun {{ selectedYear }}
                            </h3>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium leading-tight">
                            Total performa finansial dari 1 Januari {{ selectedYear }} sampai hari ini.
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap justify-center gap-8 md:gap-12">
                        <div class="text-center md:text-left">
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Omzet Tahunan</p>
                            <p class="text-lg font-black text-blue-600">{{ formatIDR(stats?.annualRevenue) }}</p>
                        </div>
                        <div class="text-center md:text-left">
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Pengeluaran Tahunan</p>
                            <p class="text-lg font-black text-red-500">{{ formatIDR(stats?.annualExpenses) }}</p>
                        </div>
                        <div class="text-center md:text-left">
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Laba Bersih Tahunan</p>
                            <p class="text-xl font-black text-green-600">
                                {{ formatIDR((stats?.annualRevenue || 0) - (stats?.annualExpenses || 0)) }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>