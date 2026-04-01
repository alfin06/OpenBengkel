<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Pencil, Trash2, X, Check } from 'lucide-vue-next'; // Nice icons for editing

const props = defineProps<{ workOrder: any }>();
// Access the page props safely
const page = usePage();

// Create a computed property so it stays reactive but has a fallback
const shop = computed(() => (page.props.shop as any) || {
    shop_name: 'OpenBengkel',
    shop_address: '',
    shop_phone: '',
    logo_path: null
});

// --- 1. Edit Check-in Info Logic ---
const isEditingInfo = ref(false);
const infoForm = useForm({
    driver_name: props.workOrder.driver_name || '',
    odometer: props.workOrder.odometer || '',
    complaints: props.workOrder.complaints || '',
});

const updateInfo = () => {
    infoForm.patch(route('work-orders.update', props.workOrder.id), {
        onSuccess: () => isEditingInfo.value = false,
    });
};

// --- 2. Add New Task Logic ---
const itemForm = useForm({
    task_name: '',
});

const addItem = () => {
    itemForm.post(route('work-orders.items.store', props.workOrder.id), {
        onSuccess: () => itemForm.reset(),
    });
};

// --- 3. Edit/Delete Existing Task Logic ---
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

const printJob = () => {
    window.print();
};
</script>

<template>
    <Head title="Kelola Work Order" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold">Work Order #{{ workOrder.id }}</h2>
                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-bold uppercase">
                    {{ workOrder.status }}
                </span>
            </div>
        </template>

        <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Card>
                    <CardHeader><CardTitle class="text-sm uppercase text-slate-500">Kendaraan</CardTitle></CardHeader>
                    <CardContent>
                        <p class="font-bold text-lg text-blue-600">{{ workOrder.vehicle.plate_number }}</p>
                        <p class="font-medium">{{ workOrder.vehicle.brand }} {{ workOrder.vehicle.model }}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0">
                        <CardTitle class="text-sm uppercase text-slate-500">Check-in Info</CardTitle>
                        <Button variant="ghost" size="sm" @click="isEditingInfo = !isEditingInfo">
                            <Pencil v-if="!isEditingInfo" class="w-4 h-4" />
                            <span v-else class="text-xs">Batal</span>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div v-if="!isEditingInfo" class="space-y-1">
                            <p><strong>Pembawa:</strong> {{ workOrder.driver_name || 'Owner' }}</p>
                            <p><strong>KM:</strong> {{ workOrder.odometer || '-' }}</p>
                            <p><strong>Keluhan:</strong> {{ workOrder.complaints || '-' }}</p>
                        </div>
                        <form v-else @submit.prevent="updateInfo" class="space-y-3">
                            <Input v-model="infoForm.driver_name" placeholder="Nama Pembawa" />
                            <Input v-model="infoForm.odometer" type="number" placeholder="KM Odometer" />
                            <textarea v-model="infoForm.complaints" class="w-full border rounded-md p-2 text-sm" rows="2" placeholder="Keluhan"></textarea>
                            <Button size="sm" class="w-full bg-blue-600">Simpan Perubahan</Button>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader><CardTitle>Instruksi Mekanik</CardTitle></CardHeader>
                <CardContent class="space-y-4">
                    <ul class="divide-y border rounded-md overflow-hidden">
                        <li v-for="item in workOrder.items" :key="item.id" class="p-4 flex justify-between items-center bg-white hover:bg-slate-50 group">
                            <div v-if="editingItemId !== item.id" class="flex items-center gap-3 flex-1">
                                <input 
                                    type="checkbox" 
                                    :checked="item.is_completed" 
                                    @change="router.patch(route('work-order-items.update', item.id))"
                                    class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                />
                                <span :class="{'line-through text-slate-400': item.is_completed}" class="text-lg">
                                    {{ item.task_name }}
                                </span>
                            </div>
                            
                            <div v-else class="flex items-center gap-2 flex-1">
                                <Input v-model="editItemForm.task_name" class="h-8" />
                                <Button size="sm" variant="outline" class="h-8 px-2 bg-green-50" @click="updateTaskName(item.id)">
                                    <Check class="w-4 h-4 text-green-600" />
                                </Button>
                                <Button size="sm" variant="outline" class="h-8 px-2 bg-red-50" @click="editingItemId = null">
                                    <X class="w-4 h-4 text-red-600" />
                                </Button>
                            </div>

                            <div v-if="editingItemId !== item.id" class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Button variant="ghost" size="sm" @click="startEditTask(item)">
                                    <Pencil class="w-4 h-4 text-slate-500" />
                                </Button>
                                <Button variant="ghost" size="sm" @click="deleteTask(item.id)">
                                    <Trash2 class="w-4 h-4 text-red-400" />
                                </Button>
                            </div>
                        </li>
                    </ul>

                    <form @submit.prevent="addItem" class="flex gap-2 pt-2">
                        <Input v-model="itemForm.task_name" placeholder="Tambah instruksi baru..." class="flex-1" />
                        <Button :disabled="itemForm.processing" class="bg-blue-600 text-white font-bold">
                            {{ itemForm.processing ? '...' : 'Tambah' }}
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <div class="flex justify-end gap-4">
                <Button 
                    variant="outline" 
                    class="border-blue-600 text-blue-600" 
                    @click="printJob()">
                    Print Job Slip
                </Button>

                <Button 
                    v-if="workOrder.status === 'Pending'"
                    class="bg-green-600 hover:bg-green-700 px-8"
                    @click="router.patch(route('work-orders.start', workOrder.id))">
                    Mulai Pengerjaan
                </Button>

                <Button 
                    v-else-if="workOrder.status === 'Proses'"
                    class="bg-blue-600 hover:bg-blue-700 px-8"
                    @click="router.get(route('invoices.create', workOrder.id))">
                    Selesaikan & Buat Invoice
                </Button>
            </div>
        </div>

        <div class="print-only">
            <div class="flex items-center gap-6 border-b-4 border-black pb-4 mb-4">
                <div v-if="shop.logo_path" class="w-1/4">
                    <img :src="shop.logo_path" class="h-20 w-auto object-contain" />
                </div>
                
                <div class="flex-1 text-left">
                    <h1 class="text-3xl font-bold uppercase tracking-tight">{{ shop.shop_name }}</h1>
                    <p class="text-sm font-mono mt-1" v-if="shop.shop_address">
                        {{ shop.shop_address }}
                    </p>
                    <p class="text-sm font-mono font-bold" v-if="shop.shop_phone">
                        Telp: {{ shop.shop_phone }}
                    </p>
                </div>
            </div>

            <div class="flex justify-between mb-4">
                <h2 class="text-xl font-bold underline">WORK ORDER / PERINTAH KERJA</h2>
                <div class="text-right font-mono">
                    <p>NO: #{{ workOrder.id }}</p>
                    <p>TGL: {{ new Date().toLocaleDateString('id-ID') }}</p>
                </div>
            </div>

            <table class="w-full mb-6 border-2 border-black border-collapse font-mono">
                <tbody>
                    <tr>
                        <td class="border-2 border-black p-2 w-1/2"><strong>PLAT:</strong> {{ workOrder.vehicle.plate_number }}</td>
                        <td class="border-2 border-black p-2 w-1/2"><strong>PEMBAWA:</strong> {{ workOrder.driver_name || 'Owner' }}</td>
                    </tr>
                    <tr>
                        <td class="border-2 border-black p-2"><strong>MOBIL:</strong> {{ workOrder.vehicle.brand }} {{ workOrder.vehicle.model }}</td>
                        <td class="border-2 border-black p-2"><strong>KM:</strong> {{ workOrder.odometer }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="mb-4 font-mono">
                <p class="font-bold underline mb-2">INSTRUKSI MEKANIK:</p>
                <div class="min-h-[300px] border-2 border-black p-4">
                    <ul class="space-y-2">
                        <li v-for="(item, index) in workOrder.items" :key="item.id" class="text-lg">
                            {{ Number(index) + 1 }}. [ ] {{ item.task_name }}
                        </li>
                    </ul>
                </div>
            </div>

            <p class="text-sm italic mb-10">Keluhan: {{ workOrder.complaints || '-' }}</p>

            <!-- <div class="grid grid-cols-3 gap-8 text-center mt-auto font-mono">
                <div>
                    <p class="mb-16">Hormat Kami,</p>
                    <div class="border-t border-black pt-2">( Admin )</div>
                </div>
                <div>
                    <p class="mb-16">Mekanik,</p>
                    <div class="border-t border-black pt-2">( ___________ )</div>
                </div>
                <div>
                    <p class="mb-16">Pelanggan,</p>
                    <div class="border-t border-black pt-2">( {{ workOrder.driver_name || 'Owner' }} )</div>
                </div>
            </div> -->
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