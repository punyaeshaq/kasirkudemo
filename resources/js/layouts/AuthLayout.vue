<template>
    <div class="min-h-screen relative overflow-hidden bg-[#0F172A]">
        <!-- Premium Background Layers -->
        <div class="absolute inset-0 z-0">
            <!-- Noise Texture -->
            <div class="absolute inset-0 opacity-[0.03] z-10 pointer-events-none mix-blend-overlay" 
                 style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E');">
            </div>

            <!-- Deep Gradient Base -->
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 via-slate-900 to-black opacity-90"></div>

            <!-- Animated Orbs -->
            <div class="absolute top-[-10%] left-[-10%] w-[50vh] h-[50vh] bg-purple-600/20 rounded-full blur-[100px] animate-blob"></div>
            <div class="absolute top-[20%] right-[-10%] w-[60vh] h-[60vh] bg-indigo-600/20 rounded-full blur-[100px] animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-[-10%] left-[10%] w-[60vh] h-[60vh] bg-blue-600/20 rounded-full blur-[100px] animate-blob animation-delay-4000"></div>
            
            <!-- Grid Pattern Overlay -->
            <div class="absolute inset-0 bg-[url('/kasirku/public/images/grid.svg')] bg-center [mask-image:linear-gradient(180deg,white,rgba(255,255,255,0))] opacity-5"></div>
        </div>

        <!-- Main Content -->
        <div class="relative z-10 min-h-screen flex">
            <!-- Left Side - Illustration (Hidden on mobile) -->
            <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 items-center justify-center p-12 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent skew-x-12 translate-x-[-100%] animate-shine"></div>
                
                <div class="max-w-lg text-center relative z-20">
                    <!-- Cashier Illustration -->
                    <div class="relative mb-8 group">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/30 to-purple-500/30 rounded-3xl blur-3xl transform group-hover:scale-110 transition-transform duration-700"></div>
                        <img 
                            src="/kasirku/public/images/cashier-illustration.png" 
                            alt="Kasir Illustration" 
                            class="relative w-full max-w-md mx-auto drop-shadow-2xl animate-float-gentle"
                        />
                    </div>
                    
                    <!-- Tagline -->
                    <h2 class="text-3xl xl:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-indigo-300 mb-6 drop-shadow-lg tracking-tight">
                        Kelola Bisnis Lebih Mudah
                    </h2>
                    <p class="text-xl text-slate-300 leading-relaxed font-light">
                        Sistem kasir modern yang membantu Anda mengelola penjualan, stok, dan laporan dengan lebih efisien
                    </p>
                    
                    <!-- Feature Pills -->
                    <div class="flex flex-wrap justify-center gap-3 mt-10">
                        <span class="px-5 py-2.5 bg-white/5 backdrop-blur-md rounded-full text-white/90 text-sm font-medium border border-white/10 hover:bg-white/10 transition-colors cursor-default">
                            ⚡ Cepat & Mudah
                        </span>
                        <span class="px-5 py-2.5 bg-white/5 backdrop-blur-md rounded-full text-white/90 text-sm font-medium border border-white/10 hover:bg-white/10 transition-colors cursor-default">
                            📊 Laporan Real-time
                        </span>
                        <span class="px-5 py-2.5 bg-white/5 backdrop-blur-md rounded-full text-white/90 text-sm font-medium border border-white/10 hover:bg-white/10 transition-colors cursor-default">
                            📱 Multi-device
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="w-full lg:w-1/2 xl:w-2/5 flex items-center justify-center p-4 sm:p-8 relative">
                <!-- Mobile Background accent -->
                <div class="lg:hidden absolute inset-0 bg-gradient-to-b from-transparent to-slate-900/90 z-0"></div>

                <div class="w-full max-w-md relative z-10">
                    <!-- Logo -->
                    <div class="text-center mb-8">
                        <div class="flex flex-col items-center justify-center">
                            <div class="relative mb-6 group">
                                <div class="absolute inset-0 bg-indigo-500/20 rounded-2xl blur-xl group-hover:bg-indigo-500/30 transition-all duration-500"></div>
                                <img 
                                    src="/kasirku/public/icons/kasirku-logo.png" 
                                    alt="Logo" 
                                    class="relative h-20 sm:h-24 w-auto object-contain bg-white/5 backdrop-blur-xl p-4 rounded-2xl shadow-2xl border border-white/10 group-hover:scale-105 transition-transform duration-500" 
                                />
                            </div>
                            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2 drop-shadow-lg tracking-tight">
                                {{ settings.store_name || 'KasirKu' }}
                            </h1>
                        </div>
                        <p class="text-indigo-200/80 text-lg font-light tracking-wide">Point of Sale Modern</p>
                    </div>
                    
                    <!-- Auth Card -->
                    <div class="card-glass p-6 sm:p-8 animate-fade-in backdrop-blur-2xl bg-white/[0.07] border border-white/10 shadow-2xl rounded-3xl">
                        <router-view />
                    </div>
                    
                    <!-- Footer -->
                    <p class="text-center text-slate-500 text-xs mt-8 font-medium tracking-wider uppercase">
                        &copy; {{ new Date().getFullYear() }} {{ settings.store_name || 'KasirKu' }}. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const settings = ref({});

onMounted(async () => {
    try {
        const res = await axios.get('/settings');
        settings.value = res.data;
    } catch (e) {
        console.error('Failed to load settings', e);
    }
});
</script>

<style scoped>
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

@keyframes floatGentle {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.animate-float-gentle {
    animation: floatGentle 4s ease-in-out infinite;
}

@keyframes shine {
    100% { left: 125%; }
}

.animate-shine {
    animation: shine 4s infinite;
}

/* Glass Card Enhancement */
.card-glass {
    box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.5),
        0 0 0 1px rgba(255, 255, 255, 0.1) inset;
}
</style>
