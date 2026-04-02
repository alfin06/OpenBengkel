<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, useForm, router, usePage, Link } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import debounce from 'lodash/debounce';

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
    customers: {
        data: any[];
        links: any[];
    };
    filters: {
        search: string;
    };
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
    phone_secondary: '',
    address: '',
});

const submit = () => {
    form.post(route('customers.store'), {
        onSuccess: () => {
            isOpen.value = false;
            form.reset();
            toast.success('Pelanggan berhasil ditambahkan');
        },
    });
};
</script>

<template>
    <Head title="Pelanggan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Pelanggan</h2>
                
                <Dialog v-model:open="isOpen">
                    <DialogTrigger as-child>
                        <Button class="bg-blue-600 hover:bg-blue-700">+ Tambah Pelanggan</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Tambah Pelanggan Baru</DialogTitle>
                        </DialogHeader>
                        <form @submit.prevent="submit" class="space-y-4 pt-4">
                            <div class="grid gap-2">
                                <Label for="name">Nama Lengkap</Label>
                                <Input id="name" v-model="form.name" placeholder="Contoh: Budi Santoso" />
                                <div v-if="form.errors.name" class="text-red-500 text-xs">{{ form.errors.name }}</div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="phone">Nomor WhatsApp</Label>
                                <Input id="phone" v-model="form.phone" placeholder="08..." />
                                <div v-if="form.errors.phone" class="text-red-500 text-xs">{{ form.errors.phone }}</div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="phone_secondary">Nomor Telepon Cadangan (Opsional)</Label>
                                <Input 
                                    id="phone_secondary" 
                                    v-model="form.phone_secondary" 
                                    placeholder="08... (Nomor kedua)" 
                                    @input="form.phone_secondary = form.phone_secondary.replace(/[^0-9]/g, '')" 
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="address">Alamat</Label>
                                <Input id="address" v-model="form.address" placeholder="Alamat singkat" />
                            </div>
                            <DialogFooter>
                                <Button type="submit" :disabled="form.processing" class="w-full">
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Pelanggan' }}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <Card class="shadow-sm">
                    <CardHeader class="pb-3">
                        <div class="flex items-center gap-4">
                            <Input v-model="search" class="max-w-sm" placeholder="Cari nama atau nomor telepon..." />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="rounded-md border overflow-hidden">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50 text-slate-700 uppercase text-[11px] tracking-wider border-b">
                                    <tr>
                                        <th class="p-4 font-bold">Nama</th>
                                        <th class="p-4 font-bold">Telepon</th>
                                        <th class="p-4 font-bold">Telepon2</th>
                                        <th class="p-4 font-bold text-center">Jumlah Kendaraan</th>
                                        <th class="p-4 font-bold text-right">&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-blue-50/50 transition-colors">
                                        <td class="p-4 font-medium text-slate-900">{{ customer.name }}</td>
                                        <td class="p-4 text-slate-600 font-mono text-xs">{{ customer.phone }}</td>
                                        <td class="p-4 text-slate-600">
                                            <div class="font-mono text-xs">{{ customer.phone }}</div>
                                            <div v-if="customer.phone_secondary" class="font-mono text-[10px] text-slate-400 mt-1">
                                                Alt: {{ customer.phone_secondary }}
                                            </div>
                                        </td>
                                        <td class="p-4 text-center">
                                            <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 text-[10px] font-bold uppercase">
                                                {{ customer.vehicles_count ?? 0 }} Unit
                                            </span>
                                        </td>
                                        <td class="p-4 text-right">
                                            <Link :href="route('customers.show', customer.id)">
                                                <Button variant="outline" size="sm" class="h-8 text-xs">
                                                    Lihat Detail
                                                </Button>
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="customers.data.length === 0">
                                        <td colspan="4" class="p-12 text-center text-slate-400 italic">
                                            Tidak ada pelanggan ditemukan.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="customers.links.length > 3" class="mt-6 flex justify-center items-center gap-1">
                            <template v-for="(link, k) in customers.links" :key="k">
                                <div v-if="link.url === null" 
                                     class="px-3 py-1.5 text-xs text-slate-400 border rounded bg-slate-50"
                                     v-html="link.label" />
                                <Link v-else
                                      :href="link.url"
                                      v-html="link.label"
                                      class="px-3 py-1.5 text-xs border rounded transition-colors"
                                      :class="{ 'bg-blue-600 text-white border-blue-600': link.active, 'bg-white text-slate-700 hover:bg-slate-50': !link.active }" 
                                />
                            </template>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>