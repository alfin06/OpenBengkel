<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Users, Trash2, Edit, Wrench } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    mechanics: any[];
}>();

const isEditing = ref(false);
const editId = ref<number | null>(null);

const form = useForm({
    name: '',
    phone: '',
    is_active: true,
});

const submit = () => {
    if (isEditing.value && editId.value) {
        form.put(route('admin.mechanics.update', editId.value), {
            onSuccess: () => cancelEdit(),
        });
    } else {
        form.post(route('admin.mechanics.store'), {
            onSuccess: () => form.reset(),
        });
    }
};

const editMechanic = (mechanic: any) => {
    isEditing.value = true;
    editId.value = mechanic.id;
    form.name = mechanic.name;
    form.phone = mechanic.phone || '';
    form.is_active = mechanic.is_active; 
};

const cancelEdit = () => {
    isEditing.value = false;
    editId.value = null;
    form.reset();
    form.clearErrors();
};

const deleteMechanic = (id: number) => {
    if (confirm('Yakin ingin menghapus mekanik ini? Data servis lamanya akan tetap aman.')) {
        router.delete(route('admin.mechanics.destroy', id));
    }
};
</script>

<template>
    <Head title="Manajemen Mekanik" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-slate-800 leading-tight uppercase tracking-widest">
                Tim Mekanik (HR)
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
                                    <Label class="text-xs uppercase font-bold text-slate-500">Nama Lengkap</Label>
                                    <Input v-model="form.name" placeholder="Cth: Budi Santoso" class="mt-1" required />
                                </div>
                                <div>
                                    <Label class="text-xs uppercase font-bold text-slate-500">No. WhatsApp</Label>
                                    <Input v-model="form.phone" placeholder="0812..." class="mt-1" />
                                </div>
                                <div>
                                    <Label class="text-xs uppercase font-bold text-slate-500">Status Karyawan</Label>
                                    <select v-model="form.is_active" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="1">Aktif Bekerja</option>
                                        <option value="0">Nonaktif</option>
                                    </select>
                                </div>
                                
                                <div class="pt-2 flex gap-2">
                                    <Button type="submit" class="w-full h-10 text-sm font-bold uppercase tracking-widest bg-blue-600 hover:bg-blue-700" :disabled="form.processing">
                                        {{ isEditing ? 'Simpan Perubahan' : 'Tambah Mekanik' }}
                                    </Button>
                                    <Button v-if="isEditing" type="button" variant="outline" @click="cancelEdit" class="h-10 px-3">
                                        Batal
                                    </Button>
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
    </AuthenticatedLayout>
</template>