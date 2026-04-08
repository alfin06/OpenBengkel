<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { toast } from 'vue-sonner';
import { 
    Dialog, DialogContent, DialogHeader, 
    DialogTitle, DialogTrigger, DialogFooter 
} from '@/Components/ui/dialog';
import { Pencil, Trash2, History, Car, UserCircle, ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{ customer: any }>();

// --- Modals State ---
const isVehicleModalOpen = ref(false);
const isEditVehicleModalOpen = ref(false);
const isEditCustomerModalOpen = ref(false);

// --- Create Vehicle Form ---
const vehicleForm = useForm({
    plate_number: '',
    brand: '',
    model: '',
    color: '',
    year: '',
});

const submitVehicle = () => {
    vehicleForm.post(route('vehicles.store', props.customer.id), {
        onSuccess: () => {
            isVehicleModalOpen.value = false;
            vehicleForm.reset();
            toast.success('Kendaraan berhasil ditambahkan!');
        },
    });
};

// --- Edit Customer Logic ---
const editCustomerForm = useForm({
    name: props.customer.name,
    phone_1: props.customer.phone_1 || '',
    phone_2: props.customer.phone_2 || '',
    phone_3: props.customer.phone_3 || '',
    address: props.customer.address || '',
    notes: props.customer.notes || '',
});

const updateCustomer = () => {
    editCustomerForm.put(route('customers.update', props.customer.id), {
        onSuccess: () => {
            isEditCustomerModalOpen.value = false;
            toast.success('Profil pelanggan berhasil diperbarui!');
        },
    });
};

// --- Edit Vehicle Logic ---
const editVehicleForm = useForm({
    id: null as number | null,
    plate_number: '',
    brand: '',
    model: '',
    color: '',
    year: '',
});

const openEditVehicleModal = (vehicle: any) => {
    editVehicleForm.id = vehicle.id;
    editVehicleForm.plate_number = vehicle.plate_number;
    editVehicleForm.brand = vehicle.brand;
    editVehicleForm.model = vehicle.model;
    editVehicleForm.color = vehicle.color;
    editVehicleForm.year = vehicle.year;
    isEditVehicleModalOpen.value = true; 
};

const updateVehicle = () => {
    if (!editVehicleForm.id) return;
    editVehicleForm.put(route('vehicles.update', editVehicleForm.id), {
        onSuccess: () => {
            isEditVehicleModalOpen.value = false;
            toast.success('Data mobil berhasil diperbarui!');
        },
        onError: () => toast.error("Gagal memperbarui data mobil."),
    });
};

const deleteVehicle = (id: number) => {
    if (confirm('Hapus mobil ini? Data history servis akan tetap aman di database (Soft Delete).')) {
        router.delete(route('vehicles.destroy', id), {
            onSuccess: () => toast.success('Mobil berhasil dinonaktifkan'),
        });
    }
};
</script>

<template>
    <Head :title="customer.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-100 p-2 rounded-lg">
                        <UserCircle class="w-6 h-6 text-blue-600" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-slate-800">{{ customer.name }}</h2>
                            <Button variant="ghost" size="icon" @click="isEditCustomerModalOpen = true" class="h-8 w-8 text-slate-400 hover:text-blue-600">
                                <Pencil class="w-4 h-4" />
                            </Button>
                        </div>
                        <p class="text-xs text-slate-500 italic">ID Pelanggan: #{{ customer.id }}</p>
                    </div>
                </div>
                <Link :href="route('customers.index')">
                    <Button variant="outline"><ArrowLeft class="w-4 h-4 mr-2" /> Kembali</Button>
                </Link>
            </div>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <Card class="border-none shadow-sm bg-white overflow-hidden">
                <div class="bg-slate-50 px-6 py-3 border-b flex justify-between items-center">
                    <span class="text-xs font-bold uppercase tracking-widest text-slate-500">Detail Kontak</span>
                </div>
                <CardContent class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
                    <div>
                        <Label class="text-[10px] uppercase font-bold text-slate-400">No. Telp 1</Label>
                        <p class="font-mono font-bold text-lg text-blue-700">{{ customer.phone_1 || '-' }}</p>
                    </div>
                    <div>
                        <Label class="text-[10px] uppercase font-bold text-slate-400">No. Telp 2</Label>
                        <p class="font-mono font-bold text-lg text-blue-700">{{ customer.phone_2 || '-' }}</p>
                    </div>
                    <div>
                        <Label class="text-[10px] uppercase font-bold text-slate-400">No. Telp 3</Label>
                        <p class="font-mono font-bold text-lg text-blue-700">{{ customer.phone_3 || '-' }}</p>
                    </div>
                    <div>
                        <Label class="text-[10px] uppercase font-bold text-slate-400">Alamat</Label>
                        <p class="text-slate-600 truncate" :title="customer.address">{{ customer.address || '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <Label class="text-[10px] uppercase font-bold text-amber-600">Catatan Khusus</Label>
                        <div class="mt-1 p-3 bg-amber-50 border border-amber-100 rounded-lg text-sm text-amber-900 italic">
                            {{ customer.notes || '' }}
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="flex justify-between items-center border-b pb-4 mt-12">
                <div class="flex items-center gap-2">
                    <Car class="w-5 h-5 text-slate-400" />
                    <h3 class="text-lg font-bold text-slate-800">Daftar Kendaraan</h3>
                </div>
                
                <Dialog v-model:open="isVehicleModalOpen">
                    <DialogTrigger as-child>
                        <Button class="bg-blue-600 hover:bg-blue-700 font-bold">+ Tambah Mobil</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader><DialogTitle>Daftarkan Mobil Baru</DialogTitle></DialogHeader>
                        <form @submit.prevent="submitVehicle" class="space-y-4 pt-4">
                            <div>
                                <Label>Nomor Plat</Label>
                                <Input v-model="vehicleForm.plate_number" placeholder="H 1234 AB" class="uppercase font-bold" required />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <Input v-model="vehicleForm.brand" placeholder="Merek (Toyota)" required />
                                <Input v-model="vehicleForm.model" placeholder="Model (Innova)" required />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <Input v-model="vehicleForm.color" placeholder="Warna" />
                                <Input v-model="vehicleForm.year" type="number" placeholder="Tahun" />
                            </div>
                            <DialogFooter>
                                <Button type="submit" class="w-full" :disabled="vehicleForm.processing">Simpan Kendaraan</Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card v-for="vehicle in customer.vehicles" :key="vehicle.id" class="border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <CardHeader class="pb-2 flex flex-row items-center justify-between space-y-0">
                        <div class="px-3 py-1 bg-slate-900 text-white rounded font-mono font-bold tracking-widest uppercase">
                            {{ vehicle.plate_number }}
                        </div>
                        <div class="flex gap-1">
                            <Button variant="ghost" size="icon" @click="openEditVehicleModal(vehicle)" class="h-7 w-7 text-slate-400">
                                <Pencil class="w-3.5 h-3.5" />
                            </Button>
                            <Button variant="ghost" size="icon" @click="deleteVehicle(vehicle.id)" class="h-7 w-7 text-slate-400 hover:text-red-600">
                                <Trash2 class="w-3.5 h-3.5" />
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="pt-4">
                        <div class="text-lg font-black text-slate-800">{{ vehicle.brand }} {{ vehicle.model }}</div>
                        <p class="text-sm text-slate-500 uppercase font-medium">{{ vehicle.color || '-' }} • {{ vehicle.year || '-' }}</p>
                        
                        <div class="mt-6 flex gap-2">
                            <Link :href="route('vehicles.show', vehicle.id)" class="flex-1">
                                <Button variant="outline" size="sm" class="w-full text-xs gap-1">
                                    <History class="w-3 h-3" /> Riwayat Servis
                                </Button>
                            </Link>
                            <Link :href="route('work-orders.create', { vehicle_id: vehicle.id })" class="flex-1">
                                <Button size="sm" class="w-full bg-blue-600 hover:bg-blue-700 text-xs font-bold uppercase tracking-tighter">
                                    Check-In
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <div v-if="customer.vehicles.length === 0" class="col-span-full py-12 text-center border-2 border-dashed rounded-xl bg-slate-50 text-slate-400 italic">
                    Belum ada kendaraan terdaftar.
                </div>
            </div>

            <Dialog v-model:open="isEditCustomerModalOpen">
                <DialogContent>
                    <DialogHeader><DialogTitle>Edit Profil Pelanggan</DialogTitle></DialogHeader>
                    <form @submit.prevent="updateCustomer" class="space-y-4 pt-4">
                        <div>
                            <Label>Nama Lengkap</Label>
                            <Input v-model="editCustomerForm.name" required />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label>No. Telp 1</Label>
                                <Input v-model="editCustomerForm.phone_1" required />
                            </div>
                            <div>
                                <Label>No. Telp 2</Label>
                                <Input v-model="editCustomerForm.phone_2" />
                            </div>
                            <div>
                                <Label>No. Telp 3</Label>
                                <Input v-model="editCustomerForm.phone_3" />
                            </div>
                        </div>
                        <div>
                            <Label>Alamat</Label>
                            <Input v-model="editCustomerForm.address" />
                        </div>
                        <div>
                            <Label>Catatan (Notes)</Label>
                            <textarea 
                                v-model="editCustomerForm.notes" 
                                class="w-full min-h-[100px] rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                placeholder=""
                            ></textarea>
                        </div>
                        <DialogFooter>
                            <Button type="submit" class="w-full bg-blue-600" :disabled="editCustomerForm.processing">Simpan Perubahan Profil</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog v-model:open="isEditVehicleModalOpen">
                <DialogContent>
                    <DialogHeader><DialogTitle>Edit Data Kendaraan</DialogTitle></DialogHeader>
                    <form @submit.prevent="updateVehicle" class="space-y-4 pt-4">
                        <div>
                            <Label>Nomor Plat</Label>
                            <Input v-model="editVehicleForm.plate_number" class="uppercase font-bold" required />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <Input v-model="editVehicleForm.brand" placeholder="Merek" required />
                            <Input v-model="editVehicleForm.model" placeholder="Model" required />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <Input v-model="editVehicleForm.color" placeholder="Warna" />
                            <Input v-model="editVehicleForm.year" type="number" placeholder="Tahun" />
                        </div>
                        <DialogFooter>
                            <Button type="submit" class="w-full bg-blue-600" :disabled="editVehicleForm.processing">Simpan Perubahan Mobil</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>