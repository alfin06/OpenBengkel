<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { 
    Dialog, DialogContent, DialogHeader, 
    DialogTitle, DialogTrigger, DialogFooter 
} from '@/Components/ui/dialog';

const props = defineProps<{ customer: any }>();

const isVehicleModalOpen = ref(false);

const vehicleForm = useForm({
    plate_number: '',
    brand: '',
    model: '',
    year: '',
});

const submitVehicle = () => {
    vehicleForm.post(route('vehicles.store', props.customer.id), {
        onSuccess: () => {
            isVehicleModalOpen.value = false;
            vehicleForm.reset();
        },
    });
};
</script>

<template>
    <Head :title="customer.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold">{{ customer.name }}</h2>
                <Link :href="route('customers.index')">
                    <Button variant="outline">Kembali</Button>
                </Link>
            </div>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <Card>
                <CardHeader><CardTitle>Informasi Pelanggan</CardTitle></CardHeader>
                <CardContent>
                    <p><strong>WhatsApp:</strong> {{ customer.phone }}</p>
                    <p><strong>Alamat:</strong> {{ customer.address || '-' }}</p>
                </CardContent>
            </Card>

            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium">Daftar Kendaraan</h3>
                <Dialog v-model:open="isVehicleModalOpen">
                    <DialogTrigger as-child>
                        <Button>+ Tambah Mobil</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Tambah Kendaraan untuk {{ customer.name }}</DialogTitle>
                        </DialogHeader>
                        <form @submit.prevent="submitVehicle" class="space-y-4 pt-4">
                            <div>
                                <Label for="plate">Nomor Plat</Label>
                                <Input id="plate" v-model="vehicleForm.plate_number" placeholder="Contoh: B 1234 ABC" class="uppercase" />
                                <div v-if="vehicleForm.errors.plate_number" class="text-red-500 text-sm">{{ vehicleForm.errors.plate_number }}</div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label for="brand">Merek</Label>
                                    <Input id="brand" v-model="vehicleForm.brand" placeholder="Toyota / Honda" />
                                </div>
                                <div>
                                    <Label for="model">Model/Tipe</Label>
                                    <Input id="model" v-model="vehicleForm.model" placeholder="Avanza / Civic" />
                                </div>
                            </div>
                            <div>
                                <Label for="year">Tahun</Label>
                                <Input id="year" type="number" v-model="vehicleForm.year" placeholder="2022" />
                            </div>
                            <DialogFooter>
                                <Button type="submit" :disabled="vehicleForm.processing">Simpan Kendaraan</Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Card v-for="vehicle in customer.vehicles" :key="vehicle.id">
                    <CardHeader>
                        <CardTitle class="text-blue-600">{{ vehicle.plate_number }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p>{{ vehicle.brand }} {{ vehicle.model }} ({{ vehicle.year }})</p>
                        <Button class="mt-4 w-full" variant="secondary">Riwayat Servis</Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>