<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { ScanBarcode, ArrowUpRight, History } from 'lucide-vue-next';

const props = defineProps<{ parts: any[] }>();

const searchSku = ref('');
const selectedPart = computed(() => {
    if (!searchSku.value || searchSku.value.length < 2) {
        return null;
    }

    const term = searchSku.value.toLowerCase();
    
    return props.parts.find(p => 
        (p.sku && p.sku.toLowerCase() === term) || 
        p.name.toLowerCase().includes(term)
    );
});

const form = useForm({
    incoming_qty: 0,
    new_buy_price: 0,
});

const submitRestock = () => {
    if (!selectedPart.value) return;
    
    form.post(route('parts.restock', selectedPart.value.id), {
        onSuccess: () => {
            form.reset();
            searchSku.value = '';
            document.getElementById('scanner-input')?.focus();
        },
    });
};

const isNewItem = computed(() => {
    return searchSku.value.length > 3 && !selectedPart.value;
});

const newPartForm = useForm({
    name: '',
    sku: '', // This will be auto-filled by the scanner
    buy_price: 0,
    sell_price: 0,
    stock: 0,
    type: 'Part',
});

const submitNewPart = () => {
    newPartForm.sku = searchSku.value; // Capture the scanned barcode
    newPartForm.post(route('parts.store'), {
        onSuccess: () => {
            newPartForm.reset();
            // The item is now in the 'parts' prop, so selectedPart will now find it!
        },
    });
};
</script>

<template>
    <Head title="Stok Masuk" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold">Input Barang Masuk (Restock)</h2>
        </template>

        <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <Card class="border-2 border-blue-500 shadow-lg">
                <CardHeader class="bg-blue-50">
                    <CardTitle class="flex items-center gap-2 text-blue-700">
                        <ScanBarcode class="w-6 h-6" /> 
                        Langkah 1: Zap Barcode Barang
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-6">
                    <Input 
                        id="scanner-input"
                        v-model="searchSku" 
                        placeholder="Gunakan scanner atau ketik nama barang..." 
                        class="text-lg py-6 border-2 focus:ring-blue-500"
                        autoFocus
                    />
                </CardContent>
            </Card>

            <Card v-if="selectedPart && searchSku" class="border-green-500 animate-in fade-in slide-in-from-bottom-4">
                <CardHeader class="bg-green-50">
                    <CardTitle class="text-green-800">Langkah 2: Detail Pembelian Baru</CardTitle>
                </CardHeader>
                <CardContent class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <p class="text-xs font-bold text-slate-500 uppercase">Item Terdeteksi</p>
                            <h3 class="text-2xl font-black">{{ selectedPart.name }}</h3>
                            <p class="text-sm text-slate-500">Stok Saat Ini: <span class="font-bold">{{ selectedPart.stock }} Unit</span></p>
                            <p class="text-sm text-slate-500">Harga Modal Lama: <span class="font-bold text-blue-600">Rp {{ Number(selectedPart.buy_price).toLocaleString('id-ID') }}</span></p>
                        </div>

                        <form @submit.prevent="submitRestock" class="space-y-4 bg-slate-50 p-4 rounded-lg border">
                            <div>
                                <label class="text-xs font-bold uppercase">Jumlah Masuk (Qty)</label>
                                <Input v-model="form.incoming_qty" type="number" required class="bg-white" />
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase">Harga Beli Baru (per Unit)</label>
                                <Input v-model="form.new_buy_price" type="number" required class="bg-white" />
                            </div>
                            <Button class="w-full bg-green-600 hover:bg-green-700 h-12 text-lg" :disabled="form.processing">
                                <ArrowUpRight class="w-5 h-5 mr-2" /> {{ form.processing ? 'Menghitung...' : 'Update Stok & Harga' }}
                            </Button>
                        </form>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="isNewItem" class="border-orange-500 bg-orange-50/30 animate-in zoom-in">
                <CardHeader class="bg-orange-100">
                    <CardTitle class="text-orange-800 flex items-center gap-2">
                        <Plus class="w-5 h-5" /> Barang Baru! Barcode Tidak Terdaftar
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-6">
                    <p class="mb-4 text-sm text-orange-700 font-medium">
                        Barcode <span class="font-mono font-bold bg-white px-2 py-1 rounded border">{{ searchSku }}</span> belum ada di sistem. Lengkapi data di bawah untuk menyimpannya:
                    </p>
                    <form @submit.prevent="submitNewPart" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="text-xs font-bold uppercase">Nama Barang Baru</label>
                            <Input v-model="newPartForm.name" placeholder="Misal: Oli Shell Advance AX7" required />
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase">Harga Beli (Modal)</label>
                            <Input v-model="newPartForm.buy_price" type="number" required />
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase">Harga Jual</label>
                            <Input v-model="newPartForm.sell_price" type="number" required />
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase">Stok Awal</label>
                            <Input v-model="newPartForm.stock" type="number" required />
                        </div>
                        <div class="flex items-end">
                            <Button class="w-full bg-orange-600 hover:bg-orange-700">
                                Simpan & Daftarkan Barang
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>