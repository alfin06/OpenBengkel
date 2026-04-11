<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/Components/ui/dialog';
import { PackagePlus, Trash2, Plus, Save, ArrowLeft, Calculator, Package, Search } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps<{
    suppliers: any[];
    parts: any[];
}>();

const form = useForm({
    supplier_id: '',
    entry_date: new Date().toISOString().split('T')[0],
    reference_number: '',
    notes: '',
    items: [
        { part_id: '', qty: 1, purchase_price: 0 }
    ],
    total_cost: 0
});

// --- Supplier Search ---
const supplierSearch = ref('');
const showSupplierResults = ref(false);
const filteredSuppliers = computed(() => {
    if (!supplierSearch.value) return props.suppliers;
    return props.suppliers.filter(s => s.name.toLowerCase().includes(supplierSearch.value.toLowerCase()));
});
const selectSupplier = (s) => {
    form.supplier_id = s.id;
    supplierSearch.value = s.name;
    showSupplierResults.value = false;
};

// --- Part Search ---
const partSearch = ref([]); 
const showPartResults = ref([]); 
const getFilteredParts = (index) => {
    const query = partSearch.value[index]?.toLowerCase() || '';
    if (!query) return props.parts;
    return props.parts.filter(p => 
        p.name.toLowerCase().includes(query) || (p.sku && p.sku.toLowerCase().includes(query))
    );
};
const selectPart = (rowIndex, part) => {
    form.items[rowIndex].part_id = part.id;
    form.items[rowIndex].purchase_price = part.buy_price || 0;
    partSearch.value[rowIndex] = part.name;
    showPartResults.value[rowIndex] = false;
};

// --- Quick Add Part Logic ---
const showQuickAddModal = ref(false);
const quickAddRowIndex = ref<number | null>(null);
const quickPartForm = ref({
    name: '',
    sku: '',
    type: 'Part',
    buy_price: 0,
    sell_price: 0
});

const openQuickAdd = (index: number) => {
    quickAddRowIndex.value = index;
    showQuickAddModal.value = true;
};

const isSavingQuickPart = ref(false);

const handleQuickAddSubmit = async () => {
    if (isSavingQuickPart.value) return; // Prevent double submission
    
    isSavingQuickPart.value = true;
    try {
        const response = await axios.post(route('parts.quick-add'), quickPartForm.value);
        const newPart = response.data;
        props.parts.push(newPart); // Update local list
        if (quickAddRowIndex.value !== null) {
            selectPart(quickAddRowIndex.value, newPart);
        }
        showQuickAddModal.value = false;
        quickPartForm.value = { name: '', sku: '', type: 'Part', buy_price: 0, sell_price: 0 };
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            alert('SKU ini sudah terdaftar. Silakan cari barangnya di kolom pencarian.');
        } else {
            alert('Gagal menambah barang. Periksa koneksi atau data.');
        }
    } finally {
        isSavingQuickPart.value = false;

    }
};

// --- Table Actions ---
const addItem = () => {
    form.items.push({ part_id: '', qty: 1, purchase_price: 0 });
};
const removeItem = (index: number) => {
    if (form.items.length > 1) form.items.splice(index, 1);
};
const grandTotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.qty * item.purchase_price), 0);
});
const submit = () => {
    form.total_cost = grandTotal.value;
    form.post(route('stock-entries.store'));
};
const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

// Click-away helper
const closeDropdowns = () => {
    setTimeout(() => {
        showSupplierResults.value = false;
        showPartResults.value = [];
    }, 200);
};
</script>

<template>
    <Head title="Tambah Stok Baru" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <Link :href="route('parts.index')">
                        <Button variant="ghost" size="icon"><ArrowLeft class="w-5 h-5" /></Button>
                    </Link>
                    <h2 class="text-xl font-bold uppercase text-slate-800">Penerimaan Barang (Stock In)</h2>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <Dialog :open="showQuickAddModal" @update:open="showQuickAddModal = $event">
                <DialogContent class="sm:max-w-[425px] rounded-2xl">
                    <DialogHeader>
                        <DialogTitle class="flex items-center gap-2 uppercase font-black text-blue-600">
                            <Plus class="w-5 h-5" /> Master Barang Baru
                        </DialogTitle>
                    </DialogHeader>
                    <div class="space-y-4 py-4">
                        <div class="space-y-1">
                            <Label class="text-[10px] font-bold uppercase text-slate-400">Nama Barang</Label>
                            <Input v-model="quickPartForm.name" placeholder="Nama sparepart..." />
                        </div>
                        <div class="space-y-1">
                            <Label class="text-[10px] font-bold uppercase text-slate-400">SKU / Barcode</Label>
                            <Input v-model="quickPartForm.sku" placeholder="Scan barcode..." />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <Label class="text-[10px] font-bold uppercase text-slate-400">Harga Jual Retail</Label>
                                <Input v-model="quickPartForm.sell_price" type="number" />
                            </div>
                            <div class="space-y-1">
                                <Label class="text-[10px] font-bold uppercase text-slate-400">Tipe</Label>
                                <select v-model="quickPartForm.type" class="w-full border border-slate-200 rounded-lg h-10 text-sm px-2">
                                    <option value="Part">Sparepart</option>
                                    <option value="Service">Jasa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="ghost" @click="showQuickAddModal = false">Batal</Button>
                        <Button 
                            @click="handleQuickAddSubmit" 
                            class="bg-blue-600 font-bold px-6" 
                            :disabled="isSavingQuickPart">
                            {{ isSavingQuickPart ? 'MENYIMPAN...' : 'SIMPAN & PILIH' }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
            <form @submit.prevent="submit" class="space-y-6">
                <Card class="border-blue-100 shadow-sm">
                    <CardContent class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2 relative">
                            <Label class="text-[10px] font-black uppercase text-slate-400">Pilih Supplier</Label>
                            <div class="flex items-center gap-2">
                                <Input 
                                    v-model="supplierSearch" 
                                    @focus="showSupplierResults = true"
                                    placeholder="Ketik nama supplier..." />
                                <Button 
                                    type="button" 
                                    variant="ghost" 
                                    size="icon" 
                                    @click="openQuickAdd(index)"
                                    class="h-8 w-8 text-blue-600 hover:bg-blue-50" >
                                    <Plus class="w-4 h-4" />
                                </Button>
                            </div>
                            <div v-if="showSupplierResults" class="absolute z-50 w-full bg-white border rounded-lg shadow-xl max-h-60 overflow-y-auto mt-1">
                                <div v-for="s in filteredSuppliers" :key="s.id" 
                                    @click="selectSupplier(s)"
                                    class="p-3 hover:bg-blue-50 cursor-pointer text-sm border-b last:border-0">
                                    <p class="font-bold">{{ s.name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ s.phone_sales || s.phone_office }}</p>
                                </div>
                                <div v-if="filteredSuppliers.length === 0" class="p-4 text-center text-xs text-slate-400">
                                    Supplier tidak ditemukan.
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase text-slate-400">Tanggal Terima</Label>
                            <Input v-model="form.entry_date" type="date" required />
                        </div>
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase text-slate-400">No. Invoice Supplier (Ref)</Label>
                            <Input v-model="form.reference_number" placeholder="Contoh: INV/2024/001" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="shadow-sm">
                    <CardHeader class="border-b bg-slate-50/50 flex flex-row justify-between items-center p-4">
                        <CardTitle class="text-sm font-bold uppercase flex items-center gap-2">
                            <PackagePlus class="w-4 h-4 text-blue-600" /> Daftar Item
                        </CardTitle>
                        <Button type="button" @click="addItem" variant="outline" size="sm" class="h-8 text-blue-600 border-blue-200 hover:bg-blue-50">
                            <Plus class="w-4 h-4 mr-1" /> Tambah Baris
                        </Button>
                    </CardHeader>
                    <CardContent class="p-0">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500 border-b">
                                <tr>
                                    <th class="p-4 w-1/2">Nama Barang</th>
                                    <th class="p-4 text-center w-20">Qty</th>
                                    <th class="p-4 text-right">Harga Modal (Satuan)</th>
                                    <th class="p-4 text-right">Total</th>
                                    <th class="p-4 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="(item, index) in form.items" :key="index">
                                    <td class="p-3 relative">
                                        <Input 
                                            v-model="partSearch[index]" 
                                            @focus="showPartResults[index] = true"
                                            placeholder="Cari barang..."
                                            class="border-none bg-transparent shadow-none focus:ring-0"
                                        />
                                        <div v-if="showPartResults[index]" class="absolute z-[70] w-[350px] bg-white border border-slate-200 rounded-xl shadow-2xl max-h-60 overflow-y-auto mt-2 left-3 p-1">
                                            <div v-for="p in getFilteredParts(index)" :key="p.id" 
                                                @click="selectPart(index, p)"
                                                class="p-3 hover:bg-blue-50 cursor-pointer text-sm border-b last:border-0 text-left">
                                                <p class="font-bold">{{ p.name }}</p>
                                                <p class="text-[10px] text-slate-400 font-mono">{{ p.sku || 'No SKU' }} • Stok: {{ p.stock }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        <Input v-model="item.qty" type="number" min="1" class="text-center border-slate-100" />
                                    </td>
                                    <td class="p-3">
                                        <Input v-model="item.purchase_price" type="number" class="text-right border-slate-100" />
                                    </td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-600">
                                        {{ formatIDR(item.qty * item.purchase_price) }}
                                    </td>
                                    <td class="p-3">
                                        <Button type="button" variant="ghost" size="icon" @click="removeItem(index)" class="text-red-400 hover:text-red-600 hover:bg-red-50">
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </CardContent>
                </Card>

                <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                    <div class="w-full md:w-1/2">
                        <Label class="text-[10px] font-black uppercase text-slate-400">Catatan Tambahan</Label>
                        <textarea v-model="form.notes" class="w-full mt-2 border-slate-200 rounded-xl text-sm h-24" placeholder="Contoh: Barang titipan, atau promo beli 10 gratis 1..."></textarea>
                    </div>
                    
                    <div class="w-full md:w-1/3 space-y-4">
                        <div class="bg-blue-900 text-white p-6 rounded-2xl shadow-lg relative overflow-hidden">
                            <Calculator class="absolute -right-4 -bottom-4 w-24 h-24 text-white/10" />
                            <p class="text-[10px] uppercase font-bold text-blue-300 mb-1">Total Pembelian</p>
                            <p class="text-3xl font-black">{{ formatIDR(grandTotal) }}</p>
                        </div>
                        
                        <Button type="submit" class="w-full h-14 bg-green-600 hover:bg-green-700 text-lg font-black uppercase tracking-widest shadow-xl" :disabled="form.processing">
                            <Save class="w-5 h-5 mr-2" /> Simpan Stok Masuk
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>