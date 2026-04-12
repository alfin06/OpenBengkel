<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Car, User, ClipboardList, Search, CheckCircle2 } from 'lucide-vue-next';
import axios from 'axios';
import debounce from 'lodash/debounce';

const form = useForm({
    // Vehicle Info
    customer_id: null, // Tracks if we are using an existing customer
    plate_number: '',
    brand: '',
    model: '',
    color: '',
    year: '',
    // Customer Info
    customer_name: '',
    phone_1: '',
    address: '',
    // WO Info
    driver_name: '',
    odometer: '',
    complaints: '',
});

const isExistingVehicle = ref(false);
const isChecking = ref(false);

const customerSearchResults = ref([]);
const showCustomerDropdown = ref(false);

const checkVehicle = debounce(async (plate) => {
    if (plate.length < 1) {
        resetFormFields();
        isExistingVehicle.value = false;
        return;
    }
    
    isChecking.value = true;
    try {
        const response = await axios.get(route('api.vehicles.search'), { params: { plate } });

        if (response.data && Object.keys(response.data).length > 0) {
            const v = response.data;
            form.customer_id = v.customer?.id || null;
            form.brand = v.brand;
            form.model = v.model;
            form.color = v.color || '';
            form.year = v.year || '';
            form.customer_name = v.customer?.name || '';
            form.phone_1 = v.customer?.phone_1 || '';
            form.address = v.customer?.address || '';
            isExistingVehicle.value = true;
        } else {
            // If vehicle is new, we reset fields but keep the plate
            resetFormFields();
            isExistingVehicle.value = false;
        }
    } catch (e) {
        isExistingVehicle.value = false;
    } finally {
        isChecking.value = false;
    }
}, 500);

// Function to search for existing customers if the car is new
const searchCustomer = debounce(async (query) => {
    // Only search if the vehicle doesn't exist (it's a new car)
    if (isExistingVehicle.value || query.length < 3) {
        showCustomerDropdown.value = false;
        return;
    }

    try {
        const response = await axios.get(route('api.customers.search'), { params: { q: query } });
        customerSearchResults.value = response.data;
        showCustomerDropdown.value = true;
    } catch (e) {
        const error = e as any;
        console.error("Customer search failed:", error.response?.data || error.message);
    }
}, 300);

const selectCustomer = (c) => {
    form.customer_id = c.id;
    form.customer_name = c.name;
    form.phone_1 = c.phone_1;
    form.address = c.address;
    showCustomerDropdown.value = false;
    customerSearchResults.value = [];
};

const resetFormFields = () => {
    form.customer_id = null;
    form.brand = '';
    form.model = '';
    form.color = '';
    form.year = '';
    form.customer_name = '';
    form.phone_1 = '';
    form.address = '';
};

watch(() => form.plate_number, (newVal) => {
    if (newVal.length > 0) {
        isExistingVehicle.value = false;
    }
    checkVehicle(newVal);
});

const submit = () => {
    form.post(route('work-orders.store-unified'));
};
</script>

<template>
    <Head title="Pendaftaran Servis Baru" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl uppercase tracking-widest">Pendaftaran Servis</h2>
        </template>

        <div class="py-12 max-w-4xl mx-auto px-4 space-y-6">
            <form @submit.prevent="submit" class="space-y-6">
                
                <Card class="border-2 border-slate-200">
                    <CardHeader class="bg-slate-50 border-b">
                        <CardTitle class="text-sm flex items-center gap-2"><Car class="w-4 h-4" /> INFO KENDARAAN</CardTitle>
                    </CardHeader>
                    <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-6">
                        <div class="md:col-span-2">
                            <Label>Nomor Plat</Label>
                            <Input v-model="form.plate_number" placeholder="Cth: H 1234 AB" class="text-xl font-black uppercase" required />
                            <p v-if="form.errors.plate_number" class="text-xs text-red-500 mt-1">{{ form.errors.plate_number }}</p>
                            <div v-if="isExistingVehicle" class="flex items-center gap-1 text-[10px] text-blue-600 font-bold mt-1 uppercase">
                                <CheckCircle2 class="w-3 h-3" /> Kendaraan terdaftar ditemukan.
                            </div>
                        </div>
                        <div>
                            <Label>Merek</Label>
                            <Input v-model="form.brand" placeholder="Toyota / Honda" required />
                        </div>
                        <div>
                            <Label>Model / Type</Label>
                            <Input v-model="form.model" placeholder="Innova / Brio" required />
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-2 border-slate-200 overflow-visible">
                    <CardHeader class="bg-slate-50 border-b">
                        <CardTitle class="text-sm flex items-center gap-2"><User class="w-4 h-4" /> DATA PEMILIK</CardTitle>
                    </CardHeader>
                    <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-6">
                        <div class="md:col-span-1 relative">
                            <Label>Nama Pelanggan</Label>
                            <div class="relative">
                                <Input 
                                    v-model="form.customer_name" 
                                    @input="searchCustomer(form.customer_name)"
                                    @focus="searchCustomer(form.customer_name)"
                                    placeholder="Ketik nama untuk cari pelanggan..."
                                    required 
                                />
                                <div v-if="showCustomerDropdown && customerSearchResults.length > 0" 
                                     class="absolute z-50 w-full bg-white border border-slate-200 shadow-2xl rounded-xl mt-1 max-h-60 overflow-y-auto">
                                    <div v-for="c in customerSearchResults" :key="c.id" 
                                         @click="selectCustomer(c)"
                                         class="p-3 hover:bg-blue-50 cursor-pointer border-b border-slate-50 last:border-0 transition-colors">
                                        <p class="font-bold text-sm text-slate-800">{{ c.name }}</p>
                                        <p class="text-[10px] text-slate-500">{{ c.phone_1 }} • {{ c.address }}</p>
                                    </div>
                                </div>
                            </div>
                            <p v-if="form.customer_id && !isExistingVehicle" class="text-[10px] text-green-600 font-bold mt-1 uppercase flex items-center gap-1">
                                <CheckCircle2 class="w-3 h-3" /> Menggunakan data pelanggan lama
                            </p>
                        </div>
                        <div>
                            <Label>No. WhatsApp</Label>
                            <Input v-model="form.phone_1" placeholder="0812..." />
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-2 border-blue-500 shadow-lg shadow-blue-50">
                    <CardHeader class="bg-blue-600 text-white border-b">
                        <CardTitle class="text-sm flex items-center gap-2"><ClipboardList class="w-4 h-4" /> DETAIL SERVIS</CardTitle>
                    </CardHeader>
                    <CardContent class="grid grid-cols-1 gap-4 pt-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label>Kilometer (Odo)</Label>
                                <Input v-model="form.odometer" type="number" placeholder="0" class="font-mono font-bold" />
                            </div>
                            <div>
                                <Label>Nama Pembawa</Label>
                                <Input v-model="form.driver_name" placeholder="Driver / Istri / Anak" />
                            </div>
                        </div>
                        <div>
                            <Label>Keluhan / Instruksi Kerja</Label>
                            <textarea v-model="form.complaints" class="w-full h-32 border rounded-md p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Cth: Ganti oli, cek rem bunyi decit..."></textarea>
                        </div>
                    </CardContent>
                </Card>

                <Button type="submit" class="w-full h-16 text-lg font-black uppercase tracking-widest bg-blue-600 hover:bg-blue-700 shadow-xl" :disabled="form.processing">
                    {{ form.processing ? 'Memproses...' : 'Daftarkan Servis & Mulai' }}
                </Button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>