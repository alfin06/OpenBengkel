<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Receipt, CreditCard, Plus, Trash2, Package, Wrench, Save, User, Car, ClipboardList, AlertCircle, ExternalLink } from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';

const props = defineProps<{ 
    workOrder: any,
    internalParts: any[], // New: Locked inventory items
    initialLainLain: any[], // New: Editable services/manual items
    shop: any,
    existingInvoice: any,
}>();

// local ref for the editable "Lain-lain" table only
const lainLainItems = ref([]);
const isSaved = ref(false);
const page = usePage<any>();

const form = useForm({
    work_order_id: props.workOrder.id,
    items: [], 
    discount: 0,
    payment_method: 'Cash', 
    status: 'paid',  
    due_date: '',    
    amount_paid: 0, 
    notes: ''
});

// Sync logic: separates locked stock from editable billing
const syncData = () => {
    // Force the initial data into an array format
    const rawLainLain = props.initialLainLain || [];
    lainLainItems.value = Array.isArray(rawLainLain) ? rawLainLain : Object.values(rawLainLain);

    if (props.existingInvoice) {
        form.discount = props.existingInvoice.discount_total || 0;
        form.payment_method = props.existingInvoice.payment_method || 'Cash';
        form.status = props.existingInvoice.status;
        form.due_date = props.existingInvoice.due_date || '';
        form.amount_paid = props.existingInvoice.amount_paid || 0;
    }
};

syncData();

const addLainLain = (type = 'service') => {
    lainLainItems.value.push({
        name: '', 
        qty: 1,
        price: 0,
        type: type,
        discount: 0,
        is_internal: false
    });
};

const removeItem = (index: number) => {
    lainLainItems.value.splice(index, 1);
};

const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

// Combine both lists for calculations
const allItemsForTotal = computed(() => {
    const internal = Array.isArray(props.internalParts) ? props.internalParts : [];
    const lain = Array.isArray(lainLainItems.value) ? lainLainItems.value : [];
    return [...internal, ...lain];
});

const subtotal = computed(() => {
    // Safeguard for Internal Parts
    const internal = Array.isArray(props.internalParts) ? props.internalParts : [];
    const internalTotal = internal.reduce((acc, item) => {
        return acc + (Number(item.price) * Number(item.qty));
    }, 0);

    // Safeguard for Lain-lain (The part that caused the crash)
    const lainItems = Array.isArray(lainLainItems.value) ? lainLainItems.value : [];
    const lainLainTotal = lainItems.reduce((acc, item) => {
        const price = Number(item.price) || 0;
        const qty = Number(item.qty) || 0;
        const discount = Number(item.discount) || 0;
        return acc + (price * qty) - discount;
    }, 0);

    return internalTotal + lainLainTotal;
});

const grandTotal = computed(() => subtotal.value - form.discount);

const processTransaction = (action: 'print' | 'finish' | 'save') => {
    if (form.processing) return;

    // Merge internal parts and lain-lain before sending to server
    form.items = [...props.internalParts, ...lainLainItems.value];
    
    form.post('/invoices', {
        preserveScroll: true,
        onSuccess: () => {
            if (action === 'save') return;
            if (action === 'print') setTimeout(() => { window.print(); }, 300);
            if (action === 'finish') window.location.href = route('dashboard');
        }
    });
};

const saveAsDraft = () => {
    form.status = 'draft';
    processTransaction('save');
};
</script>

<template>
    <div class="print-layout p-4 text-black font-mono bg-white">
        <div class="text-center mb-4">
            <h1 class="text-xl font-bold uppercase">{{ shop?.shop_name }}</h1>
            <p class="text-xs">{{ shop?.shop_address }}</p>
            <hr class="my-2 border-black">
        </div>
        <table class="w-full text-xs">
            <tr v-for="item in allItemsForTotal" :key="item.name" class="border-b border-dotted border-black">
                <td class="py-2">
                    <div>{{ item.name }}</div>
                    <div class="text-[9px] opacity-70">{{ item.qty }} x {{ formatIDR(item.price) }}</div>
                </td>
                <td class="text-right align-bottom font-bold">
                    {{ formatIDR((item.price * item.qty) - (item.discount || 0)) }}
                </td>
            </tr>
        </table>
        <div class="flex justify-between font-bold border-t border-black pt-2 mt-2">
            <span>TOTAL:</span>
            <span>{{ formatIDR(grandTotal) }}</span>
        </div>
    </div>
    
    <div class="no-print bg-slate-50 min-h-screen">
        <Head title="Buat Invoice" />
        <AuthenticatedLayout>
            <template #header>
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold uppercase">Billing Work Order #{{ workOrder.id }}</h2>
                </div>
            </template>

            <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-white p-5 rounded-xl border shadow-sm mb-6">
                    <div class="flex items-start gap-3">
                        <User class="w-5 h-5 text-slate-400" />
                        <div>
                            <p class="text-[10px] uppercase font-black text-slate-400">Pelanggan</p>
                            <p class="font-bold text-slate-900 uppercase">{{ workOrder.vehicle?.customer?.name }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 border-l pl-6 border-slate-100">
                        <Car class="w-5 h-5 text-blue-500" />
                        <div>
                            <p class="text-[10px] uppercase font-black text-slate-400">Kendaraan</p>
                            <p class="font-mono font-black text-blue-700">{{ workOrder.vehicle?.plate_number }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 border-l pl-6 border-slate-100">
                        <ClipboardList class="w-5 h-5 text-orange-500" />
                        <div>
                            <p class="text-[10px] uppercase font-black text-slate-400">Status WO</p>
                            <p class="text-sm font-bold">{{ workOrder.status }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">
                        
                        <Card class="border-slate-200 shadow-sm opacity-90 overflow-hidden">
                            <CardHeader class="bg-slate-100 py-3 flex flex-row items-center justify-between">
                                <CardTitle class="text-[11px] font-black text-slate-600 uppercase flex items-center gap-2">
                                    <Package class="w-4 h-4" /> Spareparts (Dari Stok Internal)
                                </CardTitle>
                                <a :href="route('work-orders.show', workOrder.id)" class="text-[10px] font-bold text-blue-600 flex items-center gap-1 hover:underline">
                                    <ExternalLink class="w-3 h-3" /> Edit di Work Order
                                </a>
                            </CardHeader>
                            <CardContent class="p-0">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 border-b text-[10px] uppercase text-slate-400">
                                        <tr>
                                            <th class="p-4 text-left">Nama Barang</th>
                                            <th class="p-4 text-center w-20">Qty</th>
                                            <th class="p-4 text-right w-32">Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr v-for="item in internalParts" :key="item.part_id" class="bg-slate-50/30">
                                            <td class="p-4">
                                                <div class="font-medium text-slate-700">{{ item.name }}</div>
                                                <div class="text-[10px] text-slate-400">Harga Satuan: {{ formatIDR(item.price) }}</div>
                                            </td>
                                            <td class="p-4 text-center font-bold">{{ item.qty }}</td>
                                            <td class="p-4 text-right font-mono font-bold text-slate-900">
                                                {{ formatIDR(item.price * item.qty) }}
                                            </td>
                                        </tr>
                                        <tr v-if="internalParts.length === 0">
                                            <td colspan="3" class="p-4 text-center text-xs text-slate-400 italic">Tidak ada sparepart internal.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>

                        <Card class="border-blue-200 shadow-md">
                            <CardHeader class="bg-blue-600 py-3 flex flex-row items-center justify-between">
                                <CardTitle class="text-[11px] font-black text-white uppercase flex items-center gap-2">
                                    <Wrench class="w-4 h-4" /> Jasa & Lain-lain (Bisa Diedit)
                                </CardTitle>
                                <Button variant="secondary" size="sm" @click="addLainLain('service')" class="h-7 text-[10px] font-bold">+ Tambah Item</Button>
                            </CardHeader>
                            <CardContent class="p-0">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 border-b text-[10px] uppercase text-slate-500">
                                        <tr>
                                            <th class="p-4 text-left">Nama Item</th>
                                            <th class="p-4 text-center w-20">Qty</th>
                                            <th class="p-4 text-right w-32">Harga Satuan</th>
                                            <th class="p-4 text-right w-32">Diskon Item</th>
                                            <th class="p-4 w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        <tr v-for="(item, index) in lainLainItems" :key="index">
                                            <td class="p-2">
                                                <Input v-model="item.name" class="h-9" />
                                            </td>
                                            <td class="p-2">
                                                <Input v-model.number="item.qty" type="number" class="h-9 text-center" />
                                            </td>
                                            <td class="p-2">
                                                <div class="flex flex-col gap-1">
                                                    <Input v-model.number="item.price" type="number" class="h-9 text-right font-bold" />
                                                    <span class="text-[9px] text-right text-slate-400">Total: {{ formatIDR(item.price * item.qty) }}</span>
                                                </div>
                                            </td>
                                            <td class="p-2 text-right">
                                                <Input v-model.number="item.discount" type="number" class="h-9 text-right text-red-600 bg-red-50/30" />
                                            </td>
                                            <td class="p-2 text-center">
                                                <Button variant="ghost" size="icon" @click="removeItem(index)" class="text-slate-300 hover:text-red-500">
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
                                <CardTitle class="flex items-center gap-2 text-sm uppercase font-black tracking-widest"><Receipt class="w-5 h-5 text-blue-400" /> Ringkasan</CardTitle>
                            </CardHeader>
                            <CardContent class="pt-6 space-y-4">
                                <div class="flex justify-between text-sm">
                                    <span>Subtotal:</span>
                                    <span class="font-bold text-slate-700">{{ formatIDR(subtotal) }}</span>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black uppercase text-slate-400">Diskon Global</label>
                                    <Input v-model.number="form.discount" type="number" class="text-xl font-bold h-12 border-slate-200 text-red-600" />
                                </div>
                                <div class="bg-blue-50 p-4 rounded-lg text-center border-2 border-blue-100">
                                    <p class="text-[10px] uppercase font-black text-blue-400">Total Akhir</p>
                                    <p class="text-4xl font-black text-blue-900">{{ formatIDR(grandTotal) }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <Button variant="outline" :class="form.status === 'paid' ? 'bg-green-600 text-white border-green-600' : ''" @click="form.status = 'paid'; form.payment_method = 'Cash'">LUNAS</Button>
                                    <Button variant="outline" :class="form.status === 'unpaid' ? 'bg-red-600 text-white border-red-600' : ''" @click="form.status = 'unpaid'; form.payment_method = 'Receivable'">HUTANG</Button>
                                </div>

                                <div v-if="form.status === 'unpaid'" class="space-y-3 p-3 bg-red-50 rounded-lg border border-red-100 animate-in fade-in slide-in-from-top-2">
                                    <label class="text-[10px] font-black uppercase text-red-600">Jatuh Tempo</label>
                                    <Input type="date" v-model="form.due_date" class="border-red-200" />
                                </div>

                                <hr />

                                <Button @click="saveAsDraft" variant="ghost" class="w-full text-slate-500 hover:text-blue-600 gap-2 h-10 border border-dashed">
                                    <Save class="w-4 h-4" /> Simpan Draft
                                </Button>
                                
                                <div class="grid grid-cols-1 gap-2 mt-4">
                                    <Button @click="processTransaction('print')" class="h-14 bg-blue-600 hover:bg-blue-700 font-black uppercase tracking-widest gap-2">
                                        <Receipt class="w-5 h-5" /> Cetak & Simpan
                                    </Button>
                                    <Button @click="processTransaction('finish')" variant="outline" class="h-12 border-slate-200 font-bold uppercase text-xs">
                                        Selesai Tanpa Cetak
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    </div>
</template>

<style>
@media screen { .print-layout { position: absolute; opacity: 0; pointer-events: none; z-index: -100; } }
@media print { 
    .no-print { display: none !important; }
    .print-layout { display: block !important; opacity: 1 !important; position: absolute !important; top: 0 !important; width: 100% !important; z-index: 9999 !important; }
}
</style>