<script setup lang="ts">
import { ref, nextTick, watch } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Package, Wrench, Plus, AlertTriangle } from 'lucide-vue-next';

const props = defineProps<{ parts: any[] }>();

const showAddForm = ref(false);
const skuInputRef = ref<any>(null);

const form = useForm({
    name: '',
    sku: '',
    buy_price: 0,
    sell_price: 0,
    stock: 0,
    type: 'Part',
});

// Auto-focus SKU field when form opens
watch(showAddForm, async (newValue) => {
    if (newValue) {
        await nextTick();
        // Focus the SKU input so the brother can just start "zapping"
        const input = document.getElementById('sku-field');
        input?.focus();
    }
});

const handleSkuEnter = () => {
    // After scanning, jump to Name field so he doesn't need the mouse
    document.getElementById('name-field')?.focus();
};

const submit = () => {
    form.post(route('parts.store'), {
        onSuccess: () => {
            form.reset();
            showAddForm.value = false;
        },
    });
};

// Helper to format Rupiah
const formatIDR = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};
</script>

<template>
    <Head title="Inventory Sparepart" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Inventory & Jasa</h2>
                <Button @click="showAddForm = !showAddForm" :class="showAddForm ? 'bg-slate-500' : 'bg-blue-600'">
                    <Plus v-if="!showAddForm" class="w-4 h-4 mr-2" /> 
                    {{ showAddForm ? 'Batal' : 'Tambah Item' }}
                </Button>
            </div>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <Card v-if="showAddForm" class="border-blue-200 bg-blue-50/50 shadow-sm">
                <CardHeader><CardTitle class="text-blue-700">Tambah Item Baru</CardTitle></CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-1">
                            <label class="text-[10px] font-bold uppercase text-slate-500">SKU / Barcode (Scan Here)</label>
                            <Input 
                                id="sku-field"
                                v-model="form.sku" 
                                placeholder="Zap barcode..." 
                                @keydown.enter.prevent="handleSkuEnter"
                            />
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-[10px] font-bold uppercase text-slate-500">Nama Barang / Jasa</label>
                            <Input id="name-field" v-model="form.name" placeholder="Contoh: Oli Shell Helix 10W-40" required />
                        </div>
                        
                        <div>
                            <label class="text-[10px] font-bold uppercase text-slate-500">Harga Beli (Modal)</label>
                            <Input v-model="form.buy_price" type="number" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase text-slate-500">Harga Jual</label>
                            <Input v-model="form.sell_price" type="number" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase text-slate-500">Stok Awal</label>
                            <Input v-model="form.stock" type="number" :disabled="form.type === 'Service'" />
                        </div>
                        
                        <div class="flex items-end gap-2 md:col-span-3">
                            <div class="flex-1">
                                <label class="text-[10px] font-bold uppercase text-slate-500">Tipe Item</label>
                                <select v-model="form.type" class="w-full border border-input rounded-md px-3 py-2 h-10 text-sm bg-white">
                                    <option value="Part">Sparepart (Fisik)</option>
                                    <option value="Service">Jasa (Service)</option>
                                </select>
                            </div>
                            <Button class="bg-green-600 h-10 px-8" :disabled="form.processing">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan ke Database' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <Card class="overflow-hidden shadow-sm">
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-100 border-b">
                                    <th class="p-4 text-xs uppercase text-slate-500 font-bold">Item & SKU</th>
                                    <th class="p-4 text-xs uppercase text-slate-500 font-bold">Tipe</th>
                                    <th class="p-4 text-xs uppercase text-slate-500 font-bold">Status Stok</th>
                                    <th class="p-4 text-xs uppercase text-slate-500 font-bold text-right">Harga Jual</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="part in parts" :key="part.id" class="hover:bg-blue-50/30 transition-colors">
                                    <td class="p-4">
                                        <div class="font-bold text-slate-900">{{ part.name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono tracking-wider">{{ part.sku || 'N/A' }}</div>
                                    </td>
                                    <td class="p-4">
                                        <span :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase', 
                                            part.type === 'Part' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700']">
                                            <Package v-if="part.type === 'Part'" class="w-3 h-3" />
                                            <Wrench v-else class="w-3 h-3" />
                                            {{ part.type }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div v-if="part.type === 'Part'" :class="['text-sm', part.stock <= 5 ? 'text-red-600 font-black' : 'text-slate-700']">
                                            {{ part.stock }} Unit
                                            <AlertTriangle v-if="part.stock <= 5" class="inline w-3 h-3 ml-1 animate-pulse" />
                                        </div>
                                        <div v-else class="text-slate-300 text-xs italic">Unlimited (Service)</div>
                                    </td>
                                    <td class="p-4 text-right font-mono font-bold text-blue-700">
                                        {{ formatIDR(part.sell_price) }}
                                    </td>
                                </tr>
                                <tr v-if="parts.length === 0">
                                    <td colspan="4" class="p-12 text-center text-slate-400 italic">
                                        Belum ada data barang. Klik "Tambah Item" untuk memulai.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>