<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Receipt, CreditCard, Plus, Trash2, Package, Wrench, Save, User, Car, ClipboardList, AlertCircle, ExternalLink, MessageCircle } from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';

const props = defineProps<{ 
    workOrder: any,
    internalParts: any[], // New: Locked inventory items
    initialLainLain: any[], // New: Editable services/manual items
    shop: any,
    existingInvoice: any,
}>();

const paymentOptions = [
    { label: 'CASH', value: 'Cash', charge: 0 },
    { label: 'KARTU KREDIT', value: 'Credit Card', charge: 0.03 },
    { label: 'TRANSFER (Anton R)', value: 'Transfer Anton', charge: 0 },
    { label: 'TRANSFER (Martino E)', value: 'Transfer Martino', charge: 0 },
    { label: 'QRIS BCA', value: 'QRIS BCA', charge: 0 },
    { label: 'QRIS MEGA', value: 'QRIS MEGA', charge: 0 },
    { label: 'DEBIT BCA', value: 'Debit BCA', charge: 0 },
    { label: 'DEBIT (Non BCA)', value: 'Debit Non-BCA', charge: 0.03 },
    
];

// local ref for the editable "Lain-lain" table only
const editableInternalParts = ref([]);
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


const subtotal = computed(() => {
    // 1. Calculate Internal Parts (Price * Qty - Discount)
    const internalTotal = editableInternalParts.value.reduce((acc, item) => {
        const price = Number(item.price) || 0;
        const qty = Number(item.qty) || 0;
        const discount = Number(item.discount) || 0;
        return acc + (price * qty) - discount;
    }, 0);

    // 2. Calculate Lain-lain (Price * Qty - Discount)
    const lainTotal = lainLainItems.value.reduce((acc, item) => {
        const price = Number(item.price) || 0;
        const qty = Number(item.qty) || 0;
        const discount = Number(item.discount) || 0;
        return acc + (price * qty) - discount;
    }, 0);

    return internalTotal + lainTotal;
});

const grandTotal = computed(() => subtotal.value - form.discount);


// Helper to update the 3% Biaya Admin
const updateAdminFee = () => {
    const selected = paymentOptions.find(opt => opt.value === form.payment_method);
    
    // Always remove existing fee first to recalculate correctly
    lainLainItems.value = lainLainItems.value.filter(item => item.name !== 'Biaya Admin (3%)');

    if (selected && selected.charge > 0) {
        const baseAmount = form.status === 'paid' ? subtotal.value : form.amount_paid;
        
        if (baseAmount > 0) {
            const fee = Math.round(baseAmount * selected.charge);
            lainLainItems.value.push({
                name: 'Biaya Admin (3%)',
                qty: 1,
                price: fee,
                type: 'fee',
                discount: 0,
                is_internal: false
            });
        }
    }
};

// Watch both payment method and DP amount
watch(() => [form.payment_method, form.status, form.amount_paid], () => {
    updateAdminFee();
}, { deep: true });

// Sync logic: separates locked stock from editable billing
const syncData = () => {
    // Sync internal parts
    editableInternalParts.value = props.internalParts.map(p => ({ ...p }));
    
    // Sync other items, but FILTER OUT the Biaya Admin from the incoming list
    // so our local watcher can recreate it based on the current form state
    const rawLainLain = props.initialLainLain || [];
    const incomingItems = Array.isArray(rawLainLain) ? rawLainLain : Object.values(rawLainLain);
    
    lainLainItems.value = incomingItems.filter(item => item.name !== 'Biaya Admin (3%)');

    if (props.existingInvoice) {
        form.discount = props.existingInvoice.discount_total || 0;
        form.payment_method = props.existingInvoice.payment_method || 'Cash';
        // Force the status to lowercase to match your button logic
        form.status = props.existingInvoice.status?.toLowerCase() || 'paid';
        form.due_date = props.existingInvoice.due_date || '';
        form.amount_paid = props.existingInvoice.amount_paid || 0;

        const statusFromServer = props.existingInvoice.status?.toLowerCase();
        
        if (statusFromServer === 'draft') {
            // If it's a draft, determine the button highlight based on payment method
            form.status = (form.payment_method === 'Receivable') ? 'unpaid' : 'paid';
        } else {
            form.status = statusFromServer || 'paid';
        }
    }

    // After syncing data, manually trigger the fee calculation
    updateAdminFee();
};

syncData();

const removeItem = (index: number) => {
    lainLainItems.value.splice(index, 1);
};

const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

// Combine both lists for calculations
const allItemsForTotal = computed(() => {
    return [...editableInternalParts.value, ...lainLainItems.value];
});

const processTransaction = (action: 'whatsapp' | 'finish' | 'save') => {
    if (form.processing) return;

    if (action !== 'save') {
        if (form.status == 'Draft') {
            toast.error("Silakan pilih status pembayaran (LUNAS atau HUTANG) terlebih dahulu!");
            return;
        }
    }

    if (action !== 'save') {
        // If it's a draft, we check if the staff has actually toggled a status.
        // If it's STILL 'draft' and they haven't touched 'Lunas' or 'Hutang', 
        // we default to 'paid'. 
        // BUT, if they clicked 'Hutang', the status will be 'unpaid', 
        // so this 'if' will be skipped, and 'unpaid' will be sent.
        if (form.status === 'draft') {
            form.status = 'paid'; 
        }
    }

    updateAdminFee();

    form.items = [...editableInternalParts.value, ...lainLainItems.value];
    
    // We add the action to the form data so the controller sees it
    form.transform((data) => ({
        ...data,
        action: action,
    })).post('/invoices', {
        preserveScroll: true,
        onSuccess: (page) => {
            // Check if the server sent back a WhatsApp URL
            syncData();
            const flash = usePage().props.flash;
            console.log("Flash Data:", flash);

            if (flash?.wa_url) {
                console.log("Opening WA:", flash.wa_url);
                window.open(flash.wa_url, '_blank');
                        
                // 2. Delay the redirect slightly so the browser doesn't cancel the popup
                setTimeout(() => {
                    window.location.href = route('dashboard');
                }, 1000);
                return;
            }

            if (action === 'save') {
                toast.success("Draft tersimpan");
                return;
            }
            if (action === 'finish') {
                window.location.href = route('dashboard');
            }
        },
        onError: () => {
            toast.error("Gagal menyimpan invoice. Periksa data kembali.");
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
                    <Input v-model="item.name" class="h-9 border-slate-200 bg-white" placeholder="Nama Barang" />
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
                    <h2 class="text-xl font-bold uppercase">Invoice #{{ workOrder.id }}</h2>
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
                                    <Package class="w-4 h-4" /> Spareparts
                                </CardTitle>
                                <a :href="route('work-orders.show', workOrder.id)" class="text-[10px] font-bold text-blue-600 flex items-center gap-1 hover:underline">
                                    <ExternalLink class="w-3 h-3" /> Edit Qty di Work Order
                                </a>
                            </CardHeader>
                            <CardContent class="p-0">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 border-b text-[10px] uppercase text-slate-400">
                                        <tr>
                                            <th class="p-4 text-left">Nama Barang</th>
                                            <th class="p-4 text-center w-20">Qty</th>
                                            <th class="p-4 text-right w-32">Harga Satuan</th>
                                            <th class="p-4 text-right w-32">Diskon Part</th>
                                            <th class="p-4 text-right w-32">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr v-for="item in editableInternalParts" :key="item.part_id" class="bg-slate-50/30">
                                            <td class="p-2">
                                                <Input v-model="item.name" class="h-9 border-slate-200 bg-white" />
                                                <div class="text-[9px] text-slate-400 mt-1 pl-1">
                                                    Satuan: {{ formatIDR(item.price) }}
                                                </div>
                                            </td>
                                            
                                            <td class="p-4 text-center font-bold text-slate-500">{{ item.qty }}</td>
                                            
                                            <td class="p-4 text-right text-slate-500">{{ formatIDR(item.price) }}</td>
                                            
                                            <td class="p-2 text-right">
                                                <Input 
                                                    v-model.number="item.discount" 
                                                    type="number" 
                                                    class="h-9 text-right text-red-600 bg-red-50/50 border-red-100 focus:border-red-400" 
                                                    placeholder="0"
                                                />
                                            </td>

                                            <td class="p-4 text-right font-mono font-bold text-slate-900">
                                                {{ formatIDR((item.price * item.qty) - (item.discount || 0)) }}
                                            </td>
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
                                            <th class="p-4 text-right w-32">Total</th>
                                            <th class="p-4 w-4"></th>
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
                                            <td class="p-4 text-right font-mono font-bold text-slate-900">
                                                {{ formatIDR((item.price * item.qty) - (item.discount || 0)) }}
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

                                <div class="space-y-2 pt-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400">
                                        {{ form.status === 'paid' ? 'Metode Pembayaran' : 'Metode Pembayaran DP' }}
                                    </label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <Button 
                                            v-for="opt in paymentOptions" 
                                            :key="opt.value"
                                            type="button"
                                            variant="outline" 
                                            :class="[
                                                'text-[9px] h-9 px-1 font-bold transition-all leading-tight',
                                                form.payment_method === opt.value ? 'bg-blue-600 text-white border-blue-600' : 'bg-white'
                                            ]"
                                            @click="form.payment_method = opt.value"
                                        >
                                            {{ opt.label }}
                                        </Button>
                                    </div>
                                </div>

                                <div v-if="form.status === 'unpaid'" 
                                    class="space-y-3 p-3 bg-red-50 rounded-lg border border-red-100 animate-in fade-in slide-in-from-top-2">
                                    
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black uppercase text-red-600">Uang Muka / DP (Bayar Sekarang)</label>
                                        <Input 
                                            type="number" 
                                            v-model.number="form.amount_paid" 
                                            class="border-red-200 font-bold" 
                                            placeholder="0"
                                        />
                                        <p class="text-[9px] text-red-400 italic">
                                            Sisa Hutang: {{ formatIDR(grandTotal - form.amount_paid) }}
                                        </p>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black uppercase text-red-600">Jatuh Tempo</label>
                                        <Input type="date" v-model="form.due_date" class="border-red-200" />
                                    </div>
                                </div>

                                <hr />

                                <Button @click="saveAsDraft" variant="ghost" class="w-full text-slate-500 hover:text-blue-600 gap-2 h-10 border border-dashed">
                                    <Save class="w-4 h-4" /> Simpan Draft
                                </Button>
                                
                                <div class="grid grid-cols-1 gap-2 mt-4">
                                    <Button @click="processTransaction('whatsapp')" 
                                            :disabled="form.processing"
                                            class="h-14 bg-green-600 hover:bg-green-700 font-black uppercase tracking-widest gap-2">
                                        <MessageCircle class="w-5 h-5" /> Simpan & Kirim WA
                                    </Button>

                                    <Button @click="processTransaction('finish')" 
                                            :disabled="form.processing" 
                                            variant="outline" 
                                            class="h-12 border-slate-200 font-bold uppercase text-xs">
                                        Simpan Tanpa Kirim
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