<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { 
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter 
} from '@/Components/ui/dialog';
import { 
    Building2, Phone, MessageSquare, UserCheck, 
    Mail, FileText, Plus, Edit2, MapPin, X, Save, Trash2,
    Search, SearchX
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps<{ 
    suppliers: any[],
    filters: { search: string } 
}>();

const search = ref(props.filters?.search || '');

watch(search, debounce((value) => {
    router.get(route('suppliers.index'), { search: value }, { 
        preserveState: true, 
        preserveScroll: true,
        replace: true 
    });
}, 300));

const showModal = ref(false);
const isEditing = ref(false);
const selectedId = ref<number | null>(null);

const form = useForm({
    name: '',
    address: '',
    phone_office: '',
    phone_whatsapp: '',
    phone_sales: '',
    sales_name: '',
    email: '',
    npwp: '',
});

const openCreate = () => {
    isEditing.value = false;
    form.reset();
    showModal.value = true;
};

const openEdit = (s: any) => {
    isEditing.value = true;
    selectedId.value = s.id;
    form.name = s.name;
    form.address = s.address;
    form.phone_office = s.phone_office;
    form.phone_whatsapp = s.phone_whatsapp;
    form.phone_sales = s.phone_sales;
    form.sales_name = s.sales_name;
    form.email = s.email;
    form.npwp = s.npwp;
    showModal.value = true;
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('suppliers.update', selectedId.value), {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post(route('suppliers.store'), {
            onSuccess: () => {
                form.reset();
                showModal.value = false;
            },
        });
    }
};

const deleteSupplier = () => {
    if (confirm('Hapus supplier ini?')) {
        form.delete(route('suppliers.destroy', selectedId.value), {
            onSuccess: () => (showModal.value = false),
        });
    }
};
</script>

<template>
    <Head title="Manajemen Supplier" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold uppercase text-slate-800 flex items-center gap-2">
                    <Building2 class="w-6 h-6" /> Daftar Supplier
                </h2>
                <Button @click="openCreate" class="bg-blue-600 hover:bg-blue-700 font-bold uppercase text-xs tracking-widest">
                    <Plus class="w-4 h-4 mr-2" /> Tambah Supplier
                </Button>
            </div>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center max-w-md relative mb-8">
                <Search class="absolute left-3 w-4 h-4 text-slate-400" />
                <Input 
                    v-model="search" 
                    placeholder="Cari nama toko, sales, atau nomor WA..." 
                    class="pl-10 h-11 bg-white shadow-sm rounded-xl border-slate-200" 
                />
            </div>
            <div v-if="suppliers.length === 0 && search !== ''" class="py-20 text-center bg-white rounded-2xl border border-dashed border-slate-200">
                <SearchX class="w-12 h-12 mx-auto mb-4 text-slate-300 opacity-50" />
                <p class="text-slate-500 font-bold uppercase tracking-tight">Supplier Tidak Ditemukan</p>
                <p class="text-xs text-slate-400">Pencarian "{{ search }}" tidak membuahkan hasil.</p>
                <Button variant="link" @click="search = ''" class="text-blue-600 text-xs mt-2">Reset Pencarian</Button>
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card v-for="s in suppliers" :key="s.id" class="shadow-sm hover:shadow-md transition-all border-slate-200 overflow-hidden group">
                    <CardHeader class="bg-slate-50 border-b p-4 flex flex-row justify-between items-start">
                        <div>
                            <CardTitle class="text-lg font-black text-slate-900 uppercase tracking-tight">{{ s.name }}</CardTitle>
                            <p class="text-[10px] text-slate-400 font-bold flex items-center gap-1 mt-1">
                                <FileText class="w-3 h-3" /> NPWP: {{ s.npwp || '-' }}
                            </p>
                        </div>
                        <Button variant="ghost" size="icon" @click="openEdit(s)" class="h-8 w-8 text-slate-400 group-hover:text-blue-600">
                            <Edit2 class="w-4 h-4" />
                        </Button>
                    </CardHeader>
                    <CardContent class="p-4 space-y-4">
                        <div class="space-y-2">
                            <div class="flex items-start gap-2 text-xs">
                                <MapPin class="w-4 h-4 text-slate-400 shrink-0" />
                                <span class="text-slate-600">{{ s.address || 'Alamat belum diatur' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs">
                                <Mail class="w-4 h-4 text-slate-400" />
                                <span class="text-slate-600">{{ s.email || '-' }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2 pt-2 border-t border-slate-100">
                            <div class="flex items-center justify-between p-2 bg-slate-50 rounded border border-slate-100">
                                <div class="flex items-center gap-2">
                                    <Phone class="w-3 h-3 text-slate-400" />
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">Kantor</span>
                                </div>
                                <span class="text-xs font-mono font-bold">{{ s.phone_office || '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-green-50 rounded border border-green-100">
                                <div class="flex items-center gap-2">
                                    <MessageSquare class="w-3 h-3 text-green-500" />
                                    <span class="text-[10px] font-bold text-green-600 uppercase">WhatsApp</span>
                                </div>
                                <span class="text-xs font-mono font-bold text-green-700">{{ s.phone_whatsapp || '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-blue-50 rounded border border-blue-100">
                                <div class="flex items-center gap-2">
                                    <UserCheck class="w-3 h-3 text-blue-500" />
                                    <span class="text-[10px] font-bold text-blue-600 uppercase">Sales: {{ s.sales_name || 'Staff' }}</span>
                                </div>
                                <span class="text-xs font-mono font-bold text-blue-700">{{ s.phone_sales || '-' }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <Dialog :open="showModal" @update:open="showModal = $event">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle class="font-black uppercase tracking-widest text-slate-900">
                        {{ isEditing ? 'Edit Supplier' : 'Tambah Supplier Baru' }}
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-4 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <Label class="text-[10px] font-bold uppercase text-slate-400">Nama Perusahaan / Toko</Label>
                            <Input v-model="form.name" required />
                        </div>
                        <div class="col-span-2">
                            <Label class="text-[10px] font-bold uppercase text-slate-400">Alamat Lengkap</Label>
                            <Input v-model="form.address" />
                        </div>
                        <div>
                            <Label class="text-[10px] font-bold uppercase text-slate-400">Telepon Kantor</Label>
                            <Input v-model="form.phone_office" />
                        </div>
                        <div>
                            <Label class="text-[10px] font-bold uppercase text-slate-400">WhatsApp Kantor</Label>
                            <Input v-model="form.phone_whatsapp" />
                        </div>
                        <div>
                            <Label class="text-[10px] font-bold uppercase text-slate-400">Nama Sales Person</Label>
                            <Input v-model="form.sales_name" />
                        </div>
                        <div>
                            <Label class="text-[10px] font-bold uppercase text-slate-400">Nomor HP Sales</Label>
                            <Input v-model="form.phone_sales" />
                        </div>
                        <div>
                            <Label class="text-[10px] font-bold uppercase text-slate-400">Email</Label>
                            <Input v-model="form.email" type="email" />
                        </div>
                        <div>
                            <Label class="text-[10px] font-bold uppercase text-slate-400">NPWP</Label>
                            <Input v-model="form.npwp" />
                        </div>
                    </div>

                    <DialogFooter class="pt-6 border-t flex justify-between">
                        <Button v-if="isEditing" type="button" variant="ghost" @click="deleteSupplier" class="text-red-600">
                            <Trash2 class="w-4 h-4 mr-2" /> Hapus
                        </Button>
                        <div class="flex gap-2 ml-auto">
                            <Button type="button" variant="outline" @click="showModal = false">Batal</Button>
                            <Button type="submit" class="bg-blue-600 font-bold px-8" :disabled="form.processing">
                                <Save class="w-4 h-4 mr-2" /> Simpan Data
                            </Button>
                        </div>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>