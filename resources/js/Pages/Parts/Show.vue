<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import Badge from '@/Components/ui/Badge.vue';
import { 
    ArrowLeft, 
    History, 
    Package, 
    ExternalLink, 
    TrendingDown, 
    Truck, 
    ShoppingBag,
    TrendingUp
} from 'lucide-vue-next';

const props = defineProps<{
    part: any;
    transactions: any[];      // Sales History (Out)
    purchaseHistory: any[];   // Purchase History (In)
    stats: {
        cheapest_price: number;
        cheapest_supplier: string;
        last_purchase_price: number;
    }
}>();

const formatIDR = (val: number) => new Intl.NumberFormat('id-ID', { 
    style: 'currency', 
    currency: 'IDR', 
    minimumFractionDigits: 0 
}).format(val);
</script>

<template>
    <Head :title="`Detail Stok - ${part.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <Link :href="route('parts.index')">
                        <Button variant="ghost" size="icon"><ArrowLeft class="w-5 h-5" /></Button>
                    </Link>
                    <div>
                        <h2 class="text-xl font-bold uppercase leading-tight">{{ part.name }}</h2>
                        <p class="text-[10px] font-mono text-slate-500">{{ part.sku || 'TANPA SKU' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-[9px] font-bold text-slate-400 uppercase">Harga Retail</p>
                        <p class="text-lg font-black text-slate-800">{{ formatIDR(part.sell_price) }}</p>
                    </div>
                    <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border shadow-sm">
                        <Package class="w-4 h-4 text-blue-500" />
                        <span class="text-lg font-black" :class="part.stock <= 5 ? 'text-red-600' : 'text-blue-600'">
                            {{ part.stock }}
                        </span>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card class="border-green-100 bg-green-50/30 shadow-none">
                    <CardContent class="p-4 flex items-center gap-4">
                        <div class="p-2 bg-white rounded-lg border border-green-100">
                            <TrendingDown class="w-5 h-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-black text-green-600/70">Harga Beli Termurah</p>
                            <p class="text-lg font-bold text-slate-800">{{ formatIDR(stats.cheapest_price) }}</p>
                            <p class="text-[10px] text-slate-500 italic">Via: {{ stats.cheapest_supplier }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-blue-100 bg-blue-50/30 shadow-none">
                    <CardContent class="p-4 flex items-center gap-4">
                        <div class="p-2 bg-white rounded-lg border border-blue-100">
                            <Truck class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-black text-blue-600/70">Pembelian Terakhir</p>
                            <p class="text-lg font-bold text-slate-800">{{ formatIDR(stats.last_purchase_price) }}</p>
                            <p class="text-[10px] text-slate-500">Update harga modal terbaru</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-orange-100 bg-orange-50/30 shadow-none">
                    <CardContent class="p-4 flex items-center gap-4">
                        <div class="p-2 bg-white rounded-lg border border-orange-100">
                            <TrendingUp class="w-5 h-5 text-orange-600" />
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-black text-orange-600/70">Margin Estimasi</p>
                            <p class="text-lg font-bold text-slate-800">{{ formatIDR(part.sell_price - stats.last_purchase_price) }}</p>
                            <p class="text-[10px] text-slate-500">Berdasarkan modal terakhir</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card class="border-none shadow-sm ring-1 ring-slate-200">
                <CardHeader class="border-b bg-slate-50/50 py-3">
                    <CardTitle class="text-xs font-black uppercase flex items-center gap-2 text-slate-600">
                        <ShoppingBag class="w-4 h-4 text-green-600" /> Riwayat Pembelian (Barang Masuk)
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 border-b text-[10px] uppercase text-slate-400">
                                <tr>
                                    <th class="p-4">Tanggal</th>
                                    <th class="p-4">Supplier / Ref</th>
                                    <th class="p-4 text-center">Qty Masuk</th>
                                    <th class="p-4 text-right">Harga Beli</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-if="purchaseHistory.length === 0">
                                    <td colspan="4" class="p-8 text-center text-slate-400 italic">Belum ada riwayat pembelian masuk.</td>
                                </tr>
                                <tr v-for="(p, i) in purchaseHistory" :key="i" class="hover:bg-slate-50/50">
                                    <td class="p-4 text-slate-500 font-mono text-xs">{{ p.date }}</td>
                                    <td class="p-4">
                                        <div class="font-bold text-slate-700">{{ p.supplier_name }}</div>
                                        <div class="text-[10px] text-slate-400 uppercase">Ref: {{ p.reference || '-' }}</div>
                                    </td>
                                    <td class="p-4 text-center font-bold text-green-600">+ {{ p.qty }}</td>
                                    <td class="p-4 text-right font-mono font-bold">
                                        {{ formatIDR(p.price) }}
                                        <Badge v-if="p.price <= stats.cheapest_price" class="ml-2 bg-green-500 text-[8px] h-4">BEST PRICE</Badge>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <Card class="border-none shadow-sm ring-1 ring-slate-200">
                <CardHeader class="border-b bg-slate-50/50 py-3">
                    <CardTitle class="text-xs font-black uppercase flex items-center gap-2 text-slate-600">
                        <History class="w-4 h-4 text-blue-600" /> Riwayat Penjualan (Barang Keluar)
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 border-b text-[10px] uppercase text-slate-400">
                                <tr>
                                    <th class="p-4">Tanggal</th>
                                    <th class="p-4">Nota / Kendaraan</th>
                                    <th class="p-4">Pelanggan</th>
                                    <th class="p-4 text-center">Qty Keluar</th>
                                    <th class="p-4 text-right">Harga Jual</th>
                                    <th class="p-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-if="transactions.length === 0">
                                    <td colspan="6" class="p-8 text-center text-slate-400 italic">Belum ada riwayat penjualan.</td>
                                </tr>
                                <tr v-for="t in transactions" :key="t.invoice_id" class="hover:bg-slate-50/50">
                                    <td class="p-4 text-slate-500 font-mono text-xs">{{ t.date }}</td>
                                    <td class="p-4">
                                        <div class="font-bold">#{{ t.invoice_id }}</div>
                                        <div class="text-[10px] font-mono text-blue-600 font-bold bg-blue-50 px-1 rounded inline-block">
                                            {{ t.plate_number }}
                                        </div>
                                    </td>
                                    <td class="p-4 uppercase font-medium text-slate-700">{{ t.customer }}</td>
                                    <td class="p-4 text-center font-bold text-red-500">- {{ t.qty }}</td>
                                    <td class="p-4 text-right font-mono">{{ formatIDR(t.price) }}</td>
                                    <td class="p-4 text-center">
                                        <Link :href="route('invoices.show', t.invoice_uuid)">
                                            <Button variant="ghost" size="sm" class="h-8 text-blue-600 hover:bg-blue-50">
                                                <ExternalLink class="w-4 h-4 mr-1" /> Nota
                                            </Button>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>