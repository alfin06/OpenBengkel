<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Receipt, CreditCard, Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps<{ 
    workOrder: any,
    formattedParts: any[], 
    totalParts: number 
}>();

const invoiceItems = ref([]);

// We use a function so we can call it whenever props change
const syncItems = () => {
    const parts = props.formattedParts || [];
    const tasks = props.workOrder.items || [];

    invoiceItems.value = [
        ...parts,
        ...tasks.map(i => ({
            name: `JASA: ${i.task_name}`,
            qty: 1,
            price: 0,
            type: 'jasa'
        }))
    ];
};

// Run it immediately
syncItems();

// Debugging: This will tell us if the data arrived as soon as the page loads
onMounted(() => {
    console.log("Full Props Object:", props);
    console.log("Formatted Parts:", props.formattedParts);
});

// const invoiceItems = ref([
//     ...(props.formattedParts || []), 
//     ...props.workOrder.items.map(i => ({
//         name: `JASA: ${i.task_name}`,
//         qty: 1,
//         price: 0,
//         type: 'jasa'
//     }))
// ]);

console.log("Parts received from server:", props.formattedParts);

// 2. Form Logic (We now pass 'items' to the backend)
const form = useForm({
    work_order_id: props.workOrder.id,
    items: [], // This will be populated on submit
    discount: 0,
    payment_method: 'Cash',
    notes: ''
});

// 3. Manual Item Management
const addManualItem = () => {
    invoiceItems.value.push({
        name: '', 
        qty: 1,
        price: 0,
        type: 'manual'
    });
};

const removeItem = (index: number) => {
    invoiceItems.value.splice(index, 1);
};

// 4. Calculations
const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

const subtotal = computed(() => {
    return invoiceItems.value.reduce((acc, item) => acc + (item.price * item.qty), 0);
});

const grandTotal = computed(() => {
    return subtotal.value - form.discount;
});

// 5. Submit
const submitInvoice = () => {
    // We attach the edited items to the form before sending
    form.items = invoiceItems.value;
    
    form.post(route('invoices.store'), {
        onSuccess: () => {
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
                        <CardHeader class="bg-slate-50 border-b flex flex-row items-center justify-between">
                            <CardTitle class="text-sm uppercase tracking-widest text-slate-500">Rincian Perbaikan</CardTitle>
                            <Button variant="outline" size="sm" @click="addManualItem" class="gap-1 text-xs">
                                <Plus class="w-3 h-3" /> Tambah Item Manual
                            </Button>
                        </CardHeader>
                        <CardContent class="p-0">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 border-b">
                                    <tr>
                                        <th class="p-4 text-left">Item / Jasa</th>
                                        <th class="p-4 text-center w-24">Qty</th>
                                        <th class="p-4 text-right w-48">Harga Satuan</th>
                                        <th class="p-4 w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="(item, index) in invoiceItems" :key="index" 
                                        :class="item.type === 'part' ? 'bg-green-50/10' : ''">
                                        <td class="p-2">
                                            <Input v-model="item.name" class="border-transparent focus:border-blue-500 font-medium" />
                                        </td>
                                        <td class="p-2">
                                            <Input v-model.number="item.qty" type="number" class="text-center border-transparent focus:border-blue-500" />
                                        </td>
                                        <td class="p-2">
                                            <div class="relative">
                                                <span class="absolute left-3 top-2 text-slate-400 text-xs">Rp</span>
                                                <Input v-model.number="item.price" type="number" class="text-right pl-8 border-transparent focus:border-blue-500 font-mono font-bold" />
                                            </div>
                                        </td>
                                        <td class="p-2 text-center">
                                            <Button variant="ghost" size="icon" @click="removeItem(index)" class="text-red-400 hover:text-red-600">
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </CardContent>
                    </Card>
                </div>

                <div class="space-y-6">
                    <Card class="border-2 border-slate-900 shadow-xl sticky top-6">
                        <CardHeader class="bg-slate-900 text-white">
                            <CardTitle class="flex items-center gap-2"><Receipt class="w-5 h-5" /> TOTAL BILL</CardTitle>
                        </CardHeader>
                        <CardContent class="pt-6 space-y-4">
                            <div class="flex justify-between text-sm">
                                <span>Subtotal:</span>
                                <span class="font-bold">{{ formatIDR(subtotal) }}</span>
                            </div>

                            <div class="space-y-2 pt-2 border-t">
                                <label class="text-xs font-black uppercase text-slate-500">Diskon / Potongan</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-slate-400">Rp</span>
                                    <Input v-model.number="form.discount" type="number" class="pl-10 text-xl font-bold h-12 text-red-600" />
                                </div>
                            </div>

                            <div class="bg-slate-100 p-4 rounded-lg mt-4 text-center border-2 border-slate-200">
                                <p class="text-[10px] uppercase font-bold text-slate-500">Total yang Harus Dibayar</p>
                                <p class="text-4xl font-black text-slate-900 tracking-tighter">{{ formatIDR(grandTotal) }}</p>
                            </div>

                            <div class="grid grid-cols-3 gap-2 py-4">
                                <Button 
                                    v-for="method in ['Cash', 'Transfer', 'QRIS']" 
                                    :key="method"
                                    variant="outline"
                                    :class="form.payment_method === method ? 'bg-slate-900 text-white border-slate-900' : ''"
                                    @click="form.payment_method = method"
                                >{{ method }}</Button>
                            </div>

                            <Button 
                                @click="submitInvoice"
                                :disabled="form.processing"
                                class="w-full h-16 bg-blue-600 hover:bg-blue-700 text-xl font-black gap-2 uppercase shadow-lg"
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