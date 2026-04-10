<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Printer, AlertCircle, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps<{
    invoice: any,
    shop: any
}>();

const formatIDR = (val: number) => {
    return new Intl.NumberFormat('id-ID', { 
        style: 'currency', 
        currency: 'IDR', 
        minimumFractionDigits: 0 
    }).format(val).replace('Rp', 'Rp ');
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const printPage = () => window.print();
</script>

<template>
    <Head :title="'Invoice #' + invoice.id" />

    <div class="min-h-screen bg-slate-50 py-4 px-4 sm:py-12 no-print">
        <div class="max-w-3xl mx-auto">
            
            <div class="bg-white rounded-t-2xl border-x border-t p-6 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex flex-col md:flex-row items-center gap-5 text-center md:text-left">
                    <div v-if="shop?.logo_path" class="w-20 h-20 md:w-24 md:h-24 flex-shrink-0">
                        <img 
                            :src="'/storage/' + shop.logo_path" 
                            :alt="shop?.shop_name" 
                            class="w-full h-full object-contain"
                        />
                    </div>
                    <div v-else class="bg-slate-900 p-4 rounded-2xl flex-shrink-0">
                        <Car class="w-10 h-10 text-white" />
                    </div>

                    <div>
                        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">
                            {{ shop?.shop_name || 'BENGKEL' }}
                        </h1>
                        <p class="text-xs text-slate-500 max-w-xs leading-relaxed">
                            {{ shop?.shop_address }}
                        </p>
                        <p v-if="shop?.shop_phone" class="text-[10px] text-slate-400 mt-1 font-bold">
                            WA: {{ shop.shop_phone }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-col items-center md:items-end">
                    <span :class="[
                        'px-4 py-1 rounded-full text-xs font-black uppercase tracking-widest flex items-center gap-2',
                        invoice.status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                    ]">
                        <CheckCircle2 v-if="invoice.status === 'paid'" class="w-3 h-3" />
                        <AlertCircle v-else class="w-3 h-3" />
                        {{ invoice.status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
                    </span>
                    <div class="mt-2 text-right">
                        <p class="text-[10px] text-slate-400 uppercase tracking-tighter font-bold">Nota #{{ invoice.id }}</p>
                        <p class="text-[10px] text-slate-500 uppercase tracking-tighter">{{ formatDate(invoice.created_at) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-900 text-white p-6 grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Pelanggan</p>
                    <p class="font-bold uppercase text-sm">{{ invoice.work_order.vehicle.customer.name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Kendaraan</p>
                    <p class="font-mono font-bold text-sm">{{ invoice.work_order.vehicle.plate_number }}</p>
                    <p class="text-[10px] opacity-70">{{ invoice.work_order.vehicle.brand }} {{ invoice.work_order.vehicle.model }}</p>
                </div>
            </div>

            <div class="bg-white border-x p-0 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-y text-[10px] uppercase font-bold text-slate-500">
                        <tr>
                            <th class="p-4 text-left">Item</th>
                            <th class="p-4 text-center">Qty</th>
                            <th class="p-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="item in invoice.details" :key="item.id">
                            <td class="p-4">
                                <div class="font-bold text-slate-800">{{ item.item_name }}</div>
                                <div class="text-[10px] text-slate-400">
                                    {{ formatIDR(item.price) }} / unit
                                    <span v-if="item.discount > 0" class="ml-2 text-red-500 font-medium">
                                        (Disc: -{{ formatIDR(item.discount) }})
                                    </span>
                                </div>
                            </td>
                            <td class="p-4 text-center font-medium">{{ item.qty }}</td>
                            <td class="p-4 text-right font-mono font-bold">
                                {{ formatIDR((item.price * item.qty) - (item.discount || 0)) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-slate-50 border rounded-b-2xl p-6 space-y-3">
                <div class="flex justify-between text-xs text-slate-500">
                    <span>Subtotal:</span>
                    <span>{{ formatIDR(invoice.details.reduce((acc, i) => acc + (i.price * i.qty), 0)) }}</span>
                </div>

                <div v-if="invoice.details.some(i => Number(i.discount) > 0)" class="flex justify-between text-xs text-red-500 italic">
                    <span>Total Diskon Item:</span>
                    <span>- {{ formatIDR(invoice.details.reduce((acc, i) => acc + (Number(i.discount) || 0), 0)) }}</span>
                </div>

                <div v-if="Number(invoice.discount_total) > 0" class="flex justify-between text-xs text-red-600 font-medium border-t border-dashed border-slate-300 pt-2">
                    <span>Diskon Tambahan:</span>
                    <span>- {{ formatIDR(invoice.discount_total) }}</span>
                </div>

                <div class="flex justify-between items-center pt-3 border-t-2 border-slate-900 mt-2">
                    <span class="text-sm font-black text-slate-900 uppercase">Total Tagihan:</span>
                    <span class="text-xl font-black text-slate-900">{{ formatIDR(invoice.grand_total) }}</span>
                </div>

                <template v-if="invoice.status !== 'paid'">
                    <div class="mt-4 pt-4 border-t-4 border-double border-slate-200 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Sudah Dibayar (DP):</span>
                            <span class="font-bold text-green-600">{{ formatIDR(invoice.amount_paid) }}</span>
                        </div>
                        <div class="flex justify-between items-center bg-red-50 p-3 rounded-lg border border-red-100">
                            <span class="text-sm font-black text-red-800 uppercase">Sisa yang harus dibayar:</span>
                            <span class="text-lg font-black text-red-700">{{ formatIDR(invoice.grand_total - invoice.amount_paid) }}</span>
                        </div>
                        <p v-if="invoice.due_date" class="text-[10px] text-center text-slate-400 italic pt-1">
                            Jatuh tempo pada: {{ invoice.due_date }}
                        </p>
                    </div>
                </template>
            </div>

            <div class="mt-8 flex flex-col gap-3">
                <button @click="printPage" class="w-full h-12 bg-slate-900 text-white rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-slate-800 transition-colors">
                    <Printer class="w-4 h-4" /> Simpan PDF / Cetak
                </button>
                <p class="text-center text-[10px] text-slate-400 uppercase font-medium">
                    Terima kasih telah melakukan servis di {{ shop?.shop_name }}
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    .no-print { display: none !important; }
}
</style>