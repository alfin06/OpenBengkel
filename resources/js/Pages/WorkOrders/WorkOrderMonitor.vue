<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { reactive, computed } from 'vue'; // Added computed
import { 
    Car, UserCircle, Calendar, Wrench, FileText, 
    Search, History, Clock 
} from 'lucide-vue-next';

const props = defineProps(['workOrders', 'filters']);

// 1. Force defaults to empty strings instead of null
const form = reactive({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    status: props.filters.status || 'default',
});

// 2. Create a sanitized data object for the router
const cleanData = computed(() => {
    const data = { ...form };
    Object.keys(data).forEach(key => {
        if (data[key] === null || data[key] === undefined || data[key] === '') {
            delete data[key];
        }
    });
    return data;
});

const applyFilters = () => {
    // Manually construct the URL parameters to avoid the toString() crash
    const params = new URLSearchParams();
    
    if (form.start_date) params.append('start_date', form.start_date);
    if (form.end_date) params.append('end_date', form.end_date);
    if (form.status) params.append('status', form.status);

    const baseUrl = route('admin.work-orders.monitor');
    const fullUrl = `${baseUrl}?${params.toString()}`;

    router.get(fullUrl, {}, { 
        preserveState: true,
        replace: true 
    });
};

const statusColor = (status) => {
    const colors = {
        pending: 'bg-amber-50 text-amber-700 border-amber-100',
        proses: 'bg-blue-50 text-blue-700 border-blue-100',
        finished: 'bg-emerald-50 text-emerald-700 border-emerald-100',
        invoiced: 'bg-slate-50 text-slate-700 border-slate-200',
    };
    return colors[status] || 'bg-gray-50 text-gray-600';
};
</script>

<template>
    <Head title="Monitoring Bengkel" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <History class="w-6 h-6 text-blue-600" />
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Monitoring Antrian & Status Kerja
                </h2>
            </div>
        </template>

        <div class="py-12 bg-gray-50/50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-6 rounded-xl shadow-sm mb-6 border border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-xs font-bold uppercase text-gray-500">
                                <Calendar class="w-3.5 h-3.5" /> Dari Tanggal
                            </label>
                            <input type="date" v-model="form.start_date" class="w-full border-gray-200 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-xs font-bold uppercase text-gray-500">
                                <Calendar class="w-3.5 h-3.5" /> Sampai Tanggal
                            </label>
                            <input type="date" v-model="form.end_date" class="w-full border-gray-200 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-xs font-bold uppercase text-gray-500">
                                <Clock class="w-3.5 h-3.5" /> Filter Status
                            </label>
                            <select v-model="form.status" class="w-full border-gray-200 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="default">Default (Pending & Proses)</option>
                                <option value="all">Tampilkan Semua</option>
                                <option value="pending">Pending</option>
                                <option value="proses">Proses</option>
                                <option value="finished">Selesai (Belum Bayar)</option>
                                <option value="invoiced">Lunas (Invoiced)</option>
                            </select>
                        </div>
                        <button @click="applyFilters" class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg transition-all shadow-sm shadow-blue-200">
                            <Search class="w-4 h-4" />
                            <span>Cari Data</span>
                        </button>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50/80 border-b">
                                <tr>
                                    <th class="px-6 py-4 font-bold">Waktu</th>
                                    <th class="px-6 py-4 font-bold text-center">WO/Invoice</th>
                                    <th class="px-6 py-4 font-bold">Pelanggan & Unit</th>
                                    <th class="px-6 py-4 text-center font-bold">Status</th>
                                    <th class="px-6 py-4 text-center font-bold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="wo in workOrders.data" :key="wo.id" class="hover:bg-blue-50/30 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ new Date(wo.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ new Date(wo.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">WO #{{ wo.id }}</span>
                                            <span v-if="wo.invoice" class="text-[10px] font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded">INV-{{ wo.invoice.id }}</span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 bg-gray-100 rounded-full text-gray-400 group-hover:bg-white group-hover:text-blue-500 transition">
                                                <UserCircle class="w-5 h-5" />
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900">{{ wo.customer?.name }}</div>
                                                <div class="flex items-center gap-1 text-xs text-gray-500 uppercase tracking-tight">
                                                    <Car class="w-3 h-3" /> {{ wo.vehicle?.plate_number }} • {{ wo.vehicle?.model }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span :class="statusColor(wo.status)" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border">
                                            {{ wo.status }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <Link :href="route('work-orders.show', wo.id)" 
                                                  class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                                                  title="Detail Work Order">
                                                <Wrench class="w-5 h-5" />
                                            </Link>
                                            
                                            <Link v-if="wo.invoice" 
                                                  :href="route('invoices.show', wo.invoice.uuid)" 
                                                  class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all"
                                                  title="Lihat Invoice">
                                                <FileText class="w-5 h-5" />
                                            </Link>
                                            
                                            <div v-else class="w-9"></div>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr v-if="workOrders.length === 0">
                                    <td colspan="5" class="px-6 py-20 text-center">
                                        <div class="flex flex-col items-center gap-2 text-gray-400">
                                            <Search class="w-12 h-12 opacity-20" />
                                            <p class="text-sm italic">Tidak ada data ditemukan untuk filter ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="workOrders.links.length > 3" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-b-xl">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link :href="workOrders.prev_page_url" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> 
                                Previous 
                            </Link>
                            <Link :href="workOrders.next_page_url" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> 
                                Next 
                            </Link>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Menampilkan <span class="font-medium">{{ workOrders.from }}</span> ke <span class="font-medium">{{ workOrders.to }}</span> dari <span class="font-medium">{{ workOrders.total }}</span> hasil
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <template v-for="(link, k) in workOrders.links" :key="k">
                                        <div v-if="link.url === null" 
                                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium text-gray-400 bg-gray-50 cursor-default"
                                            v-html="link.label"
                                        />

                                        <Link v-else
                                            :href="link.url"
                                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors"
                                            :class="link.active 
                                                ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' 
                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                            v-html="link.label"
                                            :data="{}"        
                                            :merge="false"
                                            preserve-state
                                        />
                                    </template>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>