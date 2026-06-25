<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { UserPlus, ShieldCheck, Mail, Trash2, Edit2, Key } from 'lucide-vue-next';

const props = defineProps<{ users: any[] }>();

const isEditing = ref(false);
const editId = ref<number | null>(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'staff',
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.users.update', editId.value), {
            onSuccess: () => resetForm(),
        });
    } else {
        form.post(route('admin.users.store'), {
            onSuccess: () => resetForm(),
        });
    }
};

const editUser = (user: any) => {
    isEditing.value = true;
    editId.value = user.id;
    form.name = user.name;
    form.email = user.email;
    form.role = user.role;
    form.password = ''; // Keep password blank unless changing
};

const resetForm = () => {
    isEditing.value = false;
    editId.value = null;
    form.reset();
};

const deleteUser = (id: number) => {
    if (confirm('Nonaktifkan pengguna ini? User tidak akan bisa login lagi.')) {
        router.delete(route('admin.users.destroy', id));
    }
};
</script>

<template>
    <Head title="Manajemen Pengguna" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-slate-800 leading-tight uppercase tracking-widest">Akses Pengguna</h2>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
            <Card class="lg:col-span-1 h-fit border-2">
                <CardHeader>
                    <CardTitle class="text-sm font-black uppercase flex items-center gap-2">
                        <UserPlus class="w-4 h-4" /> {{ isEditing ? 'Edit User' : 'Tambah User' }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label>Nama</Label>
                            <Input v-model="form.name" required />
                        </div>
                        <div>
                            <Label>Email</Label>
                            <Input v-model="form.email" type="email" required />
                        </div>
                        <div>
                            <Label>Password {{ isEditing ? '(Kosongkan jika tidak ganti)' : '' }}</Label>
                            <Input v-model="form.password" type="password" :required="!isEditing" />
                        </div>
                        <div>
                            <Label>Role</Label>
                            <select v-model="form.role" class="w-full mt-1 rounded-md border-slate-300 shadow-sm">
                                <option value="staff">Staff (Operasional)</option>
                                <option value="finance">Finance (Keuangan)</option>
                                <option value="admin">Admin (Management)</option>
                            </select>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <Button class="w-full bg-blue-600" :disabled="form.processing">Simpan</Button>
                            <Button v-if="isEditing" type="button" variant="outline" @click="resetForm">Batal</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <Card class="lg:col-span-2 border-2">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b">
                        <tr class="text-[10px] uppercase font-bold text-slate-500">
                            <th class="p-4 text-left">Nama & Email</th>
                            <th class="p-4 text-center">Role</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="u in users" :key="u.id" class="hover:bg-slate-50">
                            <td class="p-4">
                                <div class="font-bold text-slate-800">{{ u.name }}</div>
                                <div class="text-xs text-slate-500">{{ u.email }}</div>
                            </td>
                            <td class="p-4 text-center">
                                <span :class="u.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'" 
                                      class="px-2 py-1 rounded-full text-[10px] font-black uppercase">
                                    {{ u.role }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @click="editUser(u)" class="text-blue-500 hover:text-blue-700"><Edit2 class="w-4 h-4" /></button>
                                    <button @click="deleteUser(u.id)" class="text-slate-300 hover:text-red-500"><Trash2 class="w-4 h-4" /></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>