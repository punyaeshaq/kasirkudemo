import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

// Available color themes
export const colorThemes = [
    {
        id: 'ocean',
        name: 'Ocean Blue',
        icon: '🌊',
        primary: '#0ea5e9',
        secondary: '#06b6d4',
        description: 'Modern & Profesional'
    },
    {
        id: 'emerald',
        name: 'Emerald Green',
        icon: '🌿',
        primary: '#10b981',
        secondary: '#14b8a6',
        description: 'Segar & Elegan'
    },
    {
        id: 'rose',
        name: 'Rose Pink',
        icon: '🌸',
        primary: '#f43f5e',
        secondary: '#ec4899',
        description: 'Lembut & Feminin'
    },
    {
        id: 'sunset',
        name: 'Sunset Orange',
        icon: '🌅',
        primary: '#f97316',
        secondary: '#f59e0b',
        description: 'Hangat & Energik'
    },
    {
        id: 'purple',
        name: 'Purple Rain',
        icon: '💜',
        primary: '#8b5cf6',
        secondary: '#a855f7',
        description: 'Premium & Kreatif'
    },
    {
        id: 'midnight',
        name: 'Midnight',
        icon: '🌙',
        primary: '#6366f1',
        secondary: '#818cf8',
        description: 'Misterius & Tenang'
    },
];

export const useThemeStore = defineStore('theme', () => {
    const isDark = ref(localStorage.getItem('theme') === 'dark');
    const colorTheme = ref(localStorage.getItem('colorTheme') || 'ocean');

    const currentColorTheme = computed(() => {
        return colorThemes.find(t => t.id === colorTheme.value) || colorThemes[0];
    });

    const toggle = () => {
        isDark.value = !isDark.value;
        localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
        applyTheme();
    };

    const setDark = (value) => {
        isDark.value = value;
        localStorage.setItem('theme', value ? 'dark' : 'light');
        applyTheme();
    };

    const setColorTheme = (themeId) => {
        colorTheme.value = themeId;
        localStorage.setItem('colorTheme', themeId);
        applyTheme();
    };

    const applyTheme = () => {
        const html = document.documentElement;

        // Apply dark mode
        if (isDark.value) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        // Remove all color theme classes
        colorThemes.forEach(t => html.classList.remove(`theme-${t.id}`));

        // Apply current color theme
        html.classList.add(`theme-${colorTheme.value}`);
    };

    // Initialize from system preference
    if (!localStorage.getItem('theme')) {
        isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    // Apply theme on store creation
    applyTheme();

    return {
        isDark,
        colorTheme,
        currentColorTheme,
        colorThemes,
        toggle,
        setDark,
        setColorTheme,
        applyTheme
    };
});
