<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { debounce } from 'lodash';
import { toast } from 'vue-sonner';

const props = defineProps<{
    foundVehicle: any;
    filters: any;
}>();

const plateSearch = ref(props.filters.plate || '');

// Search as you type
watch(plateSearch, debounce((value) => {
    router.get(route('work-orders.create'), { plate: value }, { 
        preserveState: true, 
        replace: true 
    });
}, 500));

const form = useForm({
    vehicle_id: '',
    driver_name: '',
    odometer: '',
    complaints: '',
});

// Pre-fill vehicle_id if found
watch(() => props.foundVehicle, (newVehicle) => {
    if (newVehicle) form.vehicle_id = newVehicle.id;
}, { immediate: true });

const submit = () => {
    form.post(route('work-orders.store'), {
        onSuccess: () => {
            // This will trigger the redirect defined in the Controller
            form.reset();
        },
        onError: (errors) => {
            console.log(errors);
            toast.error("Gagal membuat Work Order. Cek kembali form Anda.");
        }
    });
};
</script>

<template>
    <Head title="Check-In Kendaraan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Check-In Kendaraan</h2>
        </template>

        <div class="py-12 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <Card>
                <CardHeader><CardTitle>Cari Nomor Plat</CardTitle></CardHeader>
                <CardContent>
                    <Input 
                        v-model="plateSearch" 
                        placeholder="Masukkan Plat Nomor (Contoh: B 1234 ABC)" 
                        class="text-2xl h-16 text-center uppercase font-bold"
                    />
                </CardContent>
            </Card>

            <div v-if="foundVehicle" class="space-y-6">
                <Card class="bg-blue-50 border-blue-200">
                    <CardContent class="pt-6">
                        <div class="flex justify-between">
                            <div>
                                <p class="text-sm text-blue-600 font-bold uppercase">Pemilik Terdaftar</p>
                                <h3 class="text-lg font-bold">{{ foundVehicle.customer.name }}</h3>
                                <p>{{ foundVehicle.customer.phone }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-blue-600 font-bold uppercase">Kendaraan</p>
                                <p>{{ foundVehicle.brand }} {{ foundVehicle.model }} ({{ foundVehicle.plate_number }})</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle>Detail Kedatangan</CardTitle></CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <Label>Nama Pembawa (Driver)</Label>
                                <Input v-model="form.driver_name" placeholder="Siapa yang mengantar mobil?" />
                            </div>
                            <div>
                                <Label>Odometer (KM)</Label>
                                <Input v-model="form.odometer" type="number" placeholder="Contoh: 55000" />
                                <div v-if="form.errors.odometer" class="text-red-500 text-sm mt-1">{{ form.errors.odometer }}</div>
                            </div>
                            <div>
                                <Label>Keluhan / Instruksi</Label>
                                <textarea 
                                    v-model="form.complaints" 
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                    rows="3"
                                    placeholder="Cth: Ganti oli, cek rem bunyi decit"
                                ></textarea>
                            </div>
                            <Button 
                                type="submit" 
                                class="w-full h-12 text-lg" 
                                :disabled="form.processing">
                                <span v-if="form.processing">Memproses...</span>
                                <span v-else>Buat Work Order</span>
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <Card v-else-if="plateSearch.length > 3" class="text-center py-8">
                <CardContent>
                    <p class="text-slate-500 mb-4">Kendaraan dengan plat <strong>{{ plateSearch }}</strong> tidak ditemukan.</p>
                    <Button variant="outline">Daftarkan Kendaraan Baru</Button>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>