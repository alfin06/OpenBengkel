<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { CheckCircle, AlertCircle, Banknote, CreditCard } from 'lucide-vue-next';

const props = defineProps<{
    invoices: any[]
}>();

const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

const form = useForm({
    payment_method: 'Transfer'
});

const settleInvoice = (invoice: any) => { // Accept the full object
    if (confirm(`Yakin tandai INV-#${invoice.id} sebagai LUNAS menggunakan ${form.payment_method}?`)) {
        // By passing the 'invoice' object, Ziggy/Inertia will automatically 
        // use the UUID because of your Model's getRouteKeyName setting.
        form.put(route('invoices.settle', invoice), {
            preserveScroll: true,
            onSuccess: () => {
                // Flash messages are handled by Laravel, but you can add local logic here
            }
        });
    }
};

const isOverdue = (dateString: string) => {
    if (!dateString) return false;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return new Date(dateString) < today;
};
</script>

<template>
    <Head title="Piutang Pelanggan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold uppercase text-red-600 flex items-center gap-2">
                    <Banknote class="w-6 h-6" /> Piutang Pelanggan (Hutang)
                </h2>
                <div class="flex items-center gap-3 bg-white p-2 rounded-lg border shadow-sm">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Metode Pelunasan:</span>
                    <select 
                        v-model="form.payment_method"
                        class="h-8 text-xs font-bold border-slate-200 rounded-md focus:ring-blue-500 focus:border-blue-500 py-0 pl-2 pr-8 bg-slate-50"
                    >
                        <option value="Transfer">Transfer Bank</option>
                        <option value="Cash">Tunai / Cash</option>
                        <option value="QRIS">QRIS</option>
                    </select>
                </div>
            </div>
        </template>

        <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <Card class="border-red-100">
                <CardHeader class="bg-red-50/50 border-b">
                    <CardTitle class="text-sm text-red-800 uppercase tracking-widest font-black">Daftar Tagihan Aktif</CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 border-b text-[10px] uppercase font-bold text-slate-500">
                            <tr>
                                <th class="p-4">Invoice / Kendaraan</th>
                                <th class="p-4">Nama Pelanggan</th>
                                <th class="p-4">Jatuh Tempo</th>
                                <th class="p-4 text-right">Sisa Tagihan</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-if="invoices.length === 0">
                                <td colspan="5" class="p-12 text-center text-slate-500">
                                    <CheckCircle class="w-12 h-12 text-green-200 mx-auto mb-3" />
                                    <p class="font-bold">Semua piutang telah lunas!</p>
                                    <p class="text-xs text-slate-400">Tidak ada tagihan tertunda saat ini.</p>
                                </td>
                            </tr>
                            <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-slate-900">INV-#{{ invoice.id }}</div>
                                    <div class="font-mono text-[10px] text-blue-600 font-bold bg-blue-50 px-1.5 py-0.5 rounded inline-block mt-1">
                                        {{ invoice.work_order?.vehicle?.plate_number }}
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="font-bold uppercase text-slate-700">{{ invoice.work_order?.vehicle?.customer?.name }}</div>
                                    <div class="text-xs text-slate-400">{{ invoice.work_order?.vehicle?.customer?.phone_1 }}</div>
                                </td>
                                <td class="p-4">
                                    <div v-if="invoice.due_date" 
                                          :class="isOverdue(invoice.due_date) ? 'text-red-600 bg-red-50 p-2 rounded border border-red-100' : 'text-slate-600'">
                                        <div class="flex items-center gap-1 font-bold">
                                            <AlertCircle v-if="isOverdue(invoice.due_date)" class="w-3 h-3" />
                                            {{ new Date(invoice.due_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                        </div>
                                        <div v-if="isOverdue(invoice.due_date)" class="text-[9px] uppercase font-black">Terlambat!</div>
                                    </div>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="font-mono font-black text-lg text-red-600">
                                        {{ formatIDR(invoice.grand_total - invoice.amount_paid) }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        Total Nota: {{ formatIDR(invoice.grand_total) }}
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <Button 
                                        @click="settleInvoice(invoice)" 
                                        :disabled="form.processing"
                                        class="bg-green-600 hover:bg-green-700 text-white font-bold h-10 px-6 shadow-sm">
                                        <CheckCircle class="w-4 h-4 mr-2" /> Pelunasan
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>