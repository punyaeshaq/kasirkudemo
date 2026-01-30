<template>
    <div :class="{ 'dark': isDark }" class="min-h-screen">
        <router-view />
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import { useThemeStore } from '@/stores/theme';
import { useAuthStore } from '@/stores/auth';

const themeStore = useThemeStore();
const authStore = useAuthStore();
const isDark = computed(() => themeStore.isDark);

// Setup auto-logout saat tab/browser ditutup
onMounted(() => {
    authStore.setupAutoLogout();
});

onUnmounted(() => {
    authStore.cleanupAutoLogout();
});
</script>
