<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, useForm, router, usePage, Link } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import debounce from 'lodash/debounce'; // Best practice for lodash imports

// shadcn-vue components
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { 
    Dialog, DialogContent, DialogHeader, 
    DialogTitle, DialogTrigger, DialogFooter 
} from '@/Components/ui/dialog';

const props = defineProps<{
    customers: any;
    filters: any;
}>();

// --- Search Logic ---
const search = ref(props.filters?.search || '');

watch(search, debounce((value) => {
    router.get(route('customers.index'), { search: value }, { 
        preserveState: true, 
        replace: true 
    });
}, 300));

// --- Create Modal Logic ---
const isOpen = ref(false);
const form = useForm({
    name: '',
    phone: '',
    address: '',
});

const submit = () => {
    form.post(route('customers.store'), {
        onSuccess: () => {
            isOpen.value = false;
            form.reset();
        },
    });
};

const page = usePage();

watch(() => page.props.flash, (newFlash: any) => {
    if (newFlash?.success) {
        toast.success(newFlash.success);
        // Optional: clear the flash manually if your version of Inertia persists it
        page.props.flash.success = null; 
    }
    if (newFlash?.error) {
        toast.error(newFlash.error);
        page.props.flash.error = null;
    }
}, { deep: true, immediate: true });
</script>

<template>
    <Head title="Pelanggan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Pelanggan</h2>
                
                <Dialog v-model:open="isOpen">
                    <DialogTrigger as-child>
                        <Button>+ Tambah Pelanggan</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Tambah Pelanggan Baru</DialogTitle>
                        </DialogHeader>
                        <form @submit.prevent="submit" class="space-y-4 pt-4">
                            <div class="grid gap-2">
                                <Label for="name">Nama Lengkap</Label>
                                <Input id="name" v-model="form.name" placeholder="Contoh: Budi Santoso" />
                                <div v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="phone">Nomor WhatsApp</Label>
                                <Input id="phone" type="tel" v-model="form.phone" placeholder="08..." @input="form.phone = form.phone.replace(/[^0-9]/g, '')" />
                                <div v-if="form.errors.phone" class="text-red-500 text-sm">{{ form.errors.phone }}</div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="address">Alamat</Label>
                                <Input id="address" v-model="form.address" placeholder="Alamat singkat" />
                            </div>
                            <DialogFooter>
                                <Button type="submit" :disabled="form.processing">Simpan</Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-4">
                            <Input v-model="search" class="max-w-sm" placeholder="Cari nama atau nomor plat..." />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="rounded-md border">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50 text-slate-700 uppercase text-xs">
                                    <tr>
                                        <th class="p-4 font-bold">Nama</th>
                                        <th class="p-4 font-bold">Telepon</th>
                                        <th class="p-4 font-bold text-center">Jumlah Kendaraan</th>
                                        <th class="p-4 font-bold text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="customer in customers.data" :key="customer.id" class="border-t hover:bg-slate-50 transition-colors">
                                        <td class="p-4 font-medium">{{ customer.name }}</td>
                                        <td class="p-4 text-slate-600">{{ customer.phone }}</td>
                                        <td class="p-4 text-center">
                                            <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 text-xs font-medium">
                                                {{ customer.vehicles_count }} Mobil
                                            </span>
                                        </td>
                                        <td class="p-4 text-right">
                                            <Link :href="route('customers.show', customer.id)">
                                                <Button variant="outline" size="sm">
                                                    Detail
                                                </Button>
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="customers.data.length === 0">
                                        <td colspan="4" class="p-8 text-center text-slate-500">
                                            Tidak ada pelanggan ditemukan.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>