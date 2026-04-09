<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Wrench, AlertCircle, CarFront, FileText, Receipt } from 'lucide-vue-next';

const props = defineProps<{
    activeWorkOrders: any[];
    receivablesDueToday: any[];
}>();

const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
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
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <Card class="lg:col-span-2 shadow-sm border-slate-200">
                    <CardHeader class="bg-slate-50 border-b">
                        <CardTitle class="flex items-center gap-2 text-slate-700 uppercase text-sm tracking-wider">
                            <Wrench class="w-5 h-5 text-blue-600" /> Kendaraan Sedang Dikerjakan
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-0">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-100 text-slate-500 text-xs uppercase border-b">
                                <tr>
                                    <th class="p-4 text-left font-bold">Plat & Kendaraan</th>
                                    <th class="p-4 text-left font-bold">Status</th>
                                    <th class="p-4 text-right font-bold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="wo in activeWorkOrders" :key="wo.id" class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4">
                                        <div class="font-bold text-blue-700 text-base uppercase">{{ wo.vehicle?.plate_number }}</div>
                                        <div class="text-xs text-slate-500">{{ wo.vehicle?.brand }} {{ wo.vehicle?.model }}</div>
                                        <div class="text-[10px] text-slate-400 mt-1">Cust: {{ wo.vehicle?.customer?.name }}</div>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-[10px] font-black uppercase tracking-widest rounded border border-yellow-200">
                                            {{ wo.status || 'PROSES' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <Link :href="route('work-orders.show', wo.id)">
                                            <Button variant="outline" size="sm" class="text-blue-600 border-blue-200 hover:bg-blue-50">
                                                <Wrench class="w-4 h-4 mr-1" /> Detail WO
                                            </Button>
                                        </Link>
                                        <Link :href="route('invoices.create', wo.id)">
                                            <Button variant="outline" size="sm" class="text-green-600 border-green-200 hover:bg-green-50 ml-2">
                                                <FileText class="w-4 h-4 mr-1" /> Lihat Nota
                                            </Button>
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="activeWorkOrders.length === 0">
                                    <td colspan="3" class="p-12 text-center text-slate-400">
                                        <CarFront class="w-12 h-12 mx-auto mb-3 opacity-20" />
                                        <p class="font-medium">Tidak ada kendaraan di bengkel.</p>
                                        <p class="text-xs">Area kerja bersih dan aman!</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </CardContent>
                </Card>

                <div class="space-y-6">
                    <Card class="border-red-200 shadow-sm overflow-hidden">
                        <CardHeader class="bg-red-50 border-b border-red-100">
                            <CardTitle class="text-red-700 flex items-center gap-2 text-sm uppercase font-black tracking-wider">
                                <AlertCircle class="w-4 h-4" /> Tagihan Hari Ini
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
                                        <span>Sisa Tagihan:</span>
                                        <span class="font-bold text-slate-900 text-sm">{{ formatIDR(inv.grand_total - inv.amount_paid) }}</span>
                                    </div>
                                    <Link :href="route('receivables.index')" class="block">
                                        <Button size="sm" class="w-full bg-slate-900 hover:bg-slate-800 text-white text-xs h-8">
                                            Buka Daftar Piutang
                                        </Button>
                                    </Link>
                                </div>
                                <div v-if="receivablesDueToday.length === 0" class="p-8 text-center text-slate-400">
                                    <AlertCircle class="w-8 h-8 mx-auto mb-2 opacity-20" />
                                    <p class="text-xs font-medium">Tidak ada jadwal penagihan hari ini.</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>