<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { CheckCircle, AlertCircle } from 'lucide-vue-next';

const props = defineProps<{
    invoices: any[]
}>();

const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

const form = useForm({
    payment_method: 'Transfer'
});

const settleInvoice = (invoiceId: number) => {
    if (confirm('Are you sure you want to mark this invoice as settled?')) {
        form.put(route('invoices.settle', invoiceId), {
            preserveScroll: true
        });
    }
};

const isOverdue = (dateString: string) => {
    if (!dateString) return false;
    return new Date(dateString) < new Date();
};
</script>

<template>
    <Head title="Accounts Receivable" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold uppercase text-red-600">Accounts Receivable (Unpaid)</h2>
        </template>

        <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <Card>
                <CardHeader>
                    <CardTitle>Active Receivables</CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 border-b">
                            <tr>
                                <th class="p-4">Invoice / WO</th>
                                <th class="p-4">Customer / Company</th>
                                <th class="p-4">Due Date</th>
                                <th class="p-4 text-right">Remaining Balance</th>
                                <th class="p-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-if="invoices.length === 0">
                                <td colspan="5" class="p-8 text-center text-slate-500 italic">No active receivables. All accounts are settled!</td>
                            </tr>
                            <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-slate-50 transition-colors">
                                <td class="p-4 font-bold">INV-#{{ invoice.id }}</td>
                                <td class="p-4">
                                    <div class="font-bold">{{ invoice.work_order?.vehicle?.customer?.name || 'Customer' }}</div>
                                    <div class="text-xs text-slate-500">{{ invoice.work_order?.vehicle?.plate_number }}</div>
                                </td>
                                <td class="p-4">
                                    <span v-if="invoice.due_date" 
                                          :class="isOverdue(invoice.due_date) ? 'text-red-600 font-bold flex items-center gap-1' : 'text-slate-600'">
                                        <AlertCircle v-if="isOverdue(invoice.due_date)" class="w-4 h-4" />
                                        {{ new Date(invoice.due_date).toLocaleDateString('id-ID') }}
                                    </span>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="font-mono font-bold text-lg text-red-600">
                                        {{ formatIDR(invoice.grand_total - invoice.amount_paid) }}
                                    </div>
                                    
                                    <div v-if="invoice.amount_paid > 0" class="text-xs text-slate-500 mt-1">
                                        DP: {{ formatIDR(invoice.amount_paid) }}
                                    </div>
                                    <div v-else class="text-[10px] uppercase font-bold text-slate-400 mt-1">
                                        No DP
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <Button 
                                        @click="settleInvoice(invoice.id)"
                                        :disabled="form.processing"
                                        class="bg-green-600 hover:bg-green-700 text-white gap-2">
                                        <CheckCircle class="w-4 h-4" /> Settle
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