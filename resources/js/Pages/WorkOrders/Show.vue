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

        <div class="print-only hidden">
            <div class="text-center border-b pb-2 mb-2">
                <img v-if="shop.logo_path" 
                    :src=" shop.logo_path" 
                    class="h-12 mx-auto mb-1"/>
                <label>{{ shop.logo_path }}</label>
                
                <h1 class="text-xl font-bold uppercase">{{ shop.shop_name }}</h1>
                <p class="text-xs" v-if="shop.shop_address">{{ shop.shop_address }}</p>
                <p class="text-xs" v-if="shop.shop_phone">Telp: {{ shop.shop_phone }}</p>
            </div>
            <div class="text-center border-b pb-2 mb-2">
                <p class="text-sm">Work Order #{{ workOrder.id }}</p>
                <p class="text-xs">{{ new Date().toLocaleString('id-ID') }}</p>
            </div>

            <div class="space-y-2 mb-4">
                <div class="flex justify-between">
                    <span class="font-bold">PLAT:</span>
                    <span>{{ workOrder.vehicle.plate_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-bold">MOBIL:</span>
                    <span>{{ workOrder.vehicle.brand }} {{ workOrder.vehicle.model }}</span>
                </div>
                <div class="flex justify-between border-t pt-1">
                    <span class="font-bold">KM:</span>
                    <span>{{ workOrder.odometer || '-' }}</span>
                </div>
            </div>

            <div class="border-t pt-2">
                <p class="font-bold mb-1 uppercase text-sm underline text-center">Instruksi Mekanik</p>
                <ul class="space-y-1">
                    <li v-for="(item, index) in workOrder.items" :key="item.id" class="text-sm">
                        {{ Number(index) + 1 }}. [ ] {{ item.task_name }}
                    </li>
                </ul>
            </div>

            <div class="mt-6 border-t pt-2 text-xs italic">
                <p>Keluhan: {{ workOrder.complaints || '-' }}</p>
            </div>
            
            <div class="mt-8 flex justify-between text-[10px] uppercase">
                <div class="text-center border-t w-20">Admin</div>
                <div class="text-center border-t w-20">Mekanik</div>
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
    /* 1. Hide the entire App UI */
    nav, header, aside, .no-print, button, form, .py-12 {
        display: none !important;
    }

    /* 2. Show only the Print section */
    .print-only {
        display: block !important;
        width: 80mm; /* Perfect for standard Thermal Printers in Indonesia */
        margin: 0 auto;
        font-family: 'Courier New', Courier, monospace; /* Clean "Receipt" look */
        color: black !important;
    }

    /* 3. Reset body for printing */
    body, html {
        background: white !important;
        margin: 0;
        padding: 0;
    }
}
</style>