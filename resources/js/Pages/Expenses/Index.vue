<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Receipt, Trash2, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    expenses: any[];
}>();

// Set today's date as the default
const today = new Date().toISOString().split('T')[0];

const form = useForm({
    description: '',
    amount: '',
    category: 'Konsumsi / Makan', // Default category
    expense_date: today,
});

const submit = () => {
    form.post(route('expenses.store'), {
        onSuccess: () => form.reset('description', 'amount'),
    });
};

const deleteExpense = (id: number) => {
    if (confirm('Hapus catatan pengeluaran ini?')) {
        router.delete(route('expenses.destroy', id));
    }
};

const formatIDR = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

// Calculate total expenses for the displayed month
const totalExpenses = computed(() => {
    return props.expenses.reduce((total, exp) => total + Number(exp.amount), 0);
});
</script>

<template>
    <Head title="Buku Pengeluaran" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold uppercase tracking-widest text-slate-800">Buku Kas Keluar</h2>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-1 space-y-6">
                    <Card class="border-2 shadow-sm border-slate-200">
                        <CardHeader class="bg-slate-50 border-b pb-4">
                            <CardTitle class="text-sm font-bold uppercase tracking-widest text-slate-700 flex items-center gap-2">
                                <Wallet class="w-4 h-4 text-slate-500" /> Catat Pengeluaran
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="pt-6">
                            <form @submit.prevent="submit" class="space-y-4">
                                <div>
                                    <Label class="text-xs uppercase font-bold text-slate-500">Tanggal</Label>
                                    <Input v-model="form.expense_date" type="date" class="mt-1" required />
                                </div>
                                <div>
                                    <Label class="text-xs uppercase font-bold text-slate-500">Kategori</Label>
                                    <select v-model="form.category" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option>Konsumsi / Makan</option>
                                        <option>Listrik & Air</option>
                                        <option>Beli Alat Bengkel</option>
                                        <option>Transportasi / Bensin</option>
                                        <option>Lain-lain</option>
                                    </select>
                                </div>
                                <div>
                                    <Label class="text-xs uppercase font-bold text-slate-500">Keterangan Detail</Label>
                                    <Input v-model="form.description" placeholder="Cth: Bensin" class="mt-1" required />
                                </div>
                                <div>
                                    <Label class="text-xs uppercase font-bold text-slate-500">Nominal (Rp)</Label>
                                    <Input v-model="form.amount" type="number" placeholder="50000" class="mt-1 font-mono font-bold text-blue-700 text-lg" required />
                                </div>
                                
                                <Button type="submit" class="w-full h-12 text-md font-bold uppercase tracking-widest bg-slate-900 hover:bg-slate-800" :disabled="form.processing">
                                    Simpan Pengeluaran
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <Card class="border-2 shadow-sm border-slate-200">
                        <CardHeader class="bg-slate-50 border-b pb-4 flex flex-row items-center justify-between">
                            <CardTitle class="text-sm font-bold uppercase tracking-widest text-slate-700 flex items-center gap-2">
                                <Receipt class="w-4 h-4 text-slate-500" /> Riwayat Bulan Ini
                            </CardTitle>
                            <div class="text-right">
                                <p class="text-[10px] uppercase font-bold text-slate-400">Total Pengeluaran</p>
                                <p class="font-black text-red-600">{{ formatIDR(totalExpenses) }}</p>
                            </div>
                        </CardHeader>
                        <CardContent class="p-0">
                            <div v-if="expenses.length === 0" class="p-12 text-center text-slate-500 italic">
                                Belum ada pengeluaran yang dicatat bulan ini.
                            </div>
                            <table v-else class="w-full text-sm">
                                <thead class="bg-slate-50 border-b text-[10px] uppercase text-slate-500 tracking-wider">
                                    <tr>
                                        <th class="p-4 text-left font-bold">Tanggal</th>
                                        <th class="p-4 text-left font-bold">Keterangan</th>
                                        <th class="p-4 text-right font-bold">Nominal</th>
                                        <th class="p-4 text-center font-bold">Admin</th>
                                        <th class="p-4 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="expense in expenses" :key="expense.id" class="hover:bg-slate-50/50 transition-colors">
                                        <td class="p-4 text-slate-600 whitespace-nowrap">{{ formatDate(expense.expense_date) }}</td>
                                        <td class="p-4">
                                            <p class="font-bold text-slate-800">{{ expense.description }}</p>
                                            <p class="text-[10px] font-bold uppercase tracking-widest text-blue-600 bg-blue-50 w-fit px-2 py-0.5 rounded mt-1">
                                                {{ expense.category }}
                                            </p>
                                        </td>
                                        <td class="p-4 text-right font-mono font-bold text-red-600 whitespace-nowrap">
                                            - {{ formatIDR(expense.amount) }}
                                        </td>
                                        <td class="p-4 text-center text-xs text-slate-500 whitespace-nowrap">
                                            {{ expense.user?.name }}
                                        </td>
                                        <td class="p-4 text-center">
                                            <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-red-600" @click="deleteExpense(expense.id)">
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </td>
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