<template>
    <Transition name="fade" @after-leave="redirectToDashboard">
        <div v-if="showWelcome" class="fixed inset-0 z-[9999] flex items-center justify-center overflow-hidden bg-[#0F172A]">
            <!-- Premium Background Layers -->
            <div class="absolute inset-0 z-0">
                <!-- Noise Texture -->
                <div class="absolute inset-0 opacity-[0.03] z-10 pointer-events-none mix-blend-overlay" 
                     style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E');">
                </div>

                <!-- Deep Gradient Base -->
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 via-slate-900 to-black opacity-90"></div>

                <!-- Animated Orbs -->
                <div class="absolute top-[-10%] left-[-10%] w-[50vh] h-[50vh] bg-purple-600/30 rounded-full blur-[120px] animate-blob"></div>
                <div class="absolute top-[-20%] right-[-10%] w-[60vh] h-[60vh] bg-indigo-600/30 rounded-full blur-[120px] animate-blob animation-delay-2000"></div>
                <div class="absolute bottom-[-20%] left-[20%] w-[60vh] h-[60vh] bg-blue-600/20 rounded-full blur-[120px] animate-blob animation-delay-4000"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-[50vh] h-[50vh] bg-violet-600/30 rounded-full blur-[100px] animate-blob animation-delay-3000"></div>
                
                <!-- Grid Pattern Overlay -->
                <div class="absolute inset-0 bg-[url('/kasirku/public/images/grid.svg')] bg-center [mask-image:linear-gradient(180deg,white,rgba(255,255,255,0))] opacity-10"></div>
            </div>
            
            <!-- Content -->
            <div class="relative z-20 text-center px-6 animate-fade-in max-w-2xl mx-auto">
                <!-- Logo Container with Glass Effect -->
                <div class="mb-10 inline-block relative group">
                    <div class="absolute inset-0 bg-white/10 rounded-3xl blur-xl group-hover:bg-white/20 transition-all duration-700"></div>
                    <div class="relative bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl p-8 shadow-2xl animate-float-gentle">
                        <img 
                            src="/kasirku/public/icons/kasirku-logo.png" 
                            alt="KasirKu Logo" 
                            class="w-28 h-28 object-contain drop-shadow-2xl"
                        />
                    </div>
                </div>
                
                <!-- Welcome Text -->
                <div class="space-y-6">
                    <div class="overflow-hidden">
                        <h1 class="text-4xl md:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-200 to-indigo-400 animate-slide-up tracking-tight">
                            Selamat Datang!
                        </h1>
                    </div>
                    
                    <div class="overflow-hidden">
                        <p class="text-2xl md:text-3xl text-slate-300 font-light animate-slide-up" style="animation-delay: 0.1s;">
                            {{ userName }}
                        </p>
                    </div>

                    <!-- Modern Loader -->
                    <div class="flex flex-col items-center justify-center gap-4 mt-8 animate-slide-up" style="animation-delay: 0.2s;">
                        <div class="relative w-16 h-1 bg-slate-800 rounded-full overflow-hidden">
                            <div class="absolute inset-0 bg-indigo-500 animate-loading-bar box-glow"></div>
                        </div>
                        <span class="text-sm font-medium text-slate-400 tracking-wider uppercase text-[10px]">Memuat Dashboard</span>
                    </div>
                </div>

                <!-- Footer/Store Name -->
                <div class="mt-16 animate-fade-in" style="animation-delay: 0.4s;">
                    <p class="text-slate-500 text-sm font-medium uppercase tracking-widest border-t border-white/5 pt-6 inline-block px-12">
                        {{ storeName }}
                    </p>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const showWelcome = ref(true);
const settings = ref({});

const userName = computed(() => authStore.user?.name || 'User');
const storeName = computed(() => settings.value.store_name || 'KasirKu POS');

const redirectToDashboard = () => {
    router.replace({ name: 'dashboard' });
};

onMounted(async () => {
    // Load settings
    try {
        const res = await axios.get('/settings');
        settings.value = res.data;
    } catch (e) {
        console.error('Failed to load settings', e);
    }

    // Show welcome for 2.5 seconds then fade out
    setTimeout(() => {
        showWelcome.value = false;
    }, 2500);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.8s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

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

.animation-delay-3000 {
    animation-delay: 3s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slide-up {
    animation: slide-up 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
}

@keyframes floatGentle {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.animate-float-gentle {
    animation: floatGentle 4s ease-in-out infinite;
}

.animate-fade-in {
    animation: fade-in 1s ease forwards;
}

@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes loading-bar {
    0% { transform: translateX(-100%); }
    50% { transform: translateX(0); }
    100% { transform: translateX(100%); }
}

.animate-loading-bar {
    animation: loading-bar 1.5s infinite linear;
}

.box-glow {
    box-shadow: 0 0 15px rgba(99, 102, 241, 0.5);
}
</style>
