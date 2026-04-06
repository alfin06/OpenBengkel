<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import { Store, UploadCloud, MapPin, Phone } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    setting: any;
}>();

// Inertia Form Setup
const form = useForm({
    shop_name: props.setting.shop_name || '',
    shop_phone: props.setting.shop_phone || '',
    shop_address: props.setting.shop_address || '',
    logo: null as File | null,
});

const getImageUrl = (path: string | null) => {
    if (!path) return null;
    
    // If it's already a full URL or already has /storage, leave it alone!
    if (path.startsWith('http') || path.startsWith('/storage')) {
        return path;
    }
    
    // Otherwise, safely prepend /storage
    return `/storage${path.startsWith('/') ? path : '/' + path}`;
};

const imagePreview = ref(getImageUrl(props.setting.logo_path));

// Handle Image Selection and Preview
const handleImageUpload = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        form.logo = file;
        // Create a temporary URL to preview the image before saving
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    // Note: Inertia automatically converts this to FormData because we have a File object
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Reset the file input so it doesn't re-upload if they just change the text later
            form.logo = null; 
        }
    });
};
</script>

<template>
    <Head title="Pengaturan Bengkel" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-slate-800 leading-tight uppercase tracking-widest">
                Pengaturan Bengkel
            </h2>
        </template>

        <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <Card class="border-2 shadow-sm border-slate-200">
                <CardHeader class="bg-slate-50 border-b pb-6">
                    <CardTitle class="text-lg font-bold tracking-tight text-slate-800 flex items-center gap-2">
                        <Store class="w-5 h-5 text-blue-600" /> Profil Usaha
                    </CardTitle>
                    <CardDescription>
                        Informasi ini akan ditampilkan di halaman depan (Landing Page) dan nota pelanggan.
                    </CardDescription>
                </CardHeader>
                
                <CardContent class="pt-6">
                    <form @submit.prevent="submit" class="space-y-8">
                        
                        <div class="flex flex-col sm:flex-row gap-6 items-start">
                            <div class="w-full sm:w-1/3">
                                <Label class="text-sm font-bold text-slate-700">Logo Bengkel</Label>
                                <p class="text-xs text-slate-500 mt-1 mb-4">Format: JPG, PNG, WEBP (Maks 2MB).</p>
                                
                                <div class="relative group cursor-pointer">
                                    <div class="w-32 h-32 rounded-xl border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden bg-slate-50 hover:bg-slate-100 transition-colors">
                                        <img v-if="imagePreview" :src="imagePreview" alt="Preview" class="w-full h-full object-contain p-2" />
                                        <div v-else class="text-center p-4">
                                            <UploadCloud class="w-8 h-8 mx-auto text-slate-400 mb-2" />
                                            <span class="text-[10px] font-bold uppercase text-slate-500">Pilih Foto</span>
                                        </div>
                                    </div>
                                    <input type="file" accept="image/*" @change="handleImageUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                </div>
                                <div v-if="form.errors.logo" class="text-red-500 text-xs mt-2 font-medium">{{ form.errors.logo }}</div>
                            </div>

                            <div class="w-full sm:w-2/3 space-y-5">
                                <div>
                                    <Label class="text-xs uppercase font-bold text-slate-500">Nama Bengkel</Label>
                                    <Input v-model="form.shop_name" placeholder="Cth: Bengkel Motor Jaya" class="mt-1" />
                                    <div v-if="form.errors.shop_name" class="text-red-500 text-xs mt-1">{{ form.errors.shop_name }}</div>
                                </div>
                                
                                <div>
                                    <Label class="text-xs uppercase font-bold text-slate-500 flex items-center gap-1">
                                        <Phone class="w-3 h-3" /> No. Telepon / WhatsApp
                                    </Label>
                                    <Input v-model="form.shop_phone" placeholder="Cth: 081234567890" class="mt-1" />
                                    <div v-if="form.errors.shop_phone" class="text-red-500 text-xs mt-1">{{ form.errors.shop_phone }}</div>
                                </div>

                                <div>
                                    <Label class="text-xs uppercase font-bold text-slate-500 flex items-center gap-1">
                                        <MapPin class="w-3 h-3" /> Alamat Lengkap
                                    </Label>
                                    <textarea 
                                        v-model="form.shop_address" 
                                        rows="3" 
                                        placeholder="Cth: Jl. Sudirman No. 1, Jakarta Pusat"
                                        class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    ></textarea>
                                    <div v-if="form.errors.shop_address" class="text-red-500 text-xs mt-1">{{ form.errors.shop_address }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 flex justify-end">
                            <Button type="submit" class="bg-blue-600 hover:bg-blue-700 font-bold tracking-widest uppercase text-xs px-6 h-10" :disabled="form.processing">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                            </Button>
                        </div>

                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>