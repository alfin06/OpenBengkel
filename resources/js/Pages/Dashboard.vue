<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3'; // Removed usePage
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import Badge from '@/Components/ui/Badge.vue';
import { Wrench, Clock, Car } from 'lucide-vue-next';
import { onMounted, onUnmounted } from 'vue';

// 1. Define Props simply. 
// Inertia will automatically fill this with data from DashboardController
const props = defineProps<{
    activeWorkOrders: any[] // Ensure this matches the Controller key exactly
}>();

// 2. Auto-refresh logic
let interval: any;
onMounted(() => {
    console.log('Work Orders received from Laravel:', props.activeWorkOrders);
    interval = setInterval(() => {
        router.reload({ only: ['activeWorkOrders'] });
    }, 30000); 
});

onUnmounted(() => clearInterval(interval));
</script>

<template>
    <Head title="Workshop Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <Card class="bg-blue-50 border-blue-200">
                        <CardHeader class="flex flex-row items-center justify-between pb-2">
                            <CardTitle class="text-sm font-medium text-blue-600">Total Unit Antri</CardTitle>
                            <Car class="w-4 h-4 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ props.activeWorkOrders?.length || 0 }} Unit</div>
                        </CardContent>
                    </Card>
                </div>

                <div v-if="!props.activeWorkOrders || props.activeWorkOrders.length === 0" class="text-center py-20 bg-white rounded-lg border-2 border-dashed">
                    <p class="text-slate-500 italic">Bengkel sedang kosong. Belum ada antrian.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link 
                        v-for="wo in props.activeWorkOrders"
                        :key="wo.id" 
                        :href="route('work-orders.show', wo.id)"
                        class="transition-transform hover:scale-[1.02]">
                        <Card :class="wo.status === 'Proses' ? 'border-l-4 border-l-green-500' : 'border-l-4 border-l-yellow-500'">
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <div>
                                    <p class="text-xs uppercase font-bold text-slate-500">WO #{{ wo.id }}</p>
                                    <h3 class="text-xl font-black text-slate-900">{{ wo.vehicle.plate_number }}</h3>
                                </div>
                                <Badge :variant="wo.status === 'Proses' ? 'default' : 'secondary'" 
                                       :class="wo.status === 'Proses' ? 'bg-green-600' : 'bg-yellow-500 text-white'">
                                    {{ wo.status }}
                                </Badge>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div class="text-sm">
                                    <p class="font-medium">{{ wo.vehicle.brand }} {{ wo.vehicle.model }}</p>
                                    <p class="text-slate-500 text-xs italic">{{ wo.complaints || 'Tidak ada keluhan tertulis' }}</p>
                                </div>

                                <div class="space-y-1">
                                    <div class="flex justify-between text-[10px] font-bold uppercase">
                                        <span>Progress Kerja</span>
                                        <span>
                                            {{ wo.items?.filter((i:any) => i.is_completed).length || 0 }} / {{ wo.items?.length || 0 }} Done
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                        <div 
                                            class="bg-green-500 h-full transition-all" 
                                            :style="{ width: (wo.items?.length > 0 ? (wo.items.filter((i:any) => i.is_completed).length / wo.items.length) * 100 : 0) + '%' }"
                                        ></div>
                                    </div>
                                </div>

                                <div class="pt-2 flex items-center gap-1 text-[10px] text-slate-400">
                                    <Clock class="w-3 h-3" />
                                    <span>Update terakhir: {{ new Date(wo.updated_at).toLocaleTimeString('id-ID') }}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>