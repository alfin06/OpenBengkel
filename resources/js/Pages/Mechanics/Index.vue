<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Users, Trash2, Edit, Wrench, FileText, Upload, X } from 'lucide-vue-next';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/Components/ui/dialog';
import { ref } from 'vue';

const props = defineProps<{
    mechanics: any[];
}>();

const isEditing = ref(false);
const editId = ref<number | null>(null);

const isKtpModalOpen = ref(false);
const selectedMechanic = ref<any>(null);

const form = useForm({
    name: '',
    phone: '',
    is_active: true,
    nik: ''
});

// Form for KTP upload
const ktpForm = useForm({
    _method: 'put',
    ktp_file: null as File | null,
});

const submit = () => {
    if (isEditing.value && editId.value) {
        form.put(route('mechanics.update', editId.value), {
            onSuccess: () => cancelEdit(),
        });
    } else {
        form.post(route('mechanics.store'), {
            onSuccess: () => form.reset(),
        });
    }
};

const openKtpModal = (mechanic: any) => {
    selectedMechanic.value = mechanic;
    isKtpModalOpen.value = true;
};

const uploadKtp = () => {
    ktpForm.post(route('mechanics.update', selectedMechanic.value.id), {
        onSuccess: () => {
            isKtpModalOpen.value = false;
            ktpForm.reset();
        },
    });
};

const editMechanic = (mechanic: any) => {
    isEditing.value = true;
    editId.value = mechanic.id;
    form.name = mechanic.name;
    form.phone = mechanic.phone || '';
    form.is_active = mechanic.is_active;
    form.nik = mechanic.nik || '';
};

const cancelEdit = () => {
    isEditing.value = false;
    editId.value = null;
    form.reset();
};

const deleteMechanic = (id: number) => {
    if (confirm('Yakin ingin menghapus mekanik ini? Data servis lamanya akan tetap aman.')) {
        router.delete(route('mechanics.destroy', id));
    }
};
</script>

<template>
    <Head title="Data Mekanik" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-slate-800 leading-tight uppercase tracking-widest">
                Tim Mekanik
            </h2>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-1 space-y-6">
                    <Card class="border-2 shadow-sm border-slate-200">
                        <CardHeader class="bg-slate-50 border-b pb-4">
                            <CardTitle class="text-sm font-bold uppercase tracking-widest text-slate-700 flex items-center gap-2">
                                <Wrench class="w-4 h-4 text-slate-500" /> 
                                {{ isEditing ? 'Edit Data Mekanik' : 'Tambah Mekanik Baru' }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="pt-6">
                            <form @submit.prevent="submit" class="space-y-4">
                                <div>
                                    <Label>Nama Lengkap</Label>
                                    <Input v-model="form.name" required />
                                </div>
                                <div>
                                    <Label>No. WhatsApp</Label>
                                    <Input v-model="form.phone" />
                                </div>
                                <div>
                                    <Label>NIK (16 Digit)</Label>
                                    <Input 
                                        id="nik" 
                                        v-model="form.nik" 
                                        :class="{'border-red-500 ring-red-100': form.errors.nik}"
                                        placeholder="Masukkan 16 digit NIK" 
                                        maxlength="16"
                                    />
                                    <p v-if="form.errors.nik" class="text-[11px] font-bold text-red-600 italic">
                                        * {{ form.errors.nik }}
                                    </p>
                                </div>
                                <div>
                                    <Label>Status</Label>
                                    <select v-model="form.is_active" class="mt-1 w-full rounded-md border border-slate-300 text-sm p-2">
                                        <option :value="true">Aktif</option>
                                        <option :value="false">Nonaktif</option>
                                    </select>
                                </div>
                                <div class="pt-2 flex gap-2">
                                    <Button type="submit" class="w-full bg-blue-600 font-bold uppercase tracking-widest">
                                        {{ isEditing ? 'Simpan' : 'Tambah' }}
                                    </Button>
                                    <Button v-if="isEditing" type="button" variant="outline" @click="cancelEdit">Batal</Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <Card class="border-2 shadow-sm border-slate-200">
                        <CardHeader class="bg-slate-50 border-b pb-4">
                            <CardTitle class="text-sm font-bold uppercase tracking-widest text-slate-700 flex items-center gap-2">
                                <Users class="w-4 h-4 text-slate-500" /> Daftar Anggota Tim
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-0">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 border-b text-[10px] uppercase text-slate-500 tracking-wider">
                                    <tr>
                                        <th class="p-4 text-left font-bold">Nama Mekanik</th>
                                        <th class="p-4 text-left font-bold">Kontak</th>
                                        <th class="p-4 text-center font-bold">Total History Servis</th>
                                        <th class="p-4 text-center font-bold">Status</th>
                                        <th class="p-4 w-20">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="mechanic in mechanics" :key="mechanic.id" class="hover:bg-slate-50 transition-colors">
                                        <td class="p-4 font-bold text-slate-800">{{ mechanic.name }}</td>
                                        <td class="p-4 text-slate-600">{{ mechanic.phone || '-' }}</td>
                                        <td class="p-4 text-center">
                                            <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded text-xs font-bold">
                                                {{ mechanic.work_orders_count }} Mobil
                                            </span>
                                        </td>
                                        <td class="p-4 text-center">
                                            <span :class="mechanic.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">
                                                {{ mechanic.is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-center flex justify-center gap-1">
                                            <Button v-if="$page.props.auth.user.role === 'admin'" variant="ghost" size="icon" 
                                                    class="h-8 w-8 text-amber-600 hover:bg-amber-50" 
                                                    @click="openKtpModal(mechanic)">
                                                <FileText class="w-4 h-4" />
                                            </Button>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 text-blue-500 hover:text-blue-700 hover:bg-blue-50" @click="editMechanic(mechanic)">
                                                <Edit class="w-4 h-4" />
                                            </Button>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-red-600 hover:bg-red-50" @click="deleteMechanic(mechanic.id)">
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </td>
                                    </tr>
                                    <tr v-if="mechanics.length === 0">
                                        <td colspan="5" class="p-8 text-center text-slate-500 italic">Belum ada mekanik terdaftar.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <Dialog v-model:open="isKtpModalOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>KTP Mekanik: {{ selectedMechanic?.name }}</DialogTitle>
                </DialogHeader>
                
                <div class="py-4 space-y-4">
                    <div v-if="selectedMechanic?.ktp_path" class="border rounded-lg overflow-hidden bg-slate-100">
                        <img :src="'/storage/' + selectedMechanic.ktp_path" class="w-full h-auto max-h-64 object-contain" />
                        <div class="p-2 bg-white text-center">
                            <a :href="'/storage/' + selectedMechanic.ktp_path" target="_blank" class="text-xs text-blue-600 underline">Lihat Full Size</a>
                        </div>
                    </div>
                    <div v-else class="h-32 flex flex-col items-center justify-center border-2 border-dashed rounded-lg bg-slate-50 text-slate-400">
                        <FileText class="w-8 h-8 mb-2" />
                        <p class="text-sm italic">Belum ada foto KTP terupload</p>
                    </div>

                    <div class="space-y-2">
                        <Label>Upload Foto KTP Baru (JPG/PNG)</Label>
                        <Input type="file" accept="image/*" @input="ktpForm.ktp_file = $event.target.files[0]" />
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="isKtpModalOpen = false">Batal</Button>
                    <Button class="bg-blue-600" @click="uploadKtp" :disabled="!ktpForm.ktp_file || ktpForm.processing">
                        {{ ktpForm.processing ? 'Mengupload...' : 'Upload Sekarang' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>