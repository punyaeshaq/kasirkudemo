<template>
    <div class="min-h-screen bg-dark-50 dark:bg-dark-900 flex">
        <!-- Sidebar -->
        <aside 
            :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-dark-800 border-r border-dark-200 dark:border-dark-700',
                'transform transition-transform duration-300',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
            @mouseleave="sidebarOpen = false"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center justify-center border-b border-dark-200 dark:border-dark-700 px-4">
                <div class="flex items-center justify-center gap-2">
                    <img 
                        :src="settings.store_logo_url || '/kasirku/public/icons/kasirku-logo.png'" 
                        alt="Logo" 
                        class="h-10 w-auto object-contain" 
                    />
                    <h1 class="text-lg font-bold store-name-gradient truncate">{{ settings.store_name || 'KasirKu' }}</h1>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="p-4 space-y-1">
                <router-link 
                    v-for="item in menuItems" 
                    :key="item.name"
                    :to="item.to" 
                    class="sidebar-link"
                    :class="{ 'active': isActive(item.to) }"
                    v-show="!item.adminOnly || authStore.isAdmin"
                    @click="sidebarOpen = false"
                >
                    <component :is="item.icon" class="w-5 h-5" />
                    <span>{{ item.label }}</span>
                </router-link>
            </nav>
            
            <!-- User Info -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-dark-200 dark:border-dark-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary-500 to-secondary-500 flex items-center justify-center text-white font-medium">
                        {{ userInitials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-dark-900 dark:text-white truncate">
                            {{ authStore.user?.name }}
                        </p>
                        <p class="text-xs text-dark-500 dark:text-dark-400 capitalize">
                            {{ authStore.user?.role }}
                        </p>
                    </div>
                    <button @click="logout" class="p-2 text-dark-500 hover:text-red-500 transition-colors">
                        <ArrowRightOnRectangleIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </aside>
        
        <!-- Overlay -->
        <div 
            v-show="sidebarOpen" 
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/50 z-40"
        ></div>
        
        <!-- Main Content -->
        <div class="flex-1 transition-all duration-300" :class="sidebarOpen ? 'lg:ml-64' : ''">
            <!-- Header -->
            <header class="h-16 bg-white dark:bg-dark-800 border-b border-dark-200 dark:border-dark-700 flex items-center justify-between px-4 sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <button @mouseenter="sidebarOpen = true" class="p-2 text-dark-500 hover:text-dark-700 dark:text-dark-400 hover:bg-dark-100 dark:hover:bg-dark-700 rounded-lg transition-colors">
                        <Bars3Icon class="w-6 h-6" />
                    </button>
                    <h2 class="text-lg font-semibold text-dark-900 dark:text-white">
                        {{ pageTitle }}
                    </h2>
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- Color Theme Picker -->
                    <div class="relative" ref="themePickerRef">
                        <button 
                            @click="showThemePicker = !showThemePicker"
                            class="p-2 rounded-lg text-dark-500 hover:bg-dark-100 dark:hover:bg-dark-700 transition-colors flex items-center gap-1"
                            title="Pilih Tema Warna"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                            <span 
                                class="w-3 h-3 rounded-full ring-2 ring-white dark:ring-dark-700"
                                :style="{ backgroundColor: themeStore.currentColorTheme.primary }"
                            ></span>
                        </button>
                        
                        <!-- Dropdown -->
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 translate-y-1"
                        >
                            <div 
                                v-if="showThemePicker"
                                class="absolute right-0 mt-2 w-64 bg-white dark:bg-dark-800 rounded-xl shadow-xl border border-dark-200 dark:border-dark-700 py-2 z-50"
                            >
                                <div class="px-3 py-2 border-b border-dark-100 dark:border-dark-700">
                                    <p class="text-xs font-semibold text-dark-500 dark:text-dark-400 uppercase tracking-wide">Pilih Tema Warna</p>
                                </div>
                                <div class="p-2 space-y-1">
                                    <button
                                        v-for="theme in colorThemes"
                                        :key="theme.id"
                                        @click="selectTheme(theme.id)"
                                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-200"
                                        :class="[
                                            themeStore.colorTheme === theme.id 
                                                ? 'bg-dark-100 dark:bg-dark-700' 
                                                : 'hover:bg-dark-50 dark:hover:bg-dark-700/50'
                                        ]"
                                    >
                                        <!-- Color Preview -->
                                        <div class="flex -space-x-1">
                                            <span 
                                                class="w-5 h-5 rounded-full ring-2 ring-white dark:ring-dark-800"
                                                :style="{ backgroundColor: theme.primary }"
                                            ></span>
                                            <span 
                                                class="w-5 h-5 rounded-full ring-2 ring-white dark:ring-dark-800"
                                                :style="{ backgroundColor: theme.secondary }"
                                            ></span>
                                        </div>
                                        
                                        <!-- Theme Info -->
                                        <div class="flex-1 text-left">
                                            <p class="text-sm font-medium text-dark-900 dark:text-white">
                                                {{ theme.icon }} {{ theme.name }}
                                            </p>
                                            <p class="text-xs text-dark-500 dark:text-dark-400">{{ theme.description }}</p>
                                        </div>
                                        
                                        <!-- Check Icon -->
                                        <svg 
                                            v-if="themeStore.colorTheme === theme.id"
                                            class="w-5 h-5 text-green-500"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>
                    
                    <!-- Dark Mode Toggle -->
                    <button 
                        @click="themeStore.toggle()"
                        class="p-2 rounded-lg text-dark-500 hover:bg-dark-100 dark:hover:bg-dark-700 transition-colors"
                        title="Mode Gelap/Terang"
                    >
                        <SunIcon v-if="themeStore.isDark" class="w-5 h-5" />
                        <MoonIcon v-else class="w-5 h-5" />
                    </button>
                    
                    <!-- Quick POS Button -->
                    <router-link to="/pos" class="btn-primary hidden sm:flex">
                        <ShoppingCartIcon class="w-4 h-4 mr-2" />
                        Kasir
                    </router-link>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="p-4 md:p-6">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useThemeStore, colorThemes } from '@/stores/theme';
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
    ServerStackIcon
} from '@heroicons/vue/24/outline';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const themeStore = useThemeStore();

const sidebarOpen = ref(false);
const settings = ref({});
const showThemePicker = ref(false);
const themePickerRef = ref(null);

// Close theme picker when clicking outside
const handleClickOutside = (event) => {
    if (themePickerRef.value && !themePickerRef.value.contains(event.target)) {
        showThemePicker.value = false;
    }
};

// Select color theme
const selectTheme = (themeId) => {
    themeStore.setColorTheme(themeId);
    showThemePicker.value = false;
};

const allMenuItems = [
    { name: 'dashboard', to: '/', label: 'Dashboard', icon: HomeIcon, permission: 'dashboard' },
    { name: 'pos', to: '/pos', label: 'Kasir (POS)', icon: ShoppingCartIcon, permission: 'pos' },
    { name: 'products', to: '/products', label: 'Produk', icon: CubeIcon, permission: 'products' },
    { name: 'categories', to: '/categories', label: 'Kategori', icon: TagIcon, permission: 'categories' },
    { name: 'transactions', to: '/transactions', label: 'Transaksi', icon: ClipboardDocumentListIcon, permission: 'transactions' },
    { name: 'customers', to: '/customers', label: 'Pelanggan', icon: UserGroupIcon, permission: 'customers' },
    { name: 'debts', to: '/debts', label: 'Piutang', icon: BanknotesIcon, permission: 'debts' },
    { name: 'bank-accounts', to: '/bank-accounts', label: 'Rekening', icon: CreditCardIcon, permission: 'bank_accounts' },
    { name: 'discounts', to: '/discounts', label: 'Diskon', icon: TicketIcon, permission: 'discounts' },
    { name: 'reports', to: '/reports', label: 'Laporan', icon: ChartBarIcon, permission: 'reports' },
    { name: 'users', to: '/users', label: 'Pengguna', icon: UsersIcon, permission: 'users' },
    { name: 'backup', to: '/backup', label: 'Backup Database', icon: ServerStackIcon, permission: 'backup' },
    { name: 'settings', to: '/settings', label: 'Pengaturan', icon: Cog6ToothIcon, permission: 'settings' },
];

const menuItems = computed(() => {
    return allMenuItems.filter(item => {
        // Admin-only items
        if (item.adminOnly) {
            return authStore.isAdmin;
        }
        // Check permission
        if (item.permission) {
            return authStore.hasPermission(item.permission);
        }
        return true;
    });
});

const pageTitle = computed(() => {
    const current = allMenuItems.find(item => item.to === route.path);
    return current?.label || 'KasirKu';
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
    // Add click outside listener
    document.addEventListener('click', handleClickOutside);
    
    try {
        const res = await axios.get('/settings');
        settings.value = res.data;
    } catch (e) {
        console.error('Failed to load settings', e);
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
