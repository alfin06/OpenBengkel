<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useForm, Head, usePage, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Receipt, CreditCard, Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps<{ 
    workOrder: any,
    formattedParts: any[], 
    shop: any,
    totalParts: number 
}>();

const invoiceItems = ref([]);

// Merge parts from scanner and tasks from mechanic
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

syncItems();

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

// Manual Item Management
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

// Formatting & Calculations
const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

const subtotal = computed(() => {
    return invoiceItems.value.reduce((acc, item) => acc + (item.price * item.qty), 0);
});

const grandTotal = computed(() => {
    return subtotal.value - form.discount;
});

interface CustomProps {
    flash: {
        success?: string;
        error?: string;
        print_id?: number | string | null;
    };
    auth: {
        user: any;
    };
    [key: string]: any; // Allow for other props like errors, etc.
}

const handlePrintSequence = () => {
    // 1. Brief pause for the TV browser to register the data
    setTimeout(() => {
        window.print();
        
        // 2. After printing, redirect to dashboard
        setTimeout(() => {
            window.location.href = route('dashboard');
        }, 1000);
    }, 500);
};

const page = usePage<CustomProps>();

const isSaved = ref(false);

// We combine the save action with whatever the user wants to do next
const processTransaction = (action) => {
    // If it's already saved, just do the action (prevents duplicate invoices!)
    if (isSaved.value) {
        if (action === 'print') window.print();
        if (action === 'finish') window.location.href = route('dashboard');
        return;
    }

    if (form.processing) return;
    form.items = invoiceItems.value;
    
    // If not saved, save it to the database FIRST
    form.post('/invoices', {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isSaved.value = true; // Lock the database!
            
            // Now do what the user originally clicked
            if (action === 'print') {
                setTimeout(() => { window.print(); }, 300);
            } else if (action === 'finish') {
                window.location.href = route('dashboard');
            }
        },
        onError: (errors) => {
            console.error("Database Error:", errors);
            alert("Gagal menyimpan data! Periksa input Anda.");
        }
    });
};

watch(
    () => page.props.flash.print_id, 
    (newId) => {
        if (newId) {
            console.log("Success! Printing Invoice:", newId);
            handlePrintSequence();
        }
    }, 
    { immediate: true, deep: true }
);
</script>

<template>
    <div class="print-layout p-4 text-black font-mono bg-white">
        <div class="text-center mb-4">
            <h1 class="text-xl font-bold uppercase">
                {{ shop?.shop_name || 'OpenBengkel' }}
            </h1>
            <p class="text-xs">{{ shop?.shop_address || 'Jl. masukan alamat' }}</p>
            <p class="text-xs">Telp: {{ shop?.shop_phone || '-' }}</p>
            <hr class="my-2 border-black">
        </div>

        <div class="flex justify-between text-xs mb-2">
            <span>Nota: #{{ workOrder.id }}</span>
            <span>Tgl: {{ new Date().toLocaleDateString('id-ID') }}</span>
        </div>

        <div class="text-xs mb-4">
            <p>Plat: {{ workOrder.vehicle?.plate_number }}</p>
            <p>Cust: {{ workOrder.vehicle?.customer?.name }}</p>
        </div>

        <table class="w-full text-xs mb-4">
            <tr v-for="item in invoiceItems" :key="item.name">
                <td class="py-1">{{ item.name }} (x{{ item.qty }})</td>
                <td class="text-right">{{ formatIDR(item.price * item.qty) }}</td>
            </tr>
        </table>

        <hr class="border-black border-dashed my-2">
        
        <div class="flex justify-between font-bold">
            <span>TOTAL:</span>
            <span>{{ formatIDR(grandTotal) }}</span>
        </div>

        <div class="text-center mt-6 text-[10px]">
            <p>Terima Kasih Atas Kunjungan Anda</p>
            <p>Periksa kembali kendaraan anda sebelum meninggalkan bengkel</p>
        </div>
    </div>
    
    <div class="no-print">
    <Head title="Buat Invoice" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold uppercase">Pembayaran Work Order #{{ workOrder.id }}</h2>
        </template>

        <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 no-print">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 rounded-lg border shadow-sm">
                        <div class="flex flex-col">
                            <span class="text-[10px] uppercase font-bold text-slate-400">Pelanggan</span>
                            <span class="font-bold text-slate-900 text-lg uppercase">
                                {{ workOrder.vehicle?.customer?.name || 'Umum / Cash' }}
                            </span>
                            <span class="text-xs text-slate-500">{{ workOrder.vehicle?.customer?.phone || '-' }}</span>
                        </div>
                        <div class="flex flex-col border-l pl-4">
                            <span class="text-[10px] uppercase font-bold text-slate-400">Kendaraan</span>
                            <span class="font-mono font-bold text-blue-600 text-lg uppercase">
                                {{ workOrder.vehicle?.plate_number }}
                            </span>
                            <span class="text-xs text-slate-500">{{ workOrder.vehicle?.brand }} {{ workOrder.vehicle?.model }}</span>
                        </div>
                        <div class="flex flex-col border-l pl-4">
                            <span class="text-[10px] uppercase font-bold text-slate-400">KM / Driver</span>
                            <span class="font-bold text-slate-900 text-lg">
                                {{ workOrder.odometer ? Number(workOrder.odometer).toLocaleString() : '-' }} <span class="text-xs">KM</span>
                            </span>
                            <span class="text-xs text-slate-500 italic">{{ workOrder.driver_name || 'Owner' }}</span>
                        </div>
                    </div>

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

                            <div class="space-y-2 pt-4 border-t">
                                <label class="text-xs font-black uppercase text-slate-500">Payment Status</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <Button 
                                        variant="outline"
                                        :class="form.status === 'paid' ? 'bg-green-600 text-white border-green-600' : ''"
                                        @click="form.status = 'paid'; form.payment_method = 'Cash'"
                                    >Bayar Lunas</Button>
                                    
                                    <Button 
                                        variant="outline"
                                        :class="form.status === 'unpaid' ? 'bg-red-600 text-white border-red-600' : ''"
                                        @click="form.status = 'unpaid'; form.payment_method = 'Receivable'"
                                    >Hutang</Button>
                                </div>
                            </div>

                            <div v-if="form.status === 'unpaid'" class="space-y-4 py-4 animate-in fade-in slide-in-from-top-2">
                                <div class="space-y-2">
                                    <label class="text-xs font-black uppercase text-slate-500">DP / Uang Muka (Opsional)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-slate-400">Rp</span>
                                        <Input 
                                            type="number" 
                                            v-model.number="form.amount_paid" 
                                            class="pl-10 font-bold" 
                                        />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-black uppercase text-red-600">Jatuh Tempo (Due Date)</label>
                                    <Input type="date" v-model="form.due_date" class="border-red-300 focus:border-red-500" />
                                </div>

                                <div class="flex justify-between items-center p-3 bg-red-50 text-red-700 rounded-lg border border-red-200 mt-2">
                                    <span class="text-[10px] font-black uppercase tracking-wider">Sisa Tagihan</span>
                                    <span class="font-mono font-bold text-lg">
                                        {{ formatIDR(grandTotal - (form.amount_paid || 0)) }}
                                    </span>
                                </div>
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
                                @click="processTransaction('print')"
                                :disabled="form.processing"
                                variant="outline"
                                class="w-full h-16 text-blue-600 border-blue-600 text-lg font-bold gap-2 uppercase">
                                <Receipt class="w-6 h-6" /> Cetak
                            </Button>

                            <Button 
                                @click="processTransaction('finish')"
                                :disabled="form.processing"
                                class="w-full h-16 bg-green-600 hover:bg-green-700 text-white text-lg font-bold uppercase gap-2">
                                <CreditCard class="w-6 h-6" v-if="!isSaved" /> Selesai
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
    </div>
</template>

<style>
/* ON THE SCREEN: Hide the receipt */
@media screen {
    .print-layout {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        z-index: -100;
    }
}

/* ON THE PAPER: Show receipt, hide UI */
@media print {
    /* Hide the UI wrappers, but DO NOT hide #app */
    .no-print, 
    nav, 
    header, 
    aside {
        display: none !important;
    }

    /* Force the receipt to take over */
    .print-layout {
        display: block !important;
        opacity: 1 !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        visibility: visible !important;
        z-index: 9999 !important;
    }

    /* Ensure a clean white background for the paper */
    body, html, #app {
        background-color: white !important;
    }
}
</style>