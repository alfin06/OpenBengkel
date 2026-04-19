<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useForm, Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Pencil, Trash2, X, Check, Package, ScanBarcode, Printer, Play, CreditCard, Search, RotateCcw } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import axios from 'axios';
import debounce from 'lodash/debounce';

const props = defineProps<{ 
    workOrder: any,
    mechanics: any[]
}>();

const page = usePage();

// Fallback for Shop Info (used in Print)
const shop = computed(() => (page.props.shop as any) || {
    shop_name: 'BENGKEL KALIMAS LUMAS',
    shop_address: 'Alamat Bengkel Anda',
    shop_phone: '0812-xxxx-xxxx',
    logo_path: null
});

// --- 1. The "Zapper" & Search Logic ---
const barcodeInput = ref<HTMLInputElement | null>(null);
const partForm = useForm({ sku: '' });
const searchResults = ref([]);
const isSearching = ref(false);

// This function fetches the items from your "Data Barang" table
const fetchParts = debounce(async (query) => {
    if (query.length < 2) {
        searchResults.value = [];
        return;
    }
    
    isSearching.value = true;
    try {
        const response = await axios.get(route('parts.search'), { params: { q: query } });
        searchResults.value = response.data;
    } catch (e) {
        console.error("Search failed", e);
    } finally {
        isSearching.value = false;
    }
}, 300);

watch(() => partForm.sku, (newVal) => {
    fetchParts(newVal);
});

const selectPart = (part: any) => {
    partForm.sku = part.sku; 
    handleBarcodeZap(); // Re-use zap logic
    searchResults.value = [];
};

const focusBarcode = () => {
    const target = barcodeInput.value as any;
    if (!target) return;
    if (target instanceof HTMLInputElement) {
        target.focus();
    } else if (target.$el) {
        const input = target.$el instanceof HTMLInputElement ? target.$el : target.$el.querySelector('input');
        input?.focus();
    }
};

onMounted(() => focusBarcode());

const handleBarcodeZap = () => {
    if (!partForm.sku) return;
    
    partForm.post(route('work-orders.parts.store', props.workOrder.id), {
        onSuccess: () => {
            partForm.reset();
            searchResults.value = [];
            focusBarcode();
        },
        onError: () => {
            toast.error("Item tidak ditemukan!");
            partForm.reset();
            focusBarcode();
        },
        preserveScroll: true,
    });
};

// Watch the input for searching names
watch(() => partForm.sku, async (newVal) => {
    // Only search if typing (short string), if it's a long barcode scanned fast, 
    // handleBarcodeZap will usually trigger via 'Enter' before search results matter.
    if (newVal.length > 2) {
        try {
            const response = await axios.get(route('parts.search'), { params: { q: newVal } });
            searchResults.value = response.data;
        } catch (e) {
            console.error("Search failed", e);
        }
    } else {
        searchResults.value = [];
    }
});

// --- 2. Edit Check-in Info Logic ---
const isEditingInfo = ref(false);
const infoForm = useForm({
    driver_name: props.workOrder.driver_name || '',
    odometer: props.workOrder.odometer || '',
    complaints: props.workOrder.complaints || '',
    mechanic_id: props.workOrder.mechanic_id || '',
});

const updateInfo = () => {
    infoForm.patch(route('work-orders.update', props.workOrder.id), {
        onSuccess: () => {
            isEditingInfo.value = false;
            toast.success("Info Check-in diperbarui");
        },
    });
};

// --- 3. Task / Instruksi Logic ---
const itemForm = useForm({ task_name: '' });
const addItem = () => {
    itemForm.post(route('work-orders.items.store', props.workOrder.id), {
        onSuccess: () => itemForm.reset(),
    });
};

const editingItemId = ref<number | null>(null);
const editItemForm = useForm({ task_name: '' });

const startEditTask = (item: any) => {
    editingItemId.value = item.id;
    editItemForm.task_name = item.task_name;
};

const updateTaskName = (id: number) => {
    editItemForm.patch(route('work-order-items.update-task', id), {
        onSuccess: () => editingItemId.value = null,
    });
};

const deleteTask = (id: number) => {
    if (confirm('Hapus instruksi ini?')) {
        router.delete(route('work-order-items.destroy', id));
    }
};

const removePart = (itemId: number) => {
    if (confirm('Hapus item ini?')) {
        // Use router.delete, NOT router.post
        router.delete(`/work-order-parts/${itemId}`, {
            preserveScroll: true,
        });
    }
};

// --- Helpers ---
const formatIDR = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0
    }).format(value);
};

const calculateTotal = computed(() => {
    return (props.workOrder.parts || []).reduce((acc, p) => {
        const qty = p.pivot?.qty || 0;
        const price = p.pivot?.price_sold || 0;
        return acc + (price * qty);
    }, 0);
});

const printJob = () => window.print();

const updatePartQty = (part: any) => {
    // 1. We must send the PIVOT ID, not the PART ID
    // 2. We must send the PIVOT QTY, which is what the input is bound to
    router.patch(route('work-orders.parts.update-qty', part.pivot.id), {
        qty: part.pivot.qty
    }, {
        preserveScroll: true,
        onSuccess: () => toast.success("Jumlah diperbarui"),
        onError: () => {
            toast.error("Gagal memperbarui jumlah. Periksa stok!");
            router.reload();
        }
    });
};

const reopenWorkOrder = () => {
    // Check if invoice exists first
    const invoiceId = props.workOrder.invoice?.id;

    if (!invoiceId) {
        toast.error("Data invoice tidak ditemukan untuk Work Order ini.");
        return;
    }

    if (confirm('Buka kembali Work Order ini? Invoice akan di-reset dan stok akan dikembalikan sementara.')) {
        router.post(route('invoices.reopen', invoiceId), {}, {
            onSuccess: () => toast.success("Work Order dibuka kembali."),
            onError: () => toast.error("Gagal membuka kembali nota."),
        });
    }
};

const isAdmin = computed(() => page.props.auth.user.role === 'admin');

const deleteWorkOrder = () => {
    if (confirm('Are you sure? This will delete the WO and return all parts to stock.')) {
        router.delete(route('work-orders.destroy', props.workOrder.id));
    }
}
</script>

<template>
    <Head title="Kelola Work Order" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <h2 class="text-xl font-bold">WORK ORDER #{{ workOrder.id }}</h2>
                    <span :class="[
                        'px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest',
                        workOrder.status === 'Selesai' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'
                    ]">
                        {{ workOrder.status }}
                    </span>
                    <Button 
                        v-if="workOrder.status === 'Selesai' && isAdmin && workOrder.invoice" 
                        variant="destructive" 
                        size="sm" 
                        @click="reopenWorkOrder"
                        class="h-7 px-3 text-[10px] font-bold uppercase gap-1 animate-pulse">
                        <RotateCcw class="w-3 h-3" /> Buka Nota
                    </Button>
                    <button 
                        v-if=isAdmin
                        @click="deleteWorkOrder"
                        class="text-red-600 hover:text-red-900">
                        <Trash2 class="w-3 h-3" />
                    </button>
                </div>
                <div class="flex gap-2 no-print">
                    <Button variant="outline" @click="printJob" class="gap-2">
                        <Printer class="w-4 h-4" /> Print Job Slip
                    </Button>
                </div>
            </div>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 no-print">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <Card class="lg:col-span-2 border-2 border-blue-500 shadow-md bg-blue-50/30">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-xs font-black text-blue-700 uppercase flex items-center gap-2">
                            <Search class="w-5 h-5" /> Cari Nama Sparepart atau Scan Barcode
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="handleBarcodeZap" class="flex gap-4">
                            <div class="relative flex-1">
                                <input 
                                    ref="barcodeInput"
                                    v-model="partForm.sku"
                                    placeholder="Ketik Nama Barang atau Scan Barcode..."
                                    class="w-full text-2xl h-16 px-4 font-mono font-bold border-2 rounded-md border-blue-500 focus:ring-4 focus:ring-blue-200 outline-none bg-white"
                                    autocomplete="off"
                                    @keyup.enter="handleBarcodeZap" />
                                
                                <div v-if="searchResults && searchResults.length > 0"
                                    class="absolute z-[100] w-full bg-white border-2 border-blue-600 rounded-b-xl shadow-2xl mt-1 max-h-96 overflow-y-auto">
                                    <div 
                                        v-for="part in searchResults" 
                                        :key="part.id" 
                                        @click="selectPart(part)"
                                        class="p-4 hover:bg-blue-600 hover:text-white cursor-pointer flex justify-between items-center border-b border-slate-100 transition-all group"
                                    >
                                        <div class="flex flex-col">
                                            <span class="font-black text-lg uppercase">{{ part.name }}</span>
                                            <span class="text-xs font-mono opacity-70 group-hover:opacity-100">{{ part.sku }}</span>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-xl">{{ formatIDR(part.sell_price) }}</p>
                                            <p :class="part.stock > 0 ? 'text-green-500 group-hover:text-white' : 'text-red-500'" class="text-[10px] font-black uppercase">
                                                Stok: {{ part.stock }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <Button type="submit" class="h-16 px-8 bg-blue-600 font-bold uppercase hover:bg-blue-700">Enter</Button>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs uppercase text-slate-500">Kendaraan</CardTitle>
                        <Button variant="ghost" size="sm" @click="isEditingInfo = !isEditingInfo">
                            <Pencil class="w-3.5 h-3.5" />
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div v-if="!isEditingInfo" class="space-y-2">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="bg-slate-900 text-white px-2 py-0.5 rounded font-mono font-bold text-sm">{{ workOrder.vehicle.plate_number }}</div>
                                <span class="text-sm font-bold">{{ workOrder.vehicle.brand }} {{ workOrder.vehicle.model }}</span>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 p-3 bg-slate-50 rounded-lg border border-slate-100">
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-slate-400">Pembawa (Driver)</p>
                                    <p class="text-sm font-medium text-slate-800">{{ workOrder.driver_name || 'Owner' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-slate-400">Odometer</p>
                                    <p class="text-sm font-mono font-bold text-slate-800">{{ workOrder.odometer || '-' }} KM</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-slate-400">Keluhan</p>
                                    <p class="text-sm font-mono font-bold text-slate-800">{{ workOrder.complaints || '-' }}</p>
                                </div>
                            </div>

                            <div class="p-3 bg-blue-50 rounded-lg border border-blue-100 mt-2 flex justify-between items-center">
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-blue-500">Mekanik Bertugas</p>
                                    <p class="text-sm font-black text-blue-900">{{ workOrder.mechanic?.name || 'Belum Ditugaskan' }}</p>
                                </div>
                                <Button v-if="!workOrder.mechanic_id" variant="ghost" size="sm" class="text-blue-600 hover:bg-blue-100 h-7 text-xs" @click="isEditingInfo = true">
                                    Tugaskan
                                </Button>
                            </div>
                        </div>

                        <form v-else @submit.prevent="updateInfo" class="space-y-3">
                            <div>
                                <Label class="text-xs text-slate-500">Mekanik</Label>
                                <select v-model="infoForm.mechanic_id" class="w-full h-9 rounded-md border border-slate-300 text-sm px-3 bg-white">
                                    <option value="">-- Pilih Mekanik --</option>
                                    <option v-for="mechanic in mechanics" :key="mechanic.id" :value="mechanic.id">
                                        {{ mechanic.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <Label class="text-xs text-slate-500">Driver</Label>
                                    <Input v-model="infoForm.driver_name" placeholder="Driver" class="h-9 text-xs" />
                                </div>
                                <div>
                                    <Label class="text-xs text-slate-500">KM</Label>
                                    <Input v-model="infoForm.odometer" placeholder="KM" class="h-9 text-xs font-mono" />
                                </div>
                                <div>
                                    <Label class="text-xs text-slate-500">Keluhan</Label>
                                    <Input v-model="infoForm.complaints" placeholder="Keluhan" class="h-9 text-xs" />
                                </div>
                            </div>
                            <div class="flex gap-2 pt-2">
                                <Button type="button" variant="outline" size="sm" class="w-full h-9" @click="isEditingInfo = false">Batal</Button>
                                <Button type="submit" size="sm" class="w-full h-9 bg-blue-600">Simpan Data</Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <Card class="shadow-sm">
                    <CardHeader class="bg-slate-50 border-b py-3">
                        <CardTitle class="text-xs font-bold uppercase text-slate-600 tracking-wider">Instruksi Mekanik</CardTitle>
                    </CardHeader>
                    <CardContent class="p-0">
                        <ul class="divide-y min-h-[150px]">
                            <li v-for="item in workOrder.items" :key="item.id" class="p-4 flex justify-between items-center group bg-white">
                                <div v-if="editingItemId !== item.id" class="flex items-center gap-3 flex-1">
                                    <input type="checkbox" :checked="item.is_completed" @change="router.patch(route('work-order-items.update', item.id))" class="w-5 h-5 rounded border-gray-300 text-blue-600" />
                                    <span :class="{'line-through text-slate-400 italic': item.is_completed}" class="text-lg">{{ item.task_name }}</span>
                                </div>
                                <div v-else class="flex gap-2 flex-1">
                                    <Input v-model="editItemForm.task_name" class="h-9" />
                                    <Button size="sm" class="bg-green-600" @click="updateTaskName(item.id)"><Check class="w-4 h-4" /></Button>
                                </div>
                                <div v-if="editingItemId !== item.id" class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Button variant="ghost" size="sm" @click="startEditTask(item)"><Pencil class="w-4 h-4" /></Button>
                                    <Button variant="ghost" size="sm" @click="deleteTask(item.id)"><Trash2 class="w-4 h-4 text-red-500" /></Button>
                                </div>
                            </li>
                        </ul>
                        <div class="p-4 border-t bg-slate-50/50">
                            <form @submit.prevent="addItem" class="flex gap-2">
                                <Input v-model="itemForm.task_name" placeholder="Tambah Jasa..." />
                                <Button class="bg-slate-900 px-6 font-bold">Tambah</Button>
                            </form>
                        </div>
                    </CardContent>
                </Card>

                <Card class="shadow-sm border-green-200">
                    <CardHeader class="bg-green-50/50 border-b py-3 flex flex-row items-center justify-between">
                        <CardTitle class="text-xs font-bold uppercase text-green-700 tracking-wider">Sparepart Terpakai</CardTitle>
                        <Package class="w-4 h-4 text-green-600" />
                    </CardHeader>
                    <CardContent class="p-0">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500 border-b">
                                <tr>
                                    <th class="p-3 text-left">Nama Barang</th>
                                    <th class="p-3 text-center">Qty</th>
                                    <th class="p-3 text-right">Harga</th>
                                    <th class="p-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="part in workOrder?.parts || []" :key="part.id" class="hover:bg-green-50/20">
                                    <td class="p-3 font-medium">
                                        {{ part?.name }}
                                        <div class="text-[10px] text-slate-400 font-mono">{{ part?.sku }}</div>
                                    </td>
                                    <td class="p-3 text-center">
                                        <div class="flex justify-center items-center">
                                            <input 
                                                type="number" 
                                                v-model="part.pivot.qty" 
                                                min="1"
                                                @change="updatePartQty(part)"
                                                class="w-16 h-8 text-center font-bold border rounded-md focus:ring-2 focus:ring-green-500 outline-none" />
                                        </div>
                                    </td>
                                    <td class="p-3 text-right font-mono text-blue-700">{{ formatIDR(part.pivot?.price_sold) }}</td>
                                    <td class="p-3 text-right">
                                        <Button variant="ghost" size="icon" @click="removePart(part.pivot.id)" class="h-8 w-8 text-red-400">
                                            <X class="w-4 h-4" />
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="!workOrder?.parts || workOrder.parts.length === 0">
                                    <td colspan="4" class="p-12 text-center text-slate-400 italic">Scan atau cari sparepart di atas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </CardContent>
                </Card>
            </div>

            <div class="flex justify-between items-center bg-white p-8 rounded-xl border-2 border-slate-100 shadow-sm">
                <div>
                    <p class="text-[10px] uppercase font-black text-slate-400 tracking-widest mb-1">Total Tagihan (Estimasi)</p>
                    <p class="text-4xl font-black text-blue-600">{{ formatIDR(calculateTotal) }}</p>
                </div>
                <div class="flex gap-3">
                    <Button v-if="workOrder.status === 'Pending'" class="h-14 px-10 bg-green-600 text-lg font-bold gap-2" @click="router.patch(route('work-orders.start', workOrder.id))">
                        <Play class="w-5 h-5" /> Mulai Kerja
                    </Button>
                    <Button v-else-if="workOrder.status === 'Proses'" class="h-14 px-10 bg-blue-600 text-lg font-bold gap-2" @click="router.get(route('invoices.create', workOrder.id))">
                        <CreditCard class="w-5 h-5" /> Selesai & Bayar
                    </Button>
                </div>
            </div>
        </div>

        <div class="print-only">
            <div class="flex items-center gap-6 border-b-2 border-black pb-2 mb-2">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold uppercase">{{ shop.shop_name }}</h1>
                    <p class="text-[9pt]">{{ shop.shop_address }} | Telp: {{ shop.shop_phone }}</p>
                </div>
                <div class="text-right font-mono text-[10pt]">
                    <p>WO: #{{ workOrder.id }}</p>
                    <p>TGL: {{ new Date().toLocaleDateString('id-ID') }}</p>
                </div>
            </div>
            <div class="text-center font-bold underline mb-2 text-lg uppercase tracking-widest">JOB SLIP / PERINTAH KERJA</div>
            <table class="w-full mb-3 border border-black border-collapse font-mono text-[10pt]">
                <tbody>
                    <tr>
                        <td class="border border-black p-1 w-1/2 uppercase"><b>PLAT:</b> {{ workOrder.vehicle.plate_number }}</td>
                        <td class="border border-black p-1 w-1/2 uppercase"><b>PEMBAWA:</b> {{ workOrder.driver_name || '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black p-1 uppercase"><b>MOBIL:</b> {{ workOrder.vehicle.brand }} {{ workOrder.vehicle.model }} ({{ workOrder.vehicle.color }})</td>
                        <td class="border border-black p-1 uppercase"><b>KM:</b> {{ workOrder.odometer }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="font-mono">
                <p class="font-bold underline text-[10pt] mb-1 uppercase">Daftar Pengerjaan & Sparepart:</p>
                <div class="min-h-[220px] border border-black p-2 bg-white">
                    <div v-for="(item, index) in workOrder.items" :key="'t'+item.id" class="text-[11pt] uppercase">
                        {{ index + 1 }}. [ ] {{ item.task_name }} (JASA)
                    </div>
                    <div v-for="(part, index) in workOrder.parts" :key="'p'+part.id" class="text-[11pt] font-bold uppercase">
                        {{ workOrder.items.length + index + 1 }}. [ ] {{ part.name }} x{{ part.pivot.qty }}
                    </div>
                </div>
            </div>
            <p class="text-[9pt] italic mt-2 uppercase">Keluhan: {{ workOrder.complaints || '-' }}</p>
            <div class="grid grid-cols-2 text-center mt-6 font-mono text-[10pt]">
                <div><p class="mb-10 uppercase">Mekanik,</p><p>( ___________ )</p></div>
                <div><p class="mb-10 uppercase">Admin,</p><p>( {{ page.props.auth?.user?.name || 'Admin' }} )</p></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Normal view: Hide the print section */
.print-only {
    display: none;
}

@media print {
    /* 1. Hide the entire Web UI */
    nav, header, footer, button, form, .no-print, .py-12, aside {
        display: none !important;
    }

    /* 2. Setup the Dot Matrix Page (Landscape) */
    @page {
        /* This forces the printer into Landscape mode */
        size: landscape; 
        /* Margin: 0 lets us control spacing via CSS */
        margin: 0; 
    }

    body {
        background: white !important;
        margin: 0;
        padding: 0;
    }

    .print-only {
        display: block !important;
        /* Common Continuous Form Width (minus tractor holes) */
        width: 210mm; 
        /* Common Continuous Form Height (Half-Letter/A5 Landscape) */
        height: 140mm; 
        margin: 0 auto;
        padding: 5mm 5mm; /* Extra left/right padding for tractor holes */
        color: black !important;
        font-family: "Courier New", Courier, monospace;
        font-size: 11pt; /* Slightly smaller for dot matrix clarity */
    }

    /* Ensure borders are visible on the 3rd (yellow) layer */
    .border-2, .border-black {
        border: 2px solid black !important;
    }
}
</style>