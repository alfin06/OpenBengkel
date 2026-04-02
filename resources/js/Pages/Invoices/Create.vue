<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Receipt, CreditCard, Wallet, Banknote, Percent, Plus } from 'lucide-vue-next';

const props = defineProps<{ 
    workOrder: any,
    totalParts: number 
}>();

// --- Invoice Form Logic ---
const form = useForm({
    work_order_id: props.workOrder.id,
    labor_fee: 0,      // Ongkos Kerja (Manual Input)
    discount: 0,       // Potongan Harga
    payment_method: 'Cash', // Cash, Transfer, QRIS
    notes: ''
});

const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

// --- Grand Total Calculation ---
const grandTotal = computed(() => {
    return (props.totalParts + form.labor_fee) - form.discount;
});

const submitInvoice = () => {
    form.post(route('invoices.store'), {
        onSuccess: () => {
            // After saving, we'll want to print the final receipt
            window.print(); 
        }
    });
};
</script>

<template>
    <Head title="Buat Invoice" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold uppercase">Pembayaran Work Order #{{ workOrder.id }}</h2>
        </template>

        <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 no-print">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 space-y-6">
                    <Card>
                        <CardHeader class="bg-slate-50 border-b">
                            <CardTitle class="text-sm uppercase tracking-widest text-slate-500">Rincian Perbaikan</CardTitle>
                        </CardHeader>
                        <CardContent class="p-0">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 border-b">
                                    <tr>
                                        <th class="p-4 text-left">Item / Jasa</th>
                                        <th class="p-4 text-center">Qty</th>
                                        <th class="p-4 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="item in workOrder.items" :key="'task-'+item.id">
                                        <td class="p-4">JASA: {{ item.task_name }}</td>
                                        <td class="p-4 text-center">1</td>
                                        <td class="p-4 text-right text-slate-400 italic">Dihitung di Ongkos Kerja</td>
                                    </tr>
                                    <tr v-for="part in workOrder.parts" :key="'part-'+part.id" class="bg-green-50/20">
                                        <td class="p-4 font-medium">{{ part.part.name }}</td>
                                        <td class="p-4 text-center">{{ part.qty }}</td>
                                        <td class="p-4 text-right font-mono">{{ formatIDR(part.price_sold * part.qty) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </CardContent>
                    </Card>
                </div>

                <div class="space-y-6">
                    <Card class="border-2 border-slate-900 shadow-xl">
                        <CardHeader class="bg-slate-900 text-white">
                            <CardTitle class="flex items-center gap-2"><Receipt class="w-5 h-5" /> TOTAL BILL</CardTitle>
                        </CardHeader>
                        <CardContent class="pt-6 space-y-4">
                            <div class="flex justify-between text-sm">
                                <span>Total Sparepart:</span>
                                <span class="font-bold">{{ formatIDR(totalParts) }}</span>
                            </div>

                            <div class="space-y-2 pt-2 border-t">
                                <label class="text-xs font-black uppercase text-slate-500">Ongkos Kerja (Jasa)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-slate-400">Rp</span>
                                    <Input v-model.number="form.labor_fee" type="number" class="pl-10 text-xl font-bold h-12" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase text-slate-500">Diskon / Potongan</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-slate-400">Rp</span>
                                    <Input v-model.number="form.discount" type="number" class="pl-10 text-xl font-bold h-12 text-red-600" />
                                </div>
                            </div>

                            <div class="bg-slate-100 p-4 rounded-lg mt-4 text-center">
                                <p class="text-[10px] uppercase font-bold text-slate-500">Total yang Harus Dibayar</p>
                                <p class="text-4xl font-black text-slate-900 tracking-tighter">{{ formatIDR(grandTotal) }}</p>
                            </div>

                            <div class="grid grid-cols-3 gap-2 py-4">
                                <Button 
                                    v-for="method in ['Cash', 'Transfer', 'QRIS']" 
                                    :key="method"
                                    type="button"
                                    variant="outline"
                                    :class="form.payment_method === method ? 'bg-slate-900 text-white' : ''"
                                    @click="form.payment_method = method"
                                >{{ method }}</Button>
                            </div>

                            <Button 
                                @click="submitInvoice"
                                :disabled="form.processing"
                                class="w-full h-16 bg-blue-600 hover:bg-blue-700 text-xl font-black gap-2 uppercase shadow-lg shadow-blue-200"
                            >
                                <CreditCard class="w-6 h-6" /> Bayar & Cetak
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>