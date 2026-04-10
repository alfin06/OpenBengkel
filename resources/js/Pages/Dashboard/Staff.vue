<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input'; // Make sure this is imported
import { 
    Wrench, AlertCircle, CarFront, FileText, 
    User, Gauge, Search, SearchX, ArrowRight
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
    activeWorkOrders: any[];
    receivablesDueToday: any[];
}>();

const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

const getStatusClass = (status: string) => {
    switch (status?.toUpperCase()) {
        case 'DRAFT': return 'bg-slate-100 text-slate-600 border-slate-200';
        case 'PROSES': return 'bg-blue-100 text-blue-700 border-blue-200';
        case 'SELESAI': return 'bg-green-100 text-green-700 border-green-200';
        default: return 'bg-yellow-100 text-yellow-700 border-yellow-200';
    }
};

const searchQuery = ref('');

const filteredWorkOrders = computed(() => {
    return props.activeWorkOrders.filter(wo => {
        const search = searchQuery.value.toLowerCase();
        const plate = wo.vehicle?.plate_number?.toLowerCase() || '';
        const brand = wo.vehicle?.brand?.toLowerCase() || '';
        const model = wo.vehicle?.model?.toLowerCase() || '';
        const customer = wo.vehicle?.customer?.name?.toLowerCase() || '';
        
        return plate.includes(search) || 
               brand.includes(search) || 
               model.includes(search) || 
               customer.includes(search);
    });
});
</script>

<template>
    <Head title="Staff Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold uppercase text-slate-800 flex items-center gap-2">
                <CarFront class="w-6 h-6" /> 
                Dashboard
            </h2>
        </template>

        <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                <div class="lg:col-span-3 space-y-4">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-2">
                        <h3 class="flex items-center gap-2 text-slate-700 uppercase text-sm font-black tracking-widest">
                            <Wrench class="w-5 h-5 text-blue-600" /> Kendaraan Di Bengkel
                            <span class="ml-2 bg-blue-600 text-white text-[10px] px-2 py-0.5 rounded-full">
                                {{ filteredWorkOrders.length }}
                            </span>
                        </h3>

                        <div class="relative w-full md:w-72">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <Input 
                                v-model="searchQuery"
                                placeholder="Cari Plat, Mobil, atau Nama..." 
                                class="pl-10 h-10 bg-white border-slate-200 focus:ring-blue-500 rounded-lg shadow-sm"
                            />
                        </div>
                    </div>

                    <div v-if="filteredWorkOrders.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <Card v-for="wo in filteredWorkOrders" :key="wo.id" class="shadow-sm hover:shadow-md transition-shadow border-slate-200 overflow-hidden">
                            <div class="bg-slate-900 p-3 flex justify-between items-center">
                                <div class="font-mono font-black text-xl text-white tracking-wider uppercase">
                                    {{ wo.vehicle?.plate_number }}
                                </div>
                                <span :class="['px-2 py-0.5 text-[9px] font-black uppercase tracking-tighter rounded border', getStatusClass(wo.status)]">
                                    {{ wo.status || 'PROSES' }}
                                </span>
                            </div>

                            <CardContent class="p-4 space-y-3">
                                <div class="space-y-1">
                                    <div class="text-sm font-bold text-slate-800 uppercase">
                                        {{ wo.vehicle?.brand }} {{ wo.vehicle?.model }}
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                        <User class="w-3 h-3" /> {{ wo.vehicle?.customer?.name || 'Umum' }}
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-slate-400">
                                        <Gauge class="w-3 h-3" /> {{ wo.odometer ? Number(wo.odometer).toLocaleString() : '-' }} KM
                                    </div>
                                </div>

                                <hr class="border-slate-100" />

                                <div class="grid grid-cols-2 gap-2">
                                    <Link :href="route('work-orders.show', wo.id)" class="w-full">
                                        <Button variant="outline" size="sm" class="w-full text-[11px] h-9 gap-1 border-slate-200">
                                            <Wrench class="w-3.5 h-3.5" /> Detail WO
                                        </Button>
                                    </Link>
                                    <Link :href="route('invoices.create', wo.id)" class="w-full">
                                        <Button size="sm" class="w-full text-[11px] h-9 gap-1 bg-blue-600 hover:bg-blue-700">
                                            <FileText class="w-3.5 h-3.5" /> {{ wo.status === 'DRAFT' ? 'Lanjut Nota' : 'Buka Nota' }}
                                        </Button>
                                    </Link>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <Card v-else-if="searchQuery !== ''" class="p-12 text-center border-dashed border-2 border-slate-200 bg-slate-50/50">
                        <SearchX class="w-12 h-12 mx-auto mb-3 text-slate-300 opacity-50" />
                        <p class="text-slate-500 font-bold uppercase tracking-tight">Tidak Ditemukan</p>
                        <p class="text-xs text-slate-400">Pencarian "{{ searchQuery }}" tidak cocok.</p>
                        <Button variant="link" @click="searchQuery = ''" class="text-blue-600 text-xs mt-2">Hapus Pencarian</Button>
                    </Card>

                    <Card v-else class="p-12 text-center border-dashed border-2 border-slate-200 bg-slate-50/50">
                        <CarFront class="w-16 h-16 mx-auto mb-4 text-slate-300 opacity-50" />
                        <p class="text-slate-500 font-bold uppercase tracking-tight">Bengkel Sedang Kosong</p>
                        <p class="text-xs text-slate-400">Belum ada kendaraan yang didaftarkan hari ini.</p>
                    </Card>
                </div>

                <div class="space-y-6">
                    <Card class="border-red-200 shadow-sm overflow-hidden">
                        <CardHeader class="bg-red-50 border-b border-red-100">
                            <CardTitle class="text-red-700 flex items-center gap-2 text-sm uppercase font-black tracking-wider">
                                <AlertCircle class="w-4 h-4" /> Tagihan Jatuh Tempo
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-0 bg-white">
                            <div class="divide-y divide-slate-50">
                                <div v-for="inv in receivablesDueToday" :key="inv.id" class="p-4 hover:bg-slate-50 transition-colors">
                                    <div class="flex justify-between items-start mb-1">
                                        <div class="font-bold text-sm text-slate-800">{{ inv.work_order?.vehicle?.customer?.name || 'Customer' }}</div>
                                        <span class="text-[10px] font-mono font-bold text-red-500 bg-red-50 px-1 rounded">#{{ inv.id }}</span>
                                    </div>
                                    <div class="text-xs text-slate-500 mb-3 flex justify-between items-end">
                                        <span>Tagihan:</span>
                                        <span class="font-bold text-red-600 text-sm">{{ formatIDR(inv.grand_total - inv.amount_paid) }}</span>
                                    </div>
                                    <Link :href="route('receivables.index')" class="block">
                                        <Button size="sm" variant="outline" class="w-full border-red-200 text-red-600 hover:bg-red-50 text-[10px] h-7 uppercase font-bold">
                                            Bayar Sekarang
                                        </Button>
                                    </Link>
                                </div>
                                <div v-if="receivablesDueToday.length === 0" class="p-8 text-center text-slate-400">
                                    <AlertCircle class="w-8 h-8 mx-auto mb-2 opacity-20" />
                                    <p class="text-[10px] font-bold uppercase">Aman</p>
                                    <p class="text-[10px]">Tidak ada tagihan hari ini.</p>
                                    <hr class="my-4 border-slate-100" />
                                
                                    <Link :href="route('receivables.index')" class="block">
                                        <Button size="sm" variant="ghost" class="w-full text-slate-500 hover:text-blue-600 hover:bg-blue-50 text-[10px] h-8 gap-2 font-bold uppercase">
                                            Lihat Semua Tagihan <ArrowRight class="w-3 h-3" />
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>