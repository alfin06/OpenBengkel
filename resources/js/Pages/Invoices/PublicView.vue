<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CheckCircle2, Clock, Printer, Package, Wrench, ShieldCheck } from 'lucide-vue-next';

const props = defineProps<{
    invoice: any,
    shop: any
}>();

const formatIDR = (val: number) => {
    return new Intl.NumberFormat('id-ID', { 
        style: 'currency', 
        currency: 'IDR', 
        minimumFractionDigits: 0 
    }).format(val);
};

const printPage = () => window.print();
</script>

<template>
    <Head :title="'Invoice #' + invoice.id" />

    <div class="min-h-screen bg-slate-50 py-4 px-4 sm:py-12 no-print">
        <div class="max-w-3xl mx-auto">
            
            <div class="bg-white rounded-t-2xl border-x border-t p-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-center md:text-left">
                    <h1 class="text-2xl font-black text-slate-900 uppercase">{{ shop?.shop_name || 'BENGKEL' }}</h1>
                    <p class="text-xs text-slate-500 max-w-xs">{{ shop?.shop_address }}</p>
                </div>
                <div class="flex flex-col items-center md:items-end">
                    <span :class="[
                        'px-4 py-1 rounded-full text-xs font-black uppercase tracking-widest',
                        invoice.status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                    ]">
                        {{ invoice.status === 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
                    </span>
                    <p class="text-[10px] text-slate-400 mt-2 uppercase">Nota #{{ invoice.id }}</p>
                </div>
            </div>

            <div class="bg-slate-900 text-white p-6 grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Pelanggan</p>
                    <p class="font-bold uppercase">{{ invoice.work_order.vehicle.customer.name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Kendaraan</p>
                    <p class="font-mono font-bold">{{ invoice.work_order.vehicle.plate_number }}</p>
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

            <div class="bg-slate-50 border rounded-b-2xl p-6 space-y-2">
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Subtotal:</span>
                    <span>{{ formatIDR(invoice.details.reduce((acc, i) => acc + (i.price * i.qty), 0)) }}</span>
                </div>

                <div v-if="invoice.details.some(i => Number(i.discount) > 0)" class="flex justify-between text-sm text-red-500 italic">
                    <span>Discount Item:</span>
                    <span>-{{ formatIDR(invoice.details.reduce((acc, i) => acc + (Number(i.discount) || 0), 0)) }}</span>
                </div>

                <div v-if="Number(invoice.discount_total) > 0" class="flex justify-between text-sm text-red-600 font-medium border-t border-dashed border-slate-300 pt-2">
                    <span>Discount Tambahan:</span>
                    <span>-{{ formatIDR(invoice.discount_total) }}</span>
                </div>

                <div class="flex justify-between items-center pt-3 border-t-2 border-slate-900 mt-2">
                    <span class="text-lg font-black text-slate-900 uppercase tracking-tighter">Total Bayar:</span>
                    <span class="text-2xl font-black text-blue-700">{{ formatIDR(invoice.grand_total) }}</span>
                </div>
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