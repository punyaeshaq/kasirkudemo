<template>
    <div class="h-screen flex bg-gray-50 dark:bg-dark-900 overflow-hidden transition-colors duration-200">
        <!-- Mobile Sidebar Overlay -->
        <div 
            v-if="sidebarOpen" 
            class="fixed inset-0 z-40 bg-dark-900/50 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Sidebar -->
        <aside 
            :class="[
                'fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white dark:bg-dark-800 border-r border-dark-100 dark:border-dark-700/60 flex flex-col transition-transform duration-300',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-dark-50 dark:border-dark-700/30">
                <div class="flex items-center gap-3">
                    <img 
                        src="/kasirku/public/icons/kasirku-logo.png" 
                        alt="Logo" 
                        class="h-8 w-auto object-contain" 
                    />
                    <h1 class="text-lg font-bold text-dark-900 dark:text-white tracking-tight">{{ settings.store_name || 'KasirKu' }}</h1>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-1">
                <div v-for="(group, index) in filteredMenuGroups" :key="index" class="mb-4">
                    <!-- Group Title (Collapsible Trigger) -->
                    <button 
                        @click="toggleGroup(group.title)"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold text-dark-400 uppercase tracking-widest hover:text-dark-600 dark:hover:text-dark-300 transition-colors"
                    >
                        <span>{{ group.title }}</span>
                        <ChevronDownIcon 
                            class="w-3 h-3 transition-transform duration-200"
                            :class="{ 'rotate-180': !collapsedGroups.includes(group.title) }"
                        />
                    </button>
                    
                    <!-- Group Items -->
                    <div 
                        v-show="!collapsedGroups.includes(group.title)"
                        class="space-y-0.5 mt-1 transition-all duration-200"
                    >
                        <router-link 
                            v-for="item in group.items" 
                            :key="item.name"
                            :to="item.to" 
                            class="sidebar-link"
                            :class="{ 'active': isActive(item.to) }"
                            @click="sidebarOpen = false"
                        >
                            <component :is="item.icon" class="icon w-5 h-5 flex-shrink-0 transition-colors" />
                            <span>{{ item.label }}</span>
                        </router-link>
                    </div>
                </div>
            </nav>
            
            <!-- User Profile Bottom -->
            <div class="p-4 border-t border-dark-100 dark:border-dark-700/60">
                <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-dark-50 dark:hover:bg-dark-700/50 transition-colors cursor-pointer group">
                    <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-700 dark:text-primary-400 font-bold text-sm group-hover:bg-primary-200 dark:group-hover:bg-primary-900/50 transition-colors">
                        {{ userInitials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-dark-900 dark:text-white truncate">
                            {{ authStore.user?.name }}
                        </p>
                        <p class="text-xs text-dark-500 dark:text-dark-400 capitalize">
                            {{ authStore.user?.role }}
                        </p>
                    </div>
                    <button @click="logout" class="p-1.5 text-dark-400 hover:text-red-500 transition-colors" title="Keluar">
                        <ArrowRightOnRectangleIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </aside>
        
        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-white dark:bg-dark-800 border-b border-dark-100 dark:border-dark-700/60 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="p-2 -ml-2 text-dark-500 hover:bg-dark-100 dark:hover:bg-dark-700 rounded-lg lg:hidden transition-colors">
                        <Bars3Icon class="w-6 h-6" />
                    </button>
                    
                    <!-- Breadcrumbs -->
                    <nav class="hidden sm:flex items-center text-sm font-medium text-dark-500 dark:text-dark-400">
                        <router-link to="/" class="hover:text-dark-900 dark:hover:text-white transition-colors">Dashboard</router-link>
                        <ChevronRightIcon class="w-4 h-4 mx-2 text-dark-300" />
                        <span class="text-dark-900 dark:text-white font-semibold">{{ pageTitle }}</span>
                    </nav>
                </div>
                
                <!-- Right Header Controls -->
                <div class="flex items-center gap-2">
                     <!-- Quick POS Button -->
                     <router-link to="/pos" class="btn-primary py-1.5 px-3 text-xs shadow-sm hidden sm:flex items-center gap-2">
                        <ShoppingCartIcon class="w-4 h-4" />
                        <span>POS</span>
                    </router-link>

                    <div class="h-6 w-px bg-dark-200 dark:bg-dark-700 mx-1 hidden sm:block"></div>

                    <!-- Color Theme Picker -->
                    <div class="relative group">
                        <button class="p-2 rounded-lg text-dark-500 hover:bg-dark-100 dark:hover:bg-dark-700 transition-colors">
                            <div class="w-5 h-5 rounded-full bg-primary-600 border border-dark-200 dark:border-dark-600"></div>
                        </button>
                        <!-- Dropdown -->
                        <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-dark-800 rounded-xl shadow-lg border border-dark-100 dark:border-700 py-2 hidden group-hover:block z-50">
                            <p class="px-3 py-1 text-xs font-semibold text-dark-500 dark:text-dark-400 uppercase tracking-wider">Pilih Tema Color</p>
                            <button 
                                v-for="theme in themeStore.colorThemes" 
                                :key="theme.id"
                                @click="themeStore.setColorTheme(theme.id)"
                                class="w-full text-left px-4 py-2 text-sm flex items-center gap-3 hover:bg-dark-50 dark:hover:bg-dark-700 transition-colors"
                                :class="{'text-primary-600 dark:text-primary-400 font-medium': themeStore.colorTheme === theme.id, 'text-dark-700 dark:text-dark-300': themeStore.colorTheme !== theme.id}"
                            >
                                <span class="text-lg">{{ theme.icon }}</span>
                                <div>
                                    <p>{{ theme.name }}</p>
                                    <p class="text-[10px] text-dark-400 opacity-75">{{ theme.description }}</p>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Theme Toggle -->
                    <button 
                        @click="themeStore.toggle()"
                        class="p-2 rounded-lg text-dark-500 hover:bg-dark-100 dark:hover:bg-dark-700 transition-colors"
                        :title="themeStore.isDark ? 'Mode Terang' : 'Mode Gelap'"
                    >
                        <SunIcon v-if="themeStore.isDark" class="w-5 h-5" />
                        <MoonIcon v-else class="w-5 h-5" />
                    </button>
                    
                    <!-- Date/Time (Desktop) -->
                    <div class="hidden md:block text-right ml-2 border-l border-dark-200 dark:border-dark-700 pl-3">
                        <p class="text-xs font-medium text-dark-500 dark:text-dark-400">{{ currentDate }}</p>
                        <p class="text-xs font-bold text-dark-900 dark:text-white">{{ currentTime }}</p>
                    </div>
                </div>
            </header>
            
            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-dark-900 p-4 sm:p-6 lg:p-8">
                <!-- Page Slot for Floating Actions (e.g., Add Product) can be handled in pages -->
                <router-view v-slot="{ Component }">
                    <transition name="fade" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </router-view>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useThemeStore } from '@/stores/theme';
import { useActivationStore } from '@/stores/activation';
import {
    HomeIcon,
    CubeIcon,
    TagIcon,
    ShoppingCartIcon,
    ClipboardDocumentListIcon,
    ChartBarIcon,
    Cog6ToothIcon,
    UsersIcon,
    Bars3Icon,
    SunIcon,
    MoonIcon,
    ArrowRightOnRectangleIcon,
    UserGroupIcon,
    BanknotesIcon,
    CreditCardIcon,
    TicketIcon,
    ServerStackIcon,
    ChevronDownIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const themeStore = useThemeStore();
const activationStore = useActivationStore();

const sidebarOpen = ref(false);
const settings = ref({});
const collapsedGroups = ref([]); // Store collapsed group titles

const toggleGroup = (title) => {
    if (collapsedGroups.value.includes(title)) {
        collapsedGroups.value = collapsedGroups.value.filter(t => t !== title);
    } else {
        collapsedGroups.value.push(title);
    }
};

const menuGroups = [
    {
        title: 'Utama',
        items: [
            { name: 'dashboard', to: '/', label: 'Dashboard', icon: HomeIcon, permission: 'dashboard' },
            { name: 'pos', to: '/pos', label: 'Kasir', icon: ShoppingCartIcon, permission: 'pos' },
        ]
    },
    {
        title: 'Produk',
        items: [
            { name: 'products', to: '/products', label: 'Daftar Produk', icon: CubeIcon, permission: 'products' },
            { name: 'categories', to: '/categories', label: 'Kategori', icon: TagIcon, permission: 'categories' },
            { name: 'discounts', to: '/discounts', label: 'Voucher & Diskon', icon: TicketIcon, permission: 'discounts' },
        ]
    },
    {
        title: 'Transaksi',
        items: [
            { name: 'transactions', to: '/transactions', label: 'Riwayat Transaksi', icon: ClipboardDocumentListIcon, permission: 'transactions' },
            { name: 'debts', to: '/debts', label: 'Catatan Piutang', icon: BanknotesIcon, permission: 'debts' },
        ]
    },
    {
        title: 'Manajemen',
        items: [
            { name: 'reports', to: '/reports', label: 'Laporan', icon: ChartBarIcon, permission: 'reports' },
            { name: 'customers', to: '/customers', label: 'Member', icon: UserGroupIcon, permission: 'customers' },
            { name: 'users', to: '/users', label: 'Pengguna', icon: UsersIcon, adminOnly: true },
            { name: 'settings', to: '/settings', label: 'Pengaturan', icon: Cog6ToothIcon, permission: 'settings' },
            { name: 'activations', to: '/activations', label: 'Aktivasi Lisensi', icon: ServerStackIcon, superadminOnly: true },
        ]
    }
];

const filteredMenuGroups = computed(() => {
    return menuGroups.map(group => {
        const filteredItems = group.items.filter(item => {
            if (item.superadminOnly) return authStore.isSuperAdmin;
            if (item.adminOnly) return authStore.isAdmin || authStore.isSuperAdmin;
            if (item.permission) return authStore.hasPermission(item.permission);
            return true;
        });
        return { ...group, items: filteredItems };
    }).filter(group => group.items.length > 0);
});

const pageTitle = computed(() => {
    const allItems = menuGroups.flatMap(group => group.items);
    const current = allItems.find(item => item.to === route.path);
    return current?.label || 'Halaman';
});

const userInitials = computed(() => {
    const name = authStore.user?.name || '';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
});

const isActive = (path) => {
    if (path === '/') return route.path === '/';
    return route.path.startsWith(path);
};

const logout = async () => {
    await authStore.logout();
    router.push({ name: 'login' });
};

onMounted(async () => {
    try {
        const res = await axios.get('/settings');
        settings.value = res.data;
    } catch (e) {
        console.error('Failed to load settings', e);
    }

    // Start periodic activation check (checks every 5 sec)
    if (activationStore && activationStore.startPeriodicCheck) {
        activationStore.startPeriodicCheck();
    }
    
    // Interceptors are now setup in app.js to avoid race conditions
});

// Stop periodic check when component unmounts
onUnmounted(() => {
    if (activationStore && activationStore.stopPeriodicCheck) {
        activationStore.stopPeriodicCheck();
    }
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
