<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { ArrowLeft, History, Package, ExternalLink } from 'lucide-vue-next';

const props = defineProps<{
    part: any;
    transactions: any[];
}>();

const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
</script>

<template>
    <Head :title="`Stok - ${part.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <Link :href="route('parts.index')">
                        <Button variant="ghost" size="icon"><ArrowLeft class="w-5 h-5" /></Button>
                    </Link>
                    <h2 class="text-xl font-bold uppercase">{{ part.name }}</h2>
                </div>
                <div class="flex items-center gap-2 bg-slate-100 px-4 py-2 rounded-lg border">
                    <Package class="w-4 h-4 text-slate-500" />
                    <span class="text-xs font-bold text-slate-500 uppercase">Stok Saat Ini:</span>
                    <span class="text-lg font-black" :class="part.stock <= 5 ? 'text-red-600' : 'text-blue-600'">
                        {{ part.stock }}
                    </span>
                </div>
            </div>
        </template>

        <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-[10px] uppercase text-slate-400">Harga Jual</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-xl font-bold">{{ formatIDR(part.sell_price) }}</p>
                    </CardContent>
                </Card>
                </div>

            <Card>
                <CardHeader class="border-b bg-slate-50/50">
                    <CardTitle class="text-sm font-black uppercase flex items-center gap-2">
                        <History class="w-4 h-4 text-blue-600" /> Riwayat Penggunaan Barang
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 border-b text-[10px] uppercase text-slate-500">
                            <tr>
                                <th class="p-4">Tanggal</th>
                                <th class="p-4">Nota / Kendaraan</th>
                                <th class="p-4">Pelanggan</th>
                                <th class="p-4 text-center">Qty Keluar</th>
                                <th class="p-4 text-right">Harga Jual</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-if="transactions.length === 0">
                                <td colspan="6" class="p-8 text-center text-slate-400">Belum ada transaksi untuk barang ini.</td>
                            </tr>
                            <tr v-for="t in transactions" :key="t.invoice_id" class="hover:bg-slate-50">
                                <td class="p-4 text-slate-600">{{ t.date }}</td>
                                <td class="p-4">
                                    <div class="font-bold">#{{ t.invoice_id }}</div>
                                    <div class="text-[10px] font-mono text-blue-600 font-bold bg-blue-50 px-1 rounded inline-block">
                                        {{ t.plate_number }}
                                    </div>
                                </td>
                                <td class="p-4 uppercase font-medium">{{ t.customer }}</td>
                                <td class="p-4 text-center font-bold text-red-600">- {{ t.qty }}</td>
                                <td class="p-4 text-right font-mono">{{ formatIDR(t.price) }}</td>
                                <td class="p-4 text-center">
                                    <Link :href="route('invoices.show', t.invoice_uuid)">
                                        <Button variant="ghost" size="sm" class="h-8 text-blue-600">
                                            <ExternalLink class="w-4 h-4 mr-1" /> Nota
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