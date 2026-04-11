<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { ArrowLeft, Receipt, Calendar, Wallet, ExternalLink, Phone } from 'lucide-vue-next';

const props = defineProps<{
    supplier: any;
    purchaseHistory: any[];
    totalSpent: number;
}>();

const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
</script>

<template>
    <Head :title="`Supplier - ${supplier.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <Link :href="route('suppliers.index')">
                        <Button variant="ghost" size="icon"><ArrowLeft class="w-5 h-5" /></Button>
                    </Link>
                    <h2 class="text-xl font-bold uppercase">{{ supplier.name }}</h2>
                </div>
                <a :href="`https://wa.me/${supplier.phone_whatsapp}`" target="_blank" v-if="supplier.phone_whatsapp">
                    <Button class="bg-green-600 hover:bg-green-700">
                        <Phone class="w-4 h-4 mr-2" /> Hubungi Sales
                    </Button>
                </a>
            </div>
        </template>

        <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card class="bg-blue-900 text-white">
                    <CardContent class="p-6">
                        <p class="text-[10px] uppercase font-bold text-blue-300">Total Transaksi</p>
                        <p class="text-2xl font-black">{{ formatIDR(totalSpent) }}</p>
                    </CardContent>
                </Card>
                </div>

            <Card>
                <CardHeader class="bg-slate-50/50 border-b">
                    <CardTitle class="text-sm font-black uppercase flex items-center gap-2">
                        <Receipt class="w-4 h-4 text-blue-600" /> Riwayat Nota Masuk
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 border-b text-[10px] uppercase text-slate-500">
                            <tr>
                                <th class="p-4">Tanggal</th>
                                <th class="p-4">No. Referensi</th>
                                <th class="p-4">Deskripsi Barang</th>
                                <th class="p-4 text-right">Total Pembayaran</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="h in purchaseHistory" :key="h.id" class="hover:bg-slate-50">
                                <td class="p-4 font-mono text-xs">{{ h.date }}</td>
                                <td class="p-4 font-bold text-blue-600">{{ h.reference || '-' }}</td>
                                <td class="p-4 text-slate-500 italic text-xs">
                                    {{ h.summary }}
                                </td>
                                <td class="p-4 text-right font-black">{{ formatIDR(h.total_cost) }}</td>
                                <td class="p-4 text-center">
                                    <Link :href="route('stock-entries.show', h.id)">
                                        <Button variant="ghost" size="sm" class="text-blue-600">
                                            <ExternalLink class="w-4 h-4" />
                                        </Button>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>