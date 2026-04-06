<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Wrench, ShieldCheck, Clock, MapPin, Phone, ArrowRight, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps<{
    canLogin?: boolean;
    stats?: {
        cars_fixed: number;
        active_mechanics: number;
        years_active: number;
    };
}>();

// 1. Grab global data from Inertia
const page = usePage();
const shop = computed(() => (page.props.shop as any) || {});
const user = computed(() => page.props.auth?.user);

// 2. Auto-format the WhatsApp Link
const waLink = computed(() => {
    let phone = shop.value.shop_phone || shop.value.phone || ''; 
    if (!phone) return '#';
    
    let cleaned = phone.replace(/[^0-9]/g, '');
    if (cleaned.startsWith('0')) {
        cleaned = '62' + cleaned.substring(1);
    }
    return `https://wa.me/${cleaned}`;
});

// 3. Auto-format the Google Maps Embed URL
const mapEmbedUrl = computed(() => {
    const address = shop.value.shop_address;
    if (!address) return null;

    // Use the official Google Maps embed URL format and correctly inject the variable using ${}
    const encodedAddress = encodeURIComponent(address);
    return `https://maps.google.com/maps?q=${encodedAddress}&t=&z=15&ie=UTF8&iwloc=&output=embed`;
});
</script>

<template>
    <Head title="Selamat Datang" />

    <div class="min-h-screen bg-slate-50 font-sans selection:bg-blue-500 selection:text-white">
        
        <nav class="absolute top-0 w-full z-50 px-6 py-4 flex justify-between items-center max-w-7xl mx-auto left-0 right-0">
            <div class="flex items-center gap-3">
                <img v-if="shop.logo_path" :src="shop.logo_path" alt="Logo Bengkel" class="h-10 w-auto object-contain rounded" />
                <div v-else class="bg-blue-600 p-2 rounded-lg shadow-sm border border-blue-500">
                    <Wrench class="w-6 h-6 text-white" />
                </div>
                
                <span class="text-xl font-black tracking-tight text-slate-900">
                    {{ shop.shop_name || 'Bengkel Kami' }}
                </span>
            </div>
            
            <div v-if="canLogin" class="flex gap-4">
                <Link v-if="user" :href="user.role === 'admin' ? route('admin.dashboard') : route('dashboard')" class="font-bold text-sm text-slate-800 hover:text-blue-600 transition-colors flex items-center gap-1">
                    Masuk ke Dashboard <ArrowRight class="w-4 h-4" />
                </Link>
                <!-- <Link v-else :href="route('login')" class="font-bold text-sm bg-slate-900 text-white px-5 py-2.5 rounded-full hover:bg-blue-600 transition-all shadow-lg hover:shadow-blue-500/30">
                    Portal Karyawan
                </Link> -->
            </div>
        </nav>

        <main>
            <div class="relative pt-32 pb-20 sm:pt-40 sm:pb-24 overflow-hidden">
                <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10 text-center">
                    <h1 class="text-5xl md:text-7xl font-black text-slate-900 tracking-tight mb-8">
                        Servis Cepat, Tepat, <br class="hidden md:block" />
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">
                            Tanpa Drama.
                        </span>
                    </h1>
                    <p class="mt-6 text-lg md:text-xl leading-8 text-slate-600 max-w-2xl mx-auto font-medium">
                        Bengkel modern dengan transparansi harga, mekanik bersertifikat, dan riwayat servis digital. Kendaraan Anda berada di tangan yang tepat.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <a href="#services" class="rounded-full bg-blue-600 px-8 py-4 text-sm font-bold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-all">
                            Lihat Layanan Kami
                        </a>
                        <a :href="waLink" target="_blank" class="text-sm font-bold leading-6 text-slate-900 flex items-center gap-2 hover:text-blue-600 transition-colors">
                            Hubungi via WhatsApp <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
                
                <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80">
                    <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#3b82f6] to-[#06b6d4] opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"></div>
                </div>
            </div>

            <div v-if="stats" class="relative z-20 mx-auto max-w-7xl px-6 lg:px-8 -mt-8 sm:-mt-12">
                <div class="mx-auto max-w-3xl bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 p-8 sm:p-10">
                    <dl class="grid grid-cols-1 gap-x-8 gap-y-8 text-center sm:grid-cols-3">
                        <div class="mx-auto flex max-w-xs flex-col gap-y-2">
                            <dt class="text-sm leading-6 text-slate-500 font-bold uppercase tracking-wider">Kendaraan Selesai</dt>
                            <dd class="order-first text-4xl font-black tracking-tight text-blue-600">
                                {{ stats.cars_fixed.toLocaleString('id-ID') }}+
                            </dd>
                        </div>
                        <div class="mx-auto flex max-w-xs flex-col gap-y-2">
                            <dt class="text-sm leading-6 text-slate-500 font-bold uppercase tracking-wider">Mekanik Handal</dt>
                            <dd class="order-first text-4xl font-black tracking-tight text-blue-600">
                                {{ stats.active_mechanics }} Orang
                            </dd>
                        </div>
                        <div class="mx-auto flex max-w-xs flex-col gap-y-2">
                            <dt class="text-sm leading-6 text-slate-500 font-bold uppercase tracking-wider">Tahun Pengalaman</dt>
                            <dd class="order-first text-4xl font-black tracking-tight text-blue-600">
                                {{ stats.years_active }} Tahun
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div id="services" class="py-24 bg-white sm:py-32">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2 class="text-base font-black uppercase tracking-widest text-blue-600">Spesialisasi Kami</h2>
                        <p class="mt-2 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Semua Kebutuhan Mobil Anda</p>
                    </div>
                    <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                        <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
                            <div class="flex flex-col bg-slate-50 p-8 rounded-2xl border-2 border-slate-100 hover:border-blue-200 transition-colors">
                                <dt class="flex items-center gap-x-3 text-lg font-bold leading-7 text-slate-900">
                                    <div class="bg-blue-100 p-2 rounded-lg"><Wrench class="h-6 w-6 text-blue-600" /></div>
                                    Servis Berkala & Tune Up
                                </dt>
                                <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-slate-600 font-medium">
                                    <p class="flex-auto">Pengecekan menyeluruh, ganti oli, filter, dan kalibrasi mesin agar performa kendaraan kembali seperti baru keluar dari dealer.</p>
                                </dd>
                            </div>
                            <div class="flex flex-col bg-slate-50 p-8 rounded-2xl border-2 border-slate-100 hover:border-blue-200 transition-colors">
                                <dt class="flex items-center gap-x-3 text-lg font-bold leading-7 text-slate-900">
                                    <div class="bg-green-100 p-2 rounded-lg"><ShieldCheck class="h-6 w-6 text-green-600" /></div>
                                    Suku Cadang Asli
                                </dt>
                                <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-slate-600 font-medium">
                                    <p class="flex-auto">Kami menggunakan sistem manajemen inventaris digital untuk memastikan semua sparepart yang dipasang 100% original dan berkualitas.</p>
                                </dd>
                            </div>
                            <div class="flex flex-col bg-slate-50 p-8 rounded-2xl border-2 border-slate-100 hover:border-blue-200 transition-colors">
                                <dt class="flex items-center gap-x-3 text-lg font-bold leading-7 text-slate-900">
                                    <div class="bg-orange-100 p-2 rounded-lg"><Clock class="h-6 w-6 text-orange-600" /></div>
                                    Riwayat Servis Digital
                                </dt>
                                <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-slate-600 font-medium">
                                    <p class="flex-auto">Lupakan buku servis manual yang mudah hilang. Kami menyimpan seluruh riwayat perbaikan mobil Anda di sistem cloud yang aman.</p>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <div id="contact" class="bg-slate-900 py-24 sm:py-32 text-white relative overflow-hidden">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                        <div>
                            <h2 class="text-3xl font-black tracking-tight sm:text-4xl mb-6">Kunjungi Bengkel Kami</h2>
                            <p class="text-lg text-slate-400 mb-8 font-medium">Kami siap membantu memastikan kendaraan Anda aman dan nyaman untuk perjalanan jauh maupun aktivitas sehari-hari.</p>
                            
                            <ul class="space-y-6">
                                <li class="flex gap-4 items-start">
                                    <MapPin class="w-6 h-6 text-blue-500 shrink-0" />
                                    <div>
                                        <p class="font-bold text-lg">Alamat Lengkap</p>
                                        <p class="text-slate-400">{{ shop.shop_address || 'Alamat bengkel belum diatur.' }}</p>
                                    </div>
                                </li>
                                <li class="flex gap-4 items-start">
                                    <Clock class="w-6 h-6 text-blue-500 shrink-0" />
                                    <div>
                                        <p class="font-bold text-lg">Jam Operasional</p>
                                        <p class="text-slate-400">Senin - Sabtu: 08.00 - 17.00 WIB<br>Minggu: Libur</p>
                                    </div>
                                </li>
                                <li class="flex gap-4 items-start">
                                    <Phone class="w-6 h-6 text-blue-500 shrink-0" />
                                    <div>
                                        <p class="font-bold text-lg">Telepon / WhatsApp</p>
                                        <p class="text-slate-400">{{ shop.shop_phone || shop.phone || 'Kontak belum diatur.' }}</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="space-y-6">
                            <div class="overflow-hidden rounded-3xl border border-slate-700 bg-slate-800 shadow-2xl h-[300px] md:h-[400px] relative">
                                <iframe 
                                    v-if="mapEmbedUrl"
                                    width="100%" 
                                    height="100%" 
                                    style="border:0;" 
                                    loading="lazy" 
                                    allowfullscreen 
                                    referrerpolicy="no-referrer-when-downgrade"
                                    :src="mapEmbedUrl"
                                    class="grayscale-[0.2] contrast-[1.1]"
                                ></iframe>
                                
                                <div v-else class="flex flex-col items-center justify-center h-full text-center p-8">
                                     <MapPin class="w-12 h-12 text-slate-600 mb-4" />
                                     <p class="text-slate-500 font-medium text-sm">Alamat belum diatur dalam sistem.</p>
                                </div>
                            </div>

                            <div class="bg-blue-600/10 rounded-2xl p-6 border border-blue-500/20 flex flex-col sm:flex-row items-center justify-between gap-6">
                                <div class="text-center sm:text-left">
                                    <h3 class="text-lg font-black mb-1">Siap Servis Hari Ini?</h3>
                                    <p class="text-slate-400 text-sm font-medium">Klik tombol untuk booking via WhatsApp.</p>
                                </div>
                                <a :href="waLink" target="_blank" class="shrink-0 bg-[#25D366] hover:bg-[#128C7E] text-white font-bold py-3 px-6 rounded-full transition-all flex items-center gap-2 shadow-lg shadow-green-500/20">
                                    <Phone class="w-4 h-4 fill-current" /> Hubungi Admin
                                </a>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </main>

        <footer class="bg-slate-950 py-8 text-center border-t border-slate-800">
            <p class="text-slate-500 font-medium text-sm">
                &copy; {{ new Date().getFullYear() }} {{ shop.shop_name || 'Bengkel Kami' }}. All rights reserved.<br>
                <span class="text-xs mt-2 block opacity-50">Developed by <a href="https://finicode.com/" target="_blank" class="text-blue-400 hover:text-blue-300">Finicode</a></span>
            </p>
        </footer>

    </div>
</template>