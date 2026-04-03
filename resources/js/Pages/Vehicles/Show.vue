<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Car, UserCircle, Calendar, Wrench, FileText, ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
    vehicle: any;
}>();

const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
const formatDate = (dateString: string) => new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
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
                        <p class="text-sm text-slate-500">{{ vehicle.customer?.phone }}</p>
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-center gap-2 mb-4 mt-8">
                <History class="w-5 h-5 text-slate-400" />
                <h3 class="text-lg font-bold text-slate-800">Riwayat Servis</h3>
            </div>

            <div v-if="vehicle.work_orders?.length === 0" class="text-center py-12 bg-white rounded-xl border border-slate-200 shadow-sm">
                <Wrench class="w-12 h-12 text-slate-200 mx-auto mb-3" />
                <p class="text-slate-500 font-medium">Belum ada riwayat servis untuk kendaraan ini.</p>
            </div>

            <div class="space-y-4">
                <Card v-for="wo in vehicle.work_orders" :key="wo.id" class="border-slate-200 shadow-sm overflow-hidden transition-all hover:shadow-md">
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
                                        <p class="text-sm font-bold">{{ wo.mechanic_name || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col justify-center border-l border-slate-100 pl-6">
                                <div v-if="wo.invoice">
                                    <p class="text-[10px] uppercase font-bold text-slate-400 mb-1">Total Tagihan</p>
                                    <p class="text-xl font-black text-slate-900 mb-3">{{ formatIDR(wo.invoice.grand_total) }}</p>
                                    <Link :href="route('work-orders.show', wo.id)">
                                        <Button size="sm" class="w-full bg-slate-900 hover:bg-slate-800 text-white shadow-sm">
                                            <Wrench class="w-4 h-4 mr-2" /> Detail WO
                                        </Button>
                                    </Link>
                                    <Link :href="route('invoices.create', wo.id)">
                                        <Button variant="outline" size="sm" class="w-full text-blue-600 border-blue-200 hover:bg-blue-50">
                                            <FileText class="w-4 h-4 mr-2" /> Lihat Nota
                                        </Button>
                                    </Link>
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