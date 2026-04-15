<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Wrench, ShieldCheck, Clock, MapPin, Phone, ArrowRight, CheckCircle2, Star, Users, Car, Tool } from 'lucide-vue-next';

const props = defineProps<{
    canLogin?: boolean;
    stats?: {
        cars_fixed: number;
        active_mechanics: number;
        years_active: number;
    };
}>();

const page = usePage();
const shop = computed(() => (page.props.shop as any) || {});
const user = computed(() => page.props.auth?.user);

const waLink = computed(() => {
    let phone = shop.value.shop_phone || shop.value.phone || ''; 
    if (!phone) return '#';
    let cleaned = phone.replace(/[^0-9]/g, '');
    if (cleaned.startsWith('0')) cleaned = '62' + cleaned.substring(1);
    return `https://wa.me/${cleaned}`;
});

const mapEmbedUrl = computed(() => {
    const address = shop.value.shop_address;
    if (!address) return null;

    // Use the official Google Maps embed URL format and correctly inject the variable using ${}
    const encodedAddress = encodeURIComponent(address);
    return `https://maps.google.com/maps?q=${encodedAddress}&t=&z=15&ie=UTF8&iwloc=&output=embed`;
});
</script>

<template>
    <Head :title="`Servis Kendaraan Terpercaya - ${shop.shop_name || 'Bengkel Pintar'}`" />

    <div class="min-h-screen bg-white font-sans selection:bg-blue-600 selection:text-white">
        
        <nav class="fixed top-0 w-full z-[100] transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mt-4 flex justify-between items-center bg-white/80 backdrop-blur-md border border-white/20 shadow-lg rounded-2xl px-6 py-4">
                    <div class="flex items-center gap-3">
                        <img v-if="shop.logo_path" :src="shop.logo_path" alt="Logo" class="h-14 w-auto object-contain" />
                        <div v-else class="bg-blue-600 p-2 rounded-lg shadow-inner">
                            <Car class="w-6 h-6 text-white" />
                        </div>
                        <span class="text-xl font-black tracking-tight text-slate-900 hidden sm:block uppercase">
                            {{ shop.shop_name || 'Bengkel Pro' }}
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-6">
                        <a href="#services" class="hidden md:block text-sm font-bold text-slate-600 hover:text-blue-600 transition-colors">Layanan</a>
                        <a href="#contact" class="hidden md:block text-sm font-bold text-slate-600 hover:text-blue-600 transition-colors">Hubungi Kami</a>
                        <div v-if="canLogin">
                            <Link v-if="user" :href="route('dashboard')" class="bg-blue-600 text-white px-5 py-2 rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-md">
                                Dashboard
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full -z-10">
                    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-200/30 blur-[120px] rounded-full"></div>
                    <div class="absolute bottom-[10%] right-[-5%] w-[30%] h-[30%] bg-cyan-200/20 blur-[100px] rounded-full"></div>
                </div>

                <div class="max-w-7xl mx-auto px-6 lg:px-8 text-center">
                    <div class="inline-flex items-center gap-2 bg-blue-50 border border-blue-100 px-4 py-1.5 rounded-full text-blue-700 text-xs font-bold uppercase tracking-widest mb-8 animate-bounce">
                        <Star class="w-3.5 h-3.5 fill-current" /> Bengkel Terpercaya di Semarang <Star class="w-3.5 h-3.5 fill-current" />
                    </div>
                    <h1 class="text-5xl lg:text-8xl font-black text-slate-900 tracking-tight leading-[1.1] mb-8">
                        Solusi Servis <br />
                        <span class="relative">
                            Tanpa Ribet.
                            <svg class="absolute -bottom-2 left-0 w-full" height="8" viewBox="0 0 100 8" preserveAspectRatio="none"><path d="M0 7C25 7 25 1 50 1C75 1 75 7 100 7" stroke="#2563EB" stroke-width="2" fill="none"/></svg>
                        </span>
                    </h1>
                    <p class="mt-8 text-lg lg:text-xl text-slate-600 max-w-2xl mx-auto leading-relaxed font-medium">
                        Solusi perawatan kendaraan menyeluruh mulai dari ganti oli rutin hingga perbaikan berat. Kami pastikan perjalanan Anda tetap aman dan nyaman.
                    </p>
                    
                    <div class="mt-12 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a :href="waLink" target="_blank" class="w-full sm:w-auto rounded-2xl bg-blue-600 px-10 py-5 text-base font-bold text-white shadow-xl shadow-blue-500/20 hover:bg-blue-700 hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                            Booking Via WhatsApp <Phone class="w-5 h-5 fill-current" />
                        </a>
                        <a href="#services" class="w-full sm:w-auto px-10 py-5 text-base font-bold text-slate-700 bg-white border border-slate-200 rounded-2xl hover:bg-slate-50 transition-all">
                            Lihat Menu Servis
                        </a>
                    </div>
                </div>
            </section>

            <section v-if="stats" class="max-w-5xl mx-auto px-6 -mt-10 mb-20 relative z-20">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 bg-slate-900 rounded-[2.5rem] p-8 shadow-2xl">
                    <div class="flex items-center gap-4 px-4 border-b sm:border-b-0 sm:border-r border-slate-800 pb-6 sm:pb-0">
                        <div class="p-3 bg-blue-500/10 rounded-2xl"><Car class="w-8 h-8 text-blue-400" /></div>
                        <div>
                            <div class="text-2xl font-black text-white">{{ stats.cars_fixed.toLocaleString('id-ID') }}+</div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Mobil Tertangani</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 px-4 border-b sm:border-b-0 sm:border-r border-slate-800 pb-6 sm:pb-0">
                        <div class="p-3 bg-cyan-500/10 rounded-2xl"><Users class="w-8 h-8 text-cyan-400" /></div>
                        <div>
                            <div class="text-2xl font-black text-white">{{ stats.active_mechanics }} Ahli</div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Mekanik Terlatih</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 px-4">
                        <div class="p-3 bg-indigo-500/10 rounded-2xl"><ShieldCheck class="w-8 h-8 text-indigo-400" /></div>
                        <div>
                            <div class="text-2xl font-black text-white">{{ stats.years_active }} Thn</div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Pengalaman Luas</div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="services" class="py-24 bg-slate-50">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <div class="text-center mb-16">
                        <h2 class="text-blue-600 font-black uppercase tracking-[0.2em] text-sm mb-4">Layanan Spesialis</h2>
                        <p class="text-4xl font-black text-slate-900 leading-tight">Perawatan Kendaraan Profesional</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="group bg-white p-10 rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mb-8 group-hover:bg-blue-600 transition-colors">
                                <Wrench class="w-7 h-7 text-blue-600 group-hover:text-white" />
                            </div>
                            <h3 class="text-xl font-black text-slate-900 mb-4">Pemeliharaan Rutin</h3>
                            <p class="text-slate-600 leading-relaxed font-medium">Ganti oli, filter udara, filter bensin, dan pembersihan injeksi untuk menjaga efisiensi bahan bakar tetap optimal.</p>
                        </div>

                        <div class="group bg-white p-10 rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                            <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center mb-8 group-hover:bg-green-600 transition-colors">
                                <ShieldCheck class="w-7 h-7 text-green-600 group-hover:text-white" />
                            </div>
                            <h3 class="text-xl font-black text-slate-900 mb-4">Sistem Pengereman</h3>
                            <p class="text-slate-600 leading-relaxed font-medium">Pemeriksaan rem menyeluruh, penggantian kampas, dan pengurasan minyak rem untuk keamanan berkendara Anda.</p>
                        </div>

                        <div class="group bg-white p-10 rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                            <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center mb-8 group-hover:bg-orange-600 transition-colors">
                                <Clock class="w-7 h-7 text-orange-600 group-hover:text-white" />
                            </div>
                            <h3 class="text-xl font-black text-slate-900 mb-4">Tune Up Mesin</h3>
                            <p class="text-slate-600 leading-relaxed font-medium">Kalibrasi ulang sistem mesin, pembersihan kerak karbon, dan pengecekan kelistrikan agar tenaga mesin tetap stabil.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="contact" class="py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <div class="bg-slate-900 rounded-[3rem] overflow-hidden shadow-3xl flex flex-col lg:flex-row">
                        <div class="lg:w-1/2 p-12 lg:p-20 flex flex-col justify-center">
                            <h2 class="text-4xl font-black text-white mb-8">Butuh Konsultasi? <br /><span class="text-blue-500">Kunjungi Kami</span></h2>
                            
                            <div class="space-y-8">
                                <div class="flex gap-6">
                                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center shrink-0"><MapPin class="w-6 h-6 text-blue-400" /></div>
                                    <div>
                                        <div class="text-white font-black text-lg">Alamat Bengkel</div>
                                        <p class="text-slate-400 mt-1 leading-relaxed">{{ shop.shop_address || 'Silakan cek di bagian profil.' }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-6">
                                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center shrink-0"><Clock class="w-6 h-6 text-blue-400" /></div>
                                    <div>
                                        <div class="text-white font-black text-lg">Waktu Operasional</div>
                                        <p class="text-slate-400 mt-1">Senin - Sabtu: 08.00 - 17.00 WIB</p>
                                    </div>
                                </div>
                            </div>

                            <a :href="waLink" target="_blank" class="mt-12 inline-flex items-center justify-center gap-3 bg-green-500 hover:bg-green-600 text-white font-black py-5 px-10 rounded-2xl transition-all shadow-lg shadow-green-500/20">
                                <Phone class="w-5 h-5 fill-current" /> Konsultasi Sekarang (Gratis)
                            </a>
                        </div>
                        
                        <div class="lg:w-1/2 min-h-[400px] bg-slate-800 relative">
                            <iframe 
                                v-if="mapEmbedUrl"
                                :src="mapEmbedUrl"
                                width="100%" height="100%" style="border:0;" allowfullscreen loading="lazy">
                            </iframe>
                            <div v-else class="absolute inset-0 flex flex-col items-center justify-center text-slate-500 p-10 text-center">
                                <MapPin class="w-10 h-10 mb-4 opacity-20" />
                                <p>Peta lokasi sedang disiapkan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="bg-white border-t border-slate-100 py-12">
            <div class="max-w-7xl mx-auto px-6 text-center">
                <p class="text-slate-500 text-sm font-medium">
                    © {{ new Date().getFullYear() }} {{ shop.shop_name }}. Profesionalisme & Kualitas Tanpa Kompromi.<br />
                    <span class="text-xs opacity-50 mt-2 block tracking-widest">Developed by <a href="https://finicode.com" class="text-blue-500 hover:underline">Finicode</a></span>
                </p>
            </div>
        </footer>

        <a :href="waLink" target="_blank" class="fixed bottom-6 right-6 z-[200] bg-green-500 p-4 rounded-full shadow-2xl hover:scale-110 active:scale-95 transition-all md:hidden">
            <Phone class="w-7 h-7 text-white fill-current" />
        </a>

    </div>
</template>

<style scoped>
html {
    scroll-behavior: smooth;
}
@keyframes bounce {
    0%, 100% { transform: translateY(-5%); }
    50% { transform: translateY(0); }
}
.animate-bounce {
    animation: bounce 3s infinite ease-in-out;
}
</style>