<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Receipt, CreditCard, Plus, Trash2, Package, Wrench, Save } from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';

const props = defineProps<{ 
    workOrder: any,
    formattedParts: any[], 
    shop: any,
    totalParts: number,
    existingInvoice: any,
    savedItems: any[]
}>();

const invoiceItems = ref([]);
const isSaved = ref(false);
const page = usePage<any>();

    // Computed properties for the two tables
const partsItems = computed(() => invoiceItems.value.filter(i => i.type === 'part'));
const servicesItems = computed(() => invoiceItems.value.filter(i => i.type === 'service' || i.type === 'manual'));

const form = useForm({
    work_order_id: props.workOrder.id,
    items: [], 
    discount: 0, // This is the Global/Extra discount
    payment_method: 'Cash', 
    status: 'unpaid',  
    due_date: '',    
    amount_paid: 0, 
    notes: ''
});

// Update sync logic to check for existing data first
const syncItems = () => {
    // IF there is an existing invoice, load its data
    if (props.savedItems && props.savedItems.length > 0) {
        invoiceItems.value = props.savedItems;
        
        // Also sync the form values if an invoice exists
        if (props.existingInvoice) {
            form.discount = props.existingInvoice.discount_total || 0;
            form.payment_method = props.existingInvoice.payment_method || 'Cash';
            form.status = props.existingInvoice.status;
            form.due_date = props.existingInvoice.due_date || '';
            form.amount_paid = props.existingInvoice.amount_paid || 0;
        }
        return;
    }

    // OTHERWISE (First time opening), use the original logic
    const parts = (props.formattedParts || []).map(p => ({
        name: p.name,
        qty: p.qty,
        price: p.price,
        type: 'part',
        discount: 0
    }));

    const tasks = (props.workOrder.items || []).map(i => ({
        name: `JASA: ${i.task_name}`,
        qty: 1,
        price: 0,
        type: 'service',
        discount: 0
    }));

    invoiceItems.value = [...parts, ...tasks];
};

syncItems();

const addManualItem = (type = 'manual') => {
    invoiceItems.value.push({
        name: '', 
        qty: 1,
        price: 0,
        type: type,
        discount: 0
    });
};

const removeItem = (itemRef: any) => {
    const index = invoiceItems.value.indexOf(itemRef);
    if (index > -1) invoiceItems.value.splice(index, 1);
};

const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(val);

// Subtotal includes item-level discounts
const subtotal = computed(() => {
    return invoiceItems.value.reduce((acc, item) => acc + (item.price * item.qty) - (item.discount || 0), 0);
});

const grandTotal = computed(() => {
    return subtotal.value - form.discount;
});

// Original Print Sequence
const handlePrintSequence = () => {
    setTimeout(() => {
        window.print();
        setTimeout(() => {
            window.location.href = route('dashboard');
        }, 1000);
    }, 500);
};

// Process Transaction (Save + Action)
const processTransaction = (action: 'print' | 'finish' | 'save') => {
    if (isSaved.value) {
        if (action === 'print') window.print();
        if (action === 'finish') window.location.href = route('dashboard');
        return;
    }

    if (form.processing) return;
    form.items = invoiceItems.value;
    
    form.post('/invoices', {
        preserveScroll: true,
        onSuccess: () => {
            if (action === 'save') {
                // Just stay here, don't lock isSaved so they can save again
                return; 
            }
            isSaved.value = true;
            if (action === 'print') {
                setTimeout(() => { window.print(); }, 300);
            } else if (action === 'finish') {
                window.location.href = route('dashboard');
            }
        }
    });
};

const saveAsDraft = () => {
    // We set status to draft and call the standard process
    form.status = 'draft';
    form.payment_method = 'None';
    processTransaction('save'); // We reuse your existing transaction logic
};

// Listen for flash print trigger
watch(() => page.props.flash?.print_id, (newId) => {
    if (newId) handlePrintSequence();
}, { immediate: true });
</script>

<template>
    <div class="print-layout p-4 text-black font-mono bg-white">
        <div class="text-center mb-4">
            <h1 class="text-xl font-bold uppercase">{{ shop?.shop_name || 'BENGKEL' }}</h1>
            <p class="text-xs">{{ shop?.shop_address }}</p>
            <hr class="my-2 border-black">
        </div>
        <div class="flex justify-between text-xs mb-2">
            <span>Nota: #{{ workOrder.id }}</span>
            <span>Tgl: {{ new Date().toLocaleDateString('id-ID') }}</span>
        </div>
        <div class="text-[10px] mb-4 border-b border-black pb-2">
            <div class="flex justify-between">
                <span>PELANGGAN: {{ workOrder.vehicle?.customer?.name || 'CASH' }}</span>
                <span>PLAT: {{ workOrder.vehicle?.plate_number }}</span>
            </div>
            <div>MOBIL: {{ workOrder.vehicle?.brand }} {{ workOrder.vehicle?.model }}</div>
        </div>
        <table class="w-full text-xs mb-4">
            <tr v-for="item in invoiceItems" :key="item.name">
                <td class="py-1">{{ item.name }} (x{{ item.qty }})</td>
                <td class="text-right">{{ formatIDR((item.price * item.qty) - (item.discount || 0)) }}</td>
            </tr>
            <tr v-if="form.discount > 0">
                <td class="py-1 italic">Potongan Tambahan</td>
                <td class="text-right">-{{ formatIDR(form.discount) }}</td>
            </tr>
        </table>
        <div class="flex justify-between font-bold border-t border-black pt-2">
            <span>TOTAL:</span>
            <span>{{ formatIDR(grandTotal) }}</span>
        </div>
    </div>
    
    <div class="no-print">
    <Head title="Buat Invoice" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold uppercase">Pembayaran Work Order #{{ workOrder.id }}</h2>
        </template>

        <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-white p-5 rounded-xl border shadow-sm mb-6">
                <div class="flex items-start gap-3">
                    <div class="bg-slate-100 p-2 rounded-lg"><User class="w-5 h-5 text-slate-600" /></div>
                    <div>
                        <p class="text-[10px] uppercase font-black text-slate-400 tracking-wider">Data Pelanggan</p>
                        <p class="font-bold text-slate-900 text-base uppercase leading-tight">{{ workOrder.vehicle?.customer?.name || 'Umum / Cash' }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ workOrder.vehicle?.customer?.phone_1 || '-' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 border-l pl-6 border-slate-100">
                    <div class="bg-blue-50 p-2 rounded-lg"><Car class="w-5 h-5 text-blue-600" /></div>
                    <div>
                        <p class="text-[10px] uppercase font-black text-slate-400 tracking-wider">Kendaraan</p>
                        <span class="font-mono font-black text-blue-700 text-base">{{ workOrder.vehicle?.plate_number }}</span>
                        <p class="text-xs text-slate-600 font-medium">{{ workOrder.vehicle?.brand }} {{ workOrder.vehicle?.model }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 border-l pl-6 border-slate-100">
                    <div class="bg-orange-50 p-2 rounded-lg"><ClipboardList class="w-5 h-5 text-orange-600" /></div>
                    <div>
                        <p class="text-[10px] uppercase font-black text-slate-400 tracking-wider">Info Masuk</p>
                        <p class="text-sm font-bold text-slate-800">Odo: {{ workOrder.odometer || '-' }} KM</p>
                        <p class="text-[11px] text-slate-500 italic">Pembawa: {{ workOrder.driver_name || 'Owner' }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <Card class="border-green-200">
                        <CardHeader class="bg-green-50 border-b py-3 flex justify-between items-center">
                            <CardTitle class="text-xs font-black text-green-700 uppercase flex items-center gap-2"><Package class="w-4 h-4" /> Spareparts</CardTitle>
                            <Button v-if="form.status === 'unpaid'" variant="outline" size="sm" @click="addManualItem('part')" class="h-7 text-[10px] border-green-300 text-green-700">+ Tambah Part</Button>
                        </CardHeader>
                        <CardContent class="p-0">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 border-b text-[10px] uppercase text-slate-500">
                                    <tr>
                                        <th class="p-4 text-left">Item</th>
                                        <th class="p-4 text-center w-20">Qty</th>
                                        <th class="p-4 text-right w-32">Harga</th>
                                        <th class="p-4 text-right w-32">Diskon Item</th>
                                        <th class="p-4 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="(item, index) in partsItems" :key="'p'+index">
                                        <td class="p-2"><Input v-model="item.name" class="h-9 border-transparent focus:border-green-500" /></td>
                                        <td class="p-2"><Input v-model.number="item.qty" type="number" class="h-9 text-center border-transparent" /></td>
                                        <td class="p-2"><Input v-model.number="item.price" type="number" class="h-9 text-right font-bold border-transparent" /></td>
                                        <td class="p-2"><Input v-model.number="item.discount" type="number" class="h-9 text-right text-red-600 border-transparent bg-red-50/20" /></td>
                                        <td class="p-2 text-center"><Button v-if="form.status === 'unpaid'" variant="ghost" size="icon" @click="removeItem(item)" class="text-slate-300 hover:text-red-500"><Trash2 class="w-4 h-4" /></Button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </CardContent>
                    </Card>

                    <Card class="border-blue-200">
                        <CardHeader class="bg-blue-50 border-b py-3 flex justify-between items-center">
                            <CardTitle class="text-xs font-black text-blue-700 uppercase flex items-center gap-2"><Wrench class="w-4 h-4" /> Jasa / Service</CardTitle>
                            <Button v-if="form.status === 'unpaid'" variant="outline" size="sm" @click="addManualItem('service')" class="h-7 text-[10px] border-blue-300 text-blue-700">+ Tambah Jasa</Button>
                        </CardHeader>
                        <CardContent class="p-0">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 border-b text-[10px] uppercase text-slate-500">
                                    <tr>
                                        <th class="p-4 text-left">Nama Jasa</th>
                                        <th class="p-4 text-right w-32">Harga</th>
                                        <th class="p-4 text-right w-32">Diskon Jasa</th>
                                        <th class="p-4 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="(item, index) in servicesItems" :key="'s'+index">
                                        <td class="p-2"><Input v-model="item.name" class="h-9 border-transparent focus:border-blue-500" /></td>
                                        <td class="p-2"><Input v-model.number="item.price" type="number" class="h-9 text-right font-bold border-transparent" /></td>
                                        <td class="p-2"><Input v-model.number="item.discount" type="number" class="h-9 text-right text-red-600 border-transparent bg-red-50/20" /></td>
                                        <td class="p-2 text-center"><Button v-if="form.status === 'unpaid'" variant="ghost" size="icon" @click="removeItem(item)" class="text-slate-300 hover:text-red-500"><Trash2 class="w-4 h-4" /></Button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </CardContent>
                    </Card>
                </div>

                <div class="space-y-6">
                    <Card class="border-2 border-slate-900 shadow-xl sticky top-6">
                        <CardHeader class="bg-slate-900 text-white">
                            <CardTitle class="flex items-center gap-2"><Receipt class="w-5 h-5" /> RINGKASAN TAGIHAN</CardTitle>
                        </CardHeader>
                        <CardContent class="pt-6 space-y-4">
                            <div class="flex justify-between text-sm">
                                <span>Subtotal (Net):</span>
                                <span class="font-bold">{{ formatIDR(subtotal) }}</span>
                            </div>
                            <div class="space-y-2 pt-2 border-t">
                                <label class="text-xs font-black uppercase text-slate-500">Potongan Tambahan (Global)</label>
                                <Input v-model.number="form.discount" type="number" class="text-xl font-bold h-12 text-red-600" />
                            </div>
                            <div class="bg-slate-100 p-4 rounded-lg text-center border-2 border-slate-200">
                                <p class="text-[10px] uppercase font-bold text-slate-500">Total Akhir</p>
                                <p class="text-4xl font-black text-slate-900 leading-none">{{ formatIDR(grandTotal) }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-2 pt-4">
                                <Button variant="outline" :class="form.status === 'paid' ? 'bg-green-600 text-white border-green-600' : ''" @click="form.status = 'paid'; form.payment_method = 'Cash'">Lunas</Button>
                                <Button variant="outline" :class="form.status === 'unpaid' ? 'bg-red-600 text-white border-red-600' : ''" @click="form.status = 'unpaid'; form.payment_method = 'Receivable'">Hutang</Button>
                            </div>
                            <div v-if="form.status === 'unpaid'" class="space-y-4 py-4 mt-2 border-t border-dashed animate-in fade-in slide-in-from-top-2">
                                <div class="space-y-2">
                                    <label class="text-xs font-black uppercase text-slate-500">DP / Uang Muka (Opsional)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-slate-400">Rp</span>
                                        <Input 
                                            type="number" 
                                            v-model.number="form.amount_paid" 
                                            class="pl-10 font-bold border-red-100 focus:border-red-500" 
                                        />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-black uppercase text-red-600">Jatuh Tempo (Due Date)</label>
                                    <Input type="date" v-model="form.due_date" class="border-red-200 focus:border-red-500" />
                                </div>

                                <div class="flex justify-between items-center p-3 bg-red-50 text-red-700 rounded-lg border border-red-100">
                                    <span class="text-[10px] font-black uppercase tracking-wider">Sisa Hutang</span>
                                    <span class="font-mono font-bold text-lg">
                                        {{ formatIDR(grandTotal - (form.amount_paid || 0)) }}
                                    </span>
                                </div>
                            </div>
                            <Button v-if="form.status === 'unpaid'"
                                @click="saveAsDraft" 
                                variant="outline" 
                                class="w-full h-12 border-slate-300 text-slate-600 gap-2"
                                :disabled="form.processing">
                                <Save class="w-4 h-4" /> Simpan Draft
                            </Button>
                            <Button @click="processTransaction('print')" :disabled="form.processing" variant="outline" class="w-full h-14 border-blue-600 text-blue-600 font-bold uppercase mt-4"><Receipt class="w-4 h-4" /> Cetak Nota</Button>
                            <Button @click="processTransaction('finish')" v-if="form.status === 'unpaid'" :disabled="form.processing" class="w-full h-14 bg-green-600 hover:bg-green-700 font-bold uppercase"><CreditCard class="w-4 h-4" />Selesai</Button>
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
    .no-print, nav, header, aside { display: none !important; }
    .print-layout { display: block !important; opacity: 1 !important; position: absolute !important; top: 0 !important; width: 100% !important; z-index: 9999 !important; }
    body, html, #app { background-color: white !important; }
}
</style>