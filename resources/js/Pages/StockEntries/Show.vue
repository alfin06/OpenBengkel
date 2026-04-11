<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { ArrowLeft, FileText, Printer, Package } from 'lucide-vue-next';

const props = defineProps<{
    entry: any;
}>();

const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { 
    style: 'currency', 
    currency: 'IDR', 
    minimumFractionDigits: 0 
}).format(val);

const handlePrint = () => {
    window.print();
};

const goBack = () => {
    // If we have a referer, go back, otherwise go to parts index
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.get(route('parts.index'));
    }
};
</script>

<template>
    <Head :title="'Detail Penerimaan #' + entry.reference" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <Button variant="ghost" size="icon" @click="goBack">
                        <ArrowLeft class="w-5 h-5" />
                    </Button>
                    <h2 class="text-xl font-bold uppercase text-slate-800">Detail Nota Masuk</h2>
                </div>
                <Button variant="outline" class="border-slate-200 print:hidden" @click="handlePrint">
                    <Printer class="w-4 h-4 mr-2" /> Cetak Laporan
                </Button>
            </div>
        </template>

        <div class="py-12 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <Card class="border-none shadow-sm ring-1 ring-slate-200">
                <CardHeader class="bg-slate-50 border-b p-6">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Supplier</p>
                            <h3 class="text-2xl font-black text-blue-600">{{ entry.supplier_name }}</h3>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nomor Referensi</p>
                            <p class="font-mono font-bold text-slate-700">{{ entry.reference || 'N/A' }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ entry.date }}</p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="bg-slate-50/50 border-b text-[10px] uppercase font-bold text-slate-500">
                            <tr>
                                <th class="p-4">Item Sparepart</th>
                                <th class="p-4 text-center">Qty</th>
                                <th class="p-4 text-right">Harga Beli</th>
                                <th class="p-4 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in entry.items" :key="item.id">
                                <td class="p-4">
                                    <div class="font-bold text-slate-800">{{ item.part_name }}</div>
                                    <div class="text-[10px] font-mono text-slate-400 uppercase">{{ item.sku || '-' }}</div>
                                </td>
                                <td class="p-4 text-center font-bold text-slate-600">{{ item.qty }}</td>
                                <td class="p-4 text-right">{{ formatIDR(item.price) }}</td>
                                <td class="p-4 text-right font-bold text-slate-900">{{ formatIDR(item.subtotal) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-900 text-white">
                            <tr>
                                <td colspan="3" class="p-4 text-right text-[10px] uppercase font-bold tracking-widest text-slate-400">Total Keseluruhan</td>
                                <td class="p-4 text-right text-xl font-black">{{ formatIDR(entry.total_cost) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </CardContent>
            </Card>

            <div v-if="entry.notes" class="p-6 bg-amber-50 rounded-2xl border border-amber-100 border-dashed">
                <p class="text-[10px] font-black text-amber-600 uppercase mb-2 flex items-center gap-2">
                    <FileText class="w-4 h-4" /> Catatan Tambahan
                </p>
                <p class="text-sm text-amber-800 italic leading-relaxed">{{ entry.notes }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>