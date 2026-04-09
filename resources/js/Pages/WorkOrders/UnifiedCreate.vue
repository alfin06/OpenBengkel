<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Car, User, ClipboardList, Search } from 'lucide-vue-next';
import axios from 'axios';
import debounce from 'lodash/debounce';

const form = useForm({
    // Vehicle Info
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

const checkVehicle = debounce(async (plate) => {
    if (plate.length < 1) {
        resetFormFields();
        isExistingVehicle.value = false;
        return;
    }
    
    isChecking.value = true; // Start loading
    try {
        const response = await axios.get(route('api.vehicles.search'), { params: { plate } });

        if (response.data && Object.keys(response.data).length > 0) {
            const v = response.data;
            form.brand = v.brand;
            form.model = v.model;
            form.color = v.color || '';
            form.year = v.year || '';
            form.customer_name = v.customer?.name || '';
            form.phone_1 = v.customer?.phone_1 || '';
            form.address = v.customer?.address || '';
            isExistingVehicle.value = true;
        } else {
            resetFormFields();
            isExistingVehicle.value = false;
        }
    } catch (e) {
        isExistingVehicle.value = false;
    } finally {
        isChecking.value = false; // Stop loading
    }
}, 500);

// Helper function to clear fields so the staff starts fresh
const resetFormFields = () => {
    form.brand = '';
    form.model = '';
    form.color = '';
    form.year = '';
    form.customer_name = '';
    form.phone_1 = '';
    form.address = '';
};

watch(() => form.plate_number, (newVal) => {
    // If user starts typing again, assume they might be looking for a different car
    // and hide the success message until the search confirms it.
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
                            <p v-if="isExistingVehicle" class="text-[10px] text-blue-600 font-bold mt-1 uppercase">✨ Kendaraan terdaftar ditemukan.</p>
                        </div>
                        <div>
                            <Label>Merek</Label>
                            <Input v-model="form.brand" placeholder="Toyota / Honda" required />
                        </div>
                        <div>
                            <Label>Model / Type</Label>
                            <Input v-model="form.model" placeholder="Innova / Vario" required />
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-2 border-slate-200">
                    <CardHeader class="bg-slate-50 border-b">
                        <CardTitle class="text-sm flex items-center gap-2"><User class="w-4 h-4" /> DATA PEMILIK</CardTitle>
                    </CardHeader>
                    <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-6">
                        <div class="md:col-span-1">
                            <Label>Nama Pelanggan</Label>
                            <Input v-model="form.customer_name" required />
                            <p v-if="form.errors.customer_name" class="text-xs text-red-500 mt-1">{{ form.errors.customer_name }}</p>
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
                                <Label>Nama Pembawa (Jika bukan pemilik)</Label>
                                <Input v-model="form.driver_name" placeholder="Driver / Istri / Anak" />
                            </div>
                        </div>
                        <div>
                            <Label>Keluhan / Instruksi Kerja</Label>
                            <textarea v-model="form.complaints" class="w-full h-32 border rounded-md p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Cth: Ganti oli, cek rem bunyi decit, servis rutin..."></textarea>
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