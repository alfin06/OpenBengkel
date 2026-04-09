<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input'; // Ensure this is imported
import { Car, UserCircle, Calendar, Wrench, FileText, ArrowLeft, Search, History } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    vehicle: any;
}>();

const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
const formatDate = (dateString: string) => new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

const searchQuery = ref('');

const filteredHistory = computed(() => {
    // 1. If no search query, show all work orders
    if (!searchQuery.value || searchQuery.value.trim() === '') {
        return props.vehicle.work_orders || [];
    }

    const query = searchQuery.value.toLowerCase().trim();
    
    return (props.vehicle.work_orders || []).filter(wo => {
        // A. Search in ID (must be exact match or start with the number)
        const matchesId = wo.id?.toString() === query || wo.id?.toString().startsWith(query);

        // B. Search in Mechanic Name
        const matchesMechanic = wo.mechanic?.name?.toLowerCase().includes(query);

        // C. Search in Complaint
        const matchesComplaint = wo.complaint?.toLowerCase().includes(query);

        // D. Search in Work Order Items (Tasks/Jasa)
        const matchesTasks = wo.items?.some((item: any) => 
            item.task_name?.toLowerCase().includes(query)
        );

        // E. Search in Invoice Details (Spareparts)
        const matchesParts = wo.invoice?.details?.some((detail: any) => 
            detail.item_name?.toLowerCase().includes(query)
        );
        
        // Return TRUE only if one of these specific areas matches
        // If none match, this 'wo' (Work Order) will be hidden from the list
        return matchesId || matchesMechanic || matchesComplaint || matchesTasks || matchesParts;
    });
});
</script>

<template>
    <Head :title="`History - ${vehicle.plate_number}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="bg-slate-900 p-2 rounded-lg">
                        <Car class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900 uppercase tracking-widest">{{ vehicle.plate_number }}</h2>
                        <p class="text-sm font-medium text-slate-500">{{ vehicle.brand }} {{ vehicle.model }} • {{ vehicle.year || '-' }} • {{ vehicle.color || '-' }}</p>
                        <p class="text-sm font-medium text-slate-500">No. chasis: {{ vehicle.chassis_number || '-' }} | No. mesin: {{ vehicle.engine_number || '-' }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('work-orders.create', { vehicle_id: vehicle.id })">
                        <Button class="bg-blue-600 hover:bg-blue-700 font-bold">Check-In Kendaraan</Button>
                    </Link>
                    <Link :href="route('customers.show', vehicle.customer_id)">
                        <Button variant="outline"><ArrowLeft class="w-4 h-4 mr-2" /> Kembali</Button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <Card class="border-none shadow-sm">
                <CardContent class="p-6 flex items-center gap-4">
                    <UserCircle class="w-10 h-10 text-slate-400" />
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Pemilik Kendaraan</p>
                        <p class="font-bold text-lg text-slate-800">{{ vehicle.customer?.name }}</p>
                        <p class="text-sm text-slate-500">{{ vehicle.customer?.phone_1 || vehicle.customer?.phone || '-' }}</p>
                    </div>
                </CardContent>
            </Card>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mt-8">
                <div class="flex items-center gap-2">
                    <History class="w-5 h-5 text-slate-400" />
                    <h3 class="text-lg font-bold text-slate-800">Riwayat Servis</h3>
                </div>

                <div class="relative w-full md:w-96">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <Input 
                        v-model="searchQuery" 
                        placeholder="Cari mekanik, jasa, atau sparepart..." 
                        class="pl-10 bg-white border-slate-200 shadow-sm"
                    />
                </div>
            </div>

            <div v-if="filteredHistory.length === 0" class="text-center py-12 bg-white rounded-xl border border-dashed border-slate-300">
                <Wrench class="w-12 h-12 text-slate-200 mx-auto mb-3" />
                <p class="text-slate-500 font-medium">Data tidak ditemukan.</p>
            </div>

            <div class="space-y-4">
                <Card v-for="wo in filteredHistory" :key="wo.id" class="border-slate-200 shadow-sm overflow-hidden transition-all hover:shadow-md">
                    <div class="bg-slate-50 px-6 py-3 border-b flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-bold text-slate-700 flex items-center gap-2">
                                <Calendar class="w-4 h-4 text-slate-400" /> {{ formatDate(wo.created_at) }}
                            </span>
                            <span class="px-2 py-0.5 text-[10px] uppercase font-bold tracking-widest rounded"
                                  :class="wo.status === 'Selesai' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
                                {{ wo.status }}
                            </span>
                        </div>
                        <span class="font-mono text-sm font-bold text-slate-500">WO-#{{ wo.id }}</span>
                    </div>
                    
                    <CardContent class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="md:col-span-3 space-y-4">
                                <div v-if="wo.complaint">
                                    <p class="text-[10px] uppercase font-bold text-slate-400 mb-1">Keluhan Pelanggan</p>
                                    <p class="text-sm text-slate-700">{{ wo.complaint }}</p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-lg border border-slate-100">
                                    <div>
                                        <p class="text-[10px] uppercase font-bold text-slate-400">Kilometer (Odo)</p>
                                        <p class="text-sm font-mono font-bold">{{ wo.odometer ? Number(wo.odometer).toLocaleString() + ' KM' : '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] uppercase font-bold text-slate-400">Mekanik</p>
                                        <p class="text-sm font-bold text-blue-700">{{ wo.mechanic?.name || '-' }}</p>
                                    </div>
                                </div>

                                <div v-if="searchQuery && (wo.items?.length || wo.invoice?.details?.length)" class="mt-2 pt-2 border-t border-dashed">
                                    <p class="text-[9px] uppercase font-black text-blue-500 mb-1 tracking-tighter">Hasil Pencarian Detail:</p>
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="item in wo.items" :key="item.id" class="text-[10px] px-2 py-0.5 bg-blue-50 text-blue-700 rounded border border-blue-100">
                                            {{ item.task_name }}
                                        </span>
                                        <span v-for="detail in wo.invoice?.details" :key="detail.id" class="text-[10px] px-2 py-0.5 bg-green-50 text-green-700 rounded border border-green-100">
                                            {{ detail.item_name }} (x{{ detail.qty }})
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col justify-center border-l border-slate-100 pl-6">
                                <div v-if="wo.invoice" class="space-y-3">
                                    <div>
                                        <p class="text-[10px] uppercase font-bold text-slate-400 mb-1">Total Tagihan</p>
                                        <p class="text-xl font-black text-slate-900">{{ formatIDR(wo.invoice.grand_total) }}</p>
                                    </div>
                                    <div class="space-y-2">
                                        <Link :href="route('work-orders.show', wo.id)">
                                            <Button size="sm" class="w-full bg-slate-900 hover:bg-slate-800 text-white shadow-sm gap-2">
                                                <Wrench class="w-4 h-4" /> Detail WO
                                            </Button>
                                        </Link>
                                        <Link :href="route('invoices.create', wo.id)">
                                            <Button variant="outline" size="sm" class="w-full text-blue-600 border-blue-200 hover:bg-blue-50 gap-2">
                                                <FileText class="w-4 h-4" /> Lihat Nota
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                                <div v-else class="text-center text-slate-400 text-sm italic">
                                    Nota belum diterbitkan.
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>